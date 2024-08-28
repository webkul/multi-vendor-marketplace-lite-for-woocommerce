<?php
/**
 * Front hooks template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

use WkMarketplace\Helper\Admin;
use WkMarketplace\Helper\Common;
use WkMarketplace\Includes\Shipping;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Common_Functions' ) ) {
	/**
	 * Front hooks class
	 */
	class WKMP_Common_Functions {
		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;


		/**
		 * WKMP_Common_Functions constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb         = $wpdb;
			$this->order_db_obj = Admin\WKMP_Seller_Order_Data::get_instance();
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}

		/**
		 * Manage shipping.
		 */
		public function wkmp_add_manage_shipping() {
			Shipping\WKMP_Manage_Shipping::get_instance();
		}

		/**
		 * Map admin shipping zone with sellers.
		 *
		 * @param int    $instance_id Instance id.
		 * @param string $type Shipping type.
		 * @param int    $zone_id Shipping Zone Id.
		 */
		public function wkmp_after_add_admin_shipping_zone( $instance_id, $type, $zone_id ) {
			$shipping_helper = Common\WKMP_Seller_Shipping::get_instance();
			$seller_id       = get_current_user_id();

			if ( ! empty( $zone_id ) ) {
				$zone_count = $shipping_helper->wkmp_get_shipping_zone_count( $zone_id );

				if ( $zone_count < 1 ) {
					$insert = $shipping_helper->wkmp_insert_seller_shipping_zone(
						array(
							'seller_id' => $seller_id,
							'zone_id'   => $zone_id,
						)
					);
				}
			}
		}

		/**
		 * Delete mapped zone.
		 *
		 * @param int $zone_id Shipping zone id.
		 */
		public function wkmp_action_woocommerce_delete_shipping_zone( $zone_id ) {
			$shipping_helper = Common\WKMP_Seller_Shipping::get_instance();

			if ( $zone_id > 0 ) {
				$shipping_helper->wkmp_delete_shipping_zone( $zone_id );
			}
		}

		/**
		 * Add class data as user meta.
		 *
		 * @param int   $term_id Term id.
		 * @param array $data Data.
		 *
		 * @hooked 'woocommerce_shipping_classes_save_class' action hook.
		 */
		public function wkmp_after_add_admin_shipping_class( $term_id, $data ) {
			global $current_user;
			$seller_sclass = get_user_meta( $current_user->ID, 'shipping-classes', true );
			$seller_sclass = empty( $seller_sclass ) ? array() : maybe_unserialize( $seller_sclass );
			array_push( $seller_sclass, $term_id );
			$seller_sclass_update = maybe_serialize( $seller_sclass );
			update_user_meta( $current_user->ID, 'shipping-classes', $seller_sclass_update );
		}

		/**
		 * Action_on_order_cancel.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked 'woocommerce_order_status_cancelled' action hook.
		 */
		public function wkmp_action_on_order_cancel( $order_id ) {
			$commission        = Common\WKMP_Commission::get_instance();
			$order             = wc_get_order( $order_id );
			$seller_list       = $commission->wkmp_get_sellers_in_order( $order_id );
			$commission_helper = Common\WKMP_Commission::get_instance();

			foreach ( $seller_list as $seller_id ) {
				$sel_info   = $commission->wkmp_get_sel_commission_via_order( $order_id, $seller_id );
				$seller_amt = $sel_info['total_seller_amount'];
				$admin_amt  = $sel_info['total_commission'];

				$commission_data = $commission_helper->wkmp_get_seller_commission_info( $seller_id, 'admin_amount, seller_total_ammount' );

				if ( $commission_data ) {
					$admin_amount  = floatval( $commission_data->admin_amount ) - $admin_amt;
					$seller_amount = floatval( $commission_data->seller_total_ammount ) - $seller_amt;
					$commission_helper->wkmp_update_seller_commission_info(
						$seller_id,
						array(
							'admin_amount'         => $admin_amount,
							'seller_total_ammount' => $seller_amount,
						)
					);
				}
			}
			$this->wkmp_send_mail_to_inform_seller_for_order_status( $order );
		}

		/**
		 * Action on changing order emails.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked 'woocommerce_order_status_completed'
		 * @hooked 'woocommerce_order_status_processing'
		 * @hooked 'woocommerce_order_status_failed'
		 * @hooked 'woocommerce_order_status_on-hold'
		 */
		public function wkmp_action_on_order_changed_mails( $order_id ) {
			$order               = wc_get_order( $order_id );
			$send_mail_to_seller = apply_filters( 'wkmp_send_notification_mail_to_seller_for_new_order', true, $order );
			if ( $send_mail_to_seller ) {
				$this->wkmp_send_mail_to_inform_seller_for_order_status( $order );
			}
		}

		/**
		 * Mail to seller for order status.
		 *
		 * @param \WC_Order $order Order.
		 */
		public function wkmp_send_mail_to_inform_seller_for_order_status( $order ) {
			global $wkmarketplace;

			$items         = $order->get_items();
			$sellers       = array();
			$sell_order_id = $order->get_id();

			foreach ( $items as $item ) {
				$item_id   = isset( $item['product_id'] ) ? $item['product_id'] : 0;
				$author_id = get_post_field( 'post_author', $item_id );

				if ( ! $wkmarketplace->wkmp_user_is_seller( $author_id ) ) {
					continue;
				}

				$order_approval_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_order_approval', true );
				$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $author_id, $sell_order_id );

				if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
					continue;
				}

				$author_id = get_post_field( 'post_author', $item_id );
				$author    = empty( $author_id ) ? '' : get_user_by( 'ID', $author_id );
				$email     = ( $author instanceof \WP_User ) ? $author->user_email : '';

				$sellers[ $email ][] = $item;
			}

			$order_status = $order->get_status();

			$send_processing_mail_to_seller = apply_filters( 'wkmp_send_processing_mail_to_seller', true, $order );

			foreach ( $sellers as $seller_email => $items ) {
				if ( 'cancelled' === $order_status ) {
					do_action( 'wkmp_seller_order_cancelled', $sell_order_id, $items, $seller_email );
				} elseif ( 'failed' === $order_status ) {
					do_action( 'wkmp_seller_order_failed', $sell_order_id, $items, $seller_email );
				} elseif ( 'on-hold' === $order_status ) {
					do_action( 'wkmp_seller_order_on_hold', $sell_order_id, $items, $seller_email );
				} elseif ( 'processing' === $order_status && $send_processing_mail_to_seller ) {
					do_action( 'wkmp_seller_order_processing', $sell_order_id, $items, $seller_email );
				} elseif ( 'completed' === $order_status ) {
					do_action( 'wkmp_seller_order_completed', $sell_order_id, $items, $seller_email );
				} elseif ( 'refunded' === $order_status ) {
					$refund_args = array(
						'order_id'      => $sell_order_id,
						'refund_amount' => $order->get_total() - $order->get_total_refunded(),
					);
					do_action( 'wkmp_seller_order_refunded_completely', $items, $seller_email, $refund_args );
				}
			}
		}

		/**
		 * Action on product approve.
		 *
		 * @param \WP_Post $post Post object.
		 */
		public function wkmp_action_on_product_approve( $post ) {
			if ( 'product' === $post->post_type ) {
				$author_id = get_post_field( 'post_author', $post->ID );
				if ( ! is_super_admin( $author_id ) ) {
					if ( ! get_post_meta( $post->ID, 'mp_admin_view' ) && get_post_meta( $post->ID, 'mp_added_noti', true ) ) {
						delete_post_meta( $post->ID, 'mp_added_noti' );
						update_option( 'wkmp_approved_product_count', (int) ( get_option( 'wkmp_approved_product_count', 1 ) - 1 ) );
					}
					do_action( 'wkmp_product_approve_disapprove', $author_id, $post->ID );
				}
			}
		}

		/**
		 * Action on product disapprove.
		 *
		 * @param int $post_id Post id.
		 */
		public function wkmp_action_on_product_disapprove( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( 'product' === $post_type ) {
				$author_id = get_post_field( 'post_author', $post_id );
				if ( ! is_super_admin( $author_id ) ) {
					if ( ! get_post_meta( $post_id, 'mp_admin_view' ) && get_post_meta( $post_id, 'mp_added_noti', true ) ) {
						delete_post_meta( $post_id, 'mp_added_noti' );
						update_option( 'wkmp_approved_product_count', (int) ( get_option( 'wkmp_approved_product_count', 1 ) - 1 ) );
					}
					do_action( 'wkmp_product_approve_disapprove', $author_id, $post_id, 'disapprove' );
				}
			}
		}

		/**
		 * Adding seller refund data on order refunded.
		 *
		 * @param int $order_id Order id.
		 */
		public function wkmp_add_seller_refund_data_on_order_fully_refunded( $order_id ) {
			$wpdb_obj = $this->wpdb;
			if ( ! empty( $order_id ) ) {
				$sellers_order_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT DISTINCT seller_id, seller_amount FROM {$wpdb_obj->prefix}mporders WHERE order_id = %d", $order_id ) );

				$seller_data = array();
				if ( ! empty( $sellers_order_data ) ) {
					foreach ( $sellers_order_data as $seller_order_data ) {
						$seller_id = $seller_order_data->seller_id;
						if ( array_key_exists( $seller_id, $seller_data ) ) {
							$seller_data[ $seller_id ] += $seller_order_data->seller_amount;
						} else {
							$seller_data[ $seller_id ] = $seller_order_data->seller_amount;
						}
					}
				}

				foreach ( $seller_data as $seller_id => $total_seller_amount ) {
					$shipping_cost = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value from {$wpdb_obj->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key = 'shipping_cost' ", $seller_id, $order_id ) );

					if ( ! empty( $shipping_cost ) ) {
						$total_seller_amount += $shipping_cost;
					}

					$seller_order_tax = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'seller_order_tax' ", $seller_id, $order_id ) );

					if ( ! empty( $seller_order_tax ) ) { // If Tax Calculated.
						$total_seller_amount += (float) $seller_order_tax;
					}

					$seller_order_refund_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id=%d AND order_id=%d AND meta_key=%s", $seller_id, $order_id, '_wkmp_refund_status' ) );

					if ( empty( $seller_order_refund_data ) ) {
						$seller_order_refund_data = array(
							'line_items'      => array(),
							'refunded_amount' => wc_format_decimal( $total_seller_amount ),
						);

						$wpdb_obj->insert(
							"{$wpdb_obj->prefix}mporders_meta",
							array(
								'seller_id'  => $seller_id,
								'order_id'   => $order_id,
								'meta_key'   => '_wkmp_refund_status',
								'meta_value' => maybe_serialize( $seller_order_refund_data ),
							),
							array( '%d', '%d', '%s', '%s' )
						);
					} else {
						$seller_order_refund_data                    = maybe_unserialize( $seller_order_refund_data );
						$seller_order_refund_data['refunded_amount'] = wc_format_decimal( $total_seller_amount );
						$wpdb_obj->update(
							"{$wpdb_obj->prefix}mporders_meta",
							array(
								'meta_value' => maybe_serialize( $seller_order_refund_data ),
							),
							array(
								'seller_id' => $seller_id,
								'order_id'  => $order_id,
								'meta_key'  => '_wkmp_refund_status',
							),
							array( '%s' ),
							array( '%d', '%d', '%s' )
						);
					}
				}
			}
		}

		/**
		 * Add seller refund data on order refund.
		 *
		 * @param int   $refund_id Refund id.
		 * @param array $refund_args Refund args.
		 */
		public function wkmp_add_seller_refund_data_on_order_refund( $refund_id, $refund_args ) {
			$wk_page = \WK_Caching::wk_get_request_data( 'page' );

			if ( is_admin() && ! empty( $wk_page ) && 'order-history' !== $wk_page && ! empty( $refund_id ) ) {
				$refund_total_tax_amount = 0;
				$refund_args['amount']  -= $refund_total_tax_amount;
				$order_refund            = Common\WKMP_Order_Refund::get_instance();

				$order_refund->wkmp_set_refund_args( $refund_args );
				$order_refund->wkmp_set_seller_order_refund_data();
			}
		}

		/**
		 * Save meta info.
		 *
		 * @param int $post_id Post id.
		 *
		 * @hooked 'save_post' Action hook.
		 */
		public function wkmp_save_product_seller_and_qty( $post_id ) {
			$nonce = \WK_Caching::wk_get_request_data( 'wkmp_seller_meta_box_nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp_save_meta_box_seller' ) ) {
				$seller_id = empty( $_POST['seller_id'] ) ? 0 : intval( wp_unslash( $_POST['seller_id'] ) );

				if ( ! empty( $seller_id ) ) {
					$wpdb_obj   = $this->wpdb;
					$table_name = "{$wpdb_obj->prefix}posts";
					$wpdb_obj->update( $table_name, array( 'post_author' => $seller_id ), array( 'ID' => $post_id ), array( '%d' ), array( '%d' ) );

					$product_obj = wc_get_product( $post_id );

					if ( is_a( $product_obj, 'WC_Product' ) ) {
						$variations = $product_obj->get_children();

						foreach ( $variations as $child_post_id ) {
							$wpdb_obj->update( $table_name, array( 'post_author' => $seller_id ), array( 'ID' => $child_post_id ), array( '%d' ), array( '%d' ) );
						}
					}
				}

				$qty_limit = empty( $_POST['_wkmp_max_product_qty_limit'] ) ? -1 : intval( wp_unslash( $_POST['_wkmp_max_product_qty_limit'] ) );

				if ( $qty_limit > -1 ) {
					update_post_meta( $post_id, '_wkmp_max_product_qty_limit', $qty_limit );
				}
			}
		}

		/**
		 * Save profile page data.
		 *
		 * @param int $user_id User id.
		 *
		 * @hooked 'edit_user_profile_update' & 'personal_options_update' action hooks.
		 */
		public function wkmp_save_extra_user_profile_fields( $user_id ) {
			if ( current_user_can( 'edit_user', $user_id ) ) {
				$seller_db_obj = Admin\WKMP_Seller_Data::get_instance();
				$seller_id     = $seller_db_obj->wkmp_get_sellers(
					array(
						'fields'     => 'mp.user_id',
						'single_col' => true,
						'seller_id'  => $user_id,
					)
				);

				$seller_id = ( is_iterable( $seller_id ) && 1 === count( $seller_id ) ) ? $seller_id[0] : 0;

				$args       = array( 'method' => 'post' );
				$shop_name  = \WK_Caching::wk_get_request_data( 'shopname', $args );
				$shop_url   = \WK_Caching::wk_get_request_data( 'shopurl', $args );
				$role       = \WK_Caching::wk_get_request_data( 'role', $args );
				$user_roles = array( $role );

				if ( empty( $role ) ) {
					$seller_user = get_user_by( 'ID', $user_id );
					$user_roles  = ( is_a( $seller_user, 'WP_User' ) ) ? $seller_user->roles : array();
				}

				if ( in_array( 'wk_marketplace_seller', $user_roles, true ) ) {
					update_user_meta( $user_id, 'shop_name', $shop_name );

					if ( ! empty( $shop_url ) ) {
						$db_shop_url = get_user_meta( $user_id, 'shop_address', true );

						if ( empty( $db_shop_url ) ) {
							update_user_meta( $user_id, 'shop_address', $shop_url );
						}
					}

					if ( ! empty( $seller_id ) ) {
						$seller_data = array(
							'user_id'      => $user_id,
							'seller_key'   => 'role',
							'seller_value' => 'seller',
						);

						$seller_db_obj->wkmp_insert_seller( $seller_data );
					} else {
						$seller_db_obj->wkmp_update_seller_role( $user_id, 'seller' );
					}
				} elseif ( ! empty( $seller_id ) ) {
					$seller_db_obj->wkmp_delete_seller( $seller_id, false );
				}
			}
		}

		/**
		 * Validation in profile fields
		 *
		 * @param object $errors Errors.
		 * @param string $update Update.
		 * @param object $user User data.
		 *
		 * @hooked 'user_profile_update_errors' action hook.
		 */
		public function wkmp_validate_extra_profile_fields( &$errors, $update = null, &$user = null ) {
			if ( isset( $user->ID ) && current_user_can( 'edit_user' ) ) {
				$seller_db_obj = Admin\WKMP_Seller_Data::get_instance();
				$seller_id     = $seller_db_obj->wkmp_get_sellers(
					array(
						'fields'     => 'mp.user_id',
						'single_col' => true,
						'seller_id'  => $user->ID,
					)
				);

				$seller_id = ( is_iterable( $seller_id ) && 1 === count( $seller_id ) ) ? $seller_id[0] : 0;

				$shop_url   = \WK_Caching::wk_get_request_data( 'shopurl', array( 'method' => 'post' ) );
				$role       = \WK_Caching::wk_get_request_data( 'role', array( 'method' => 'post' ) );
				$seller_key = 'role';

				if ( 'wk_marketplace_seller' === $role && ! empty( $shop_url ) ) {
					$users = get_users(
						array(
							'meta_key'   => 'shop_address',
							'meta_value' => $shop_url,
							'number'     => 1,
						)
					);

					$seller_user = reset( $users );

					if ( $seller_user instanceof \WP_User && intval( $seller_user->ID ) !== intval( $user->ID ) ) {
						$shop_err = '<strong>' . __( 'ERROR', 'wk-marketplace' ) . '</strong>: ' . __( 'The shop URL already EXISTS please try different shop url', 'wk-marketplace' ) . '.';
						$errors->add( 'invalid-shop-url', $shop_err );
					} else {
						$shop_url   = get_user_meta( $user->ID, 'shop_address', true ) ? get_user_meta( $user->ID, 'shop_address', true ) : $shop_url;
						$user_creds = array(
							'ID'            => $user->ID,
							'user_nicename' => "$shop_url",
						);

						wp_update_user( $user_creds );

						$check = update_user_meta( $user->ID, 'shop_address', $shop_url );

						if ( $check ) {
							if ( ! empty( $seller_id ) ) {
								$seller_db_obj->wkmp_update_seller_role( $seller_id, 'seller' );
							} else {
								$seller_data = array(
									'user_id'      => $user->ID,
									'seller_key'   => $seller_key,
									'seller_value' => 'seller',
								);
								$seller_db_obj->wkmp_insert_seller( $seller_data );
							}
						}
					}
				}
			}
		}

		/**
		 * Reset shipping method.
		 */
		public function wkmp_reset_previous_chosen_shipping_method() {
			$check = get_option( 'wkmp_shipping_option', 'woocommerce' );

			if ( ( is_checkout() || is_cart() ) && ! empty( WC()->session ) ) {
				$wkmp_shipping = WC()->session->get( 'wkmp_shipping' );
				if ( empty( $wkmp_shipping ) ) {
					WC()->session->set( 'wkmp_shipping', $check );
					$check = true;
				} elseif ( $check !== $wkmp_shipping ) {
					WC()->session->set( 'wkmp_shipping', $check );
					$check = true;
				} else {
					$check = false;
				}
				if ( $check ) {
					update_option( 'woocommerce_shipping_debug_mode', 'yes' );
					if ( get_current_user_id() && get_user_meta( get_current_user_id(), 'shipping_method', true ) ) {
						delete_user_meta( get_current_user_id(), 'shipping_method' );
					}
					if ( apply_filters( 'wkmp_unset_shipping_methods', true ) ) {
						WC()->session->__unset( 'chosen_shipping_methods' );
					}
				} else {
					update_option( 'woocommerce_shipping_debug_mode', 'no' );
				}
			} else {
				update_option( 'woocommerce_shipping_debug_mode', 'no' );
			}
		}

		/**
		 * Add link to admin bar.
		 *
		 * @param Object $admin_bar admin bar value.
		 */
		public function wkmp_add_toolbar_items( $admin_bar ) {
			global $current_user;

			if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) && get_option( '_wkmp_separate_seller_dashboard', false ) ) {
				$admin_bar->add_menu(
					array(
						'id'    => 'wkmp-front-dashboard',
						'title' => esc_html__( 'Frontend Dashboard', 'wk-marketplace' ) . '<span class="ab-icon" aria-hidden="true"></span>',
						'meta'  => array(
							'title' => esc_html__( 'Switch to your frontend dashboard', 'wk-marketplace' ),

						),
						'href'  => '#',
					)
				);
			}
		}

		/**
		 * Function to restrict media.
		 *
		 * @hooked 'ajax_query_attachments_args' filter hook.
		 *
		 * @param object $query Query object.
		 */
		public static function wkmp_restrict_media_library( $query ) {
			global $current_user;

			if ( is_a( $current_user, 'WP_User' ) && current_user_can( 'wk_marketplace_seller' ) ) {
				$query['author'] = $current_user->ID;
			}

			return $query;
		}

		/**
		 * Validate and upload/replace seller images.
		 *
		 * @param array $data Seller form posted data.
		 * @param int   $seller_id Seller id.
		 *
		 * @hooked 'wkmp_validate_update_seller_profile' action hook.
		 */
		public function wkmp_process_seller_profile_data( $data, $seller_id ) {
			$errors = array();
			$nonce  = \WK_Caching::wk_get_request_data( 'wkmp-user-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-user-nonce-action' ) ) {
				include_once ABSPATH . 'wp-admin/includes/image.php';
				include_once ABSPATH . 'wp-admin/includes/file.php';
				include_once ABSPATH . 'wp-admin/includes/media.php';

				$data['first_name']       = empty( $_POST['wkmp_first_name'] ) ? '' : $this->wkmp_replace_accents_characters_to_normal( wc_clean( wp_unslash( $_POST['wkmp_first_name'] ) ) );
				$data['last_name']        = empty( $_POST['wkmp_last_name'] ) ? '' : $this->wkmp_replace_accents_characters_to_normal( wc_clean( wp_unslash( $_POST['wkmp_last_name'] ) ) );
				$data['shop_name']        = empty( $_POST['wkmp_shop_name'] ) ? '' : $this->wkmp_replace_accents_characters_to_normal( wc_clean( wp_unslash( $_POST['wkmp_shop_name'] ) ) );
				$data['billing_country']  = empty( $_POST['wkmp_shop_country'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_shop_country'] ) );
				$data['billing_postcode'] = empty( $_POST['wkmp_shop_postcode'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_shop_postcode'] ) );
				$data['user_email']       = empty( $_POST['wkmp_seller_email'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_seller_email'] ) );

				$data['_thumbnail_id_avatar']       = empty( $_POST['wkmp_avatar_id'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_avatar_id'] ) );
				$data['_thumbnail_id_company_logo'] = empty( $_POST['wkmp_logo_id'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_logo_id'] ) );
				$data['_thumbnail_id_shop_banner']  = empty( $_POST['wkmp_banner_id'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_banner_id'] ) );

				if ( empty( $data['user_email'] ) || ! is_email( $data['user_email'] ) ) {
					$errors['wkmp_seller_email'] = esc_html__( 'Enter the valid E-Mail', 'wk-marketplace' );
				} else {
					$seller_info = get_user_by( 'email', $data['user_email'] );

					if ( $seller_info instanceof \WP_User && ( intval( $seller_id ) !== intval( $seller_info->ID ) ) ) {
						$errors['wkmp_seller_email'] = esc_html__( 'Email already exists.', 'wk-marketplace' );
					}
				}

				if ( ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $data['first_name'] ) ) {
					$errors['wkmp_first_name'] = esc_html__( 'Only letters and numbers are allowed.', 'wk-marketplace' );
				}

				if ( ! empty( $data['last_name'] ) && ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $data['last_name'] ) ) {
					$errors['wkmp_last_name'] = esc_html__( 'Only letters and numbers are allowed.', 'wk-marketplace' );
				}

				$shopname_visibility = get_option( 'wkmp_shop_name_visibility', 'required' );

				if ( 'remove' !== $shopname_visibility ) {
					if ( ( empty( $data['shop_name'] ) && 'required' === $shopname_visibility ) || ( ! empty( $data['shop_name'] ) && ! preg_match( '/^[-A-Za-z0-9_\s]{1,40}$/', $data['shop_name'] ) ) ) {
						$errors['wkmp_shop_name'] = esc_html__( 'Enter a valid shop name.', 'wk-marketplace' );
					}
				}

				if ( ! empty( $data['wkmp_shop_phone'] ) && ! \WC_Validation::is_phone( $data['wkmp_shop_phone'] ) ) {
					$errors['wkmp_shop_phone'] = esc_html__( 'Enter the valid phone number', 'wk-marketplace' );
				} elseif ( ( strlen( $data['wkmp_shop_phone'] ) < 4 || strlen( $data['wkmp_shop_phone'] ) > 15 ) ) {
					$errors['wkmp_shop_phone'] = esc_html__( 'Enter the valid phone number of required length from 4 to 15 characters.', 'wk-marketplace' );
				}

				if ( ! empty( $data['billing_postcode'] ) && ! \WC_Validation::is_postcode( $data['billing_postcode'], $data['billing_country'] ) ) {
					$errors['wkmp_shop_postcode'] = esc_html__( 'Enter the valid post code', 'wk-marketplace' );
				}

				if ( isset( $_FILES['wkmp_avatar_file'] ) && isset( $_FILES['wkmp_avatar_file']['name'] ) && ! empty( wc_clean( $_FILES['wkmp_avatar_file']['name'] ) ) ) {
					$message = $this->wkmp_validate_image( wc_clean( $_FILES['wkmp_avatar_file'] ) );
					if ( $message ) {
						$errors['wkmp_avatar_file'] = $message;
					} else {
						$avatar_file = media_handle_upload( 'wkmp_avatar_file', $seller_id );
						if ( is_wp_error( $avatar_file ) ) {
							$errors['wkmp_avatar_file'] = $avatar_file->get_error_message();
						} else {
							$data['_thumbnail_id_avatar'] = intval( $avatar_file );
						}
					}
				}

				if ( isset( $_FILES['wkmp_logo_file'] ) && isset( $_FILES['wkmp_logo_file']['name'] ) && ! empty( wc_clean( $_FILES['wkmp_logo_file']['name'] ) ) ) {
					$message = $this->wkmp_validate_image( wc_clean( $_FILES['wkmp_logo_file'] ) );
					if ( $message ) {
						$errors['wkmp_logo_file'] = $message;
					} else {
						$thumb_file = media_handle_upload( 'wkmp_logo_file', $seller_id );
						if ( is_wp_error( $thumb_file ) ) {
							$errors['wkmp_logo_file'] = $thumb_file->get_error_message();
						} else {
							$data['_thumbnail_id_company_logo'] = intval( $thumb_file );
						}
					}
				}

				if ( isset( $_FILES['wkmp_banner_file'] ) && isset( $_FILES['wkmp_banner_file']['name'] ) && ! empty( wc_clean( $_FILES['wkmp_banner_file']['name'] ) ) ) {
					$message = $this->wkmp_validate_image( wc_clean( $_FILES['wkmp_banner_file'] ) );
					if ( $message ) {
						$errors['wkmp_banner_file'] = $message;
					} else {
						$shop_file = media_handle_upload( 'wkmp_banner_file', $seller_id );
						if ( is_wp_error( $shop_file ) ) {
							$errors['wkmp_banner_file'] = $shop_file->get_error_message();
						} else {
							$data['_thumbnail_id_shop_banner'] = intval( $shop_file );
						}
					}
				}

				$data = apply_filters( 'wkmp_seller_profile_update_data', $data, $errors );

				if ( empty( $errors ) ) {
					$data['billing_phone'] = $data['wkmp_shop_phone'];
					unset( $data['wkmp_shop_phone'] );

					$data['billing_address_1']         = empty( $_POST['wkmp_shop_address_1'] ) ? '' : wp_strip_all_tags( wc_clean( wp_unslash( $_POST['wkmp_shop_address_1'] ) ) );
					$data['billing_address_2']         = empty( $_POST['wkmp_shop_address_2'] ) ? '' : wp_strip_all_tags( wc_clean( wp_unslash( $_POST['wkmp_shop_address_2'] ) ) );
					$data['billing_city']              = empty( $_POST['wkmp_shop_city'] ) ? '' : wp_strip_all_tags( wc_clean( wp_unslash( $_POST['wkmp_shop_city'] ) ) );
					$data['billing_state']             = empty( $_POST['wkmp_shop_state'] ) ? '' : wp_strip_all_tags( wc_clean( wp_unslash( $_POST['wkmp_shop_state'] ) ) );
					$data['mp_seller_payment_details'] = empty( $_POST['wkmp_payment_details'] ) ? '' : wp_strip_all_tags( wc_clean( wp_unslash( $_POST['wkmp_payment_details'] ) ) );
					$data['shop_banner_visibility']    = empty( $_POST['wkmp_display_banner'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_display_banner'] ) );

					$social_settings = empty( $_POST['wkmp_settings'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_settings'] ) );

					$data['social_facebook']  = empty( $social_settings['social']['fb'] ) ? '' : filter_var( $social_settings['social']['fb'], FILTER_SANITIZE_URL );
					$data['social_instagram'] = empty( $social_settings['social']['insta'] ) ? '' : filter_var( $social_settings['social']['insta'], FILTER_SANITIZE_URL );
					$data['social_twitter']   = empty( $social_settings['social']['twitter'] ) ? '' : filter_var( $social_settings['social']['twitter'], FILTER_SANITIZE_URL );
					$data['social_linkedin']  = empty( $social_settings['social']['linkedin'] ) ? '' : filter_var( $social_settings['social']['linkedin'], FILTER_SANITIZE_URL );
					$data['social_youtube']   = empty( $social_settings['social']['youtube'] ) ? '' : filter_var( $social_settings['social']['youtube'], FILTER_SANITIZE_URL );

					$this->wkmp_update_seller_profile( $data, $seller_id );
				} else {
					$_POST['wkmp_errors'] = $errors;
				}
				$_POST['wkmp_profile_data'] = $data;
			}
		}

		/**
		 * Replace accents character like ó á é í ñ to normal one o a e i n.
		 *
		 * @param sting $accented_words Accented words.
		 *
		 * @return string
		 */
		public function wkmp_replace_accents_characters_to_normal( $accented_words = '' ) {
			$normal_string = htmlentities( $accented_words, ENT_COMPAT, 'UTF-8' );
			$normal_string = preg_replace( '/&([a-zA-Z])(uml|acute|grave|circ|tilde|ring);/', '$1', $normal_string );

			return html_entity_decode( $normal_string );
		}

		/**
		 * Updating seller profile data after validation successful.
		 *
		 * @param array $final_data Profile data.
		 * @param int   $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_update_seller_profile( $final_data, $seller_id ) {
			if ( $final_data['billing_state'] ) {
				$country = get_user_meta( $seller_id, 'wkmp_shop_country', true );
				if ( WC()->countries->get_states( $country ) ) {
					$states = WC()->countries->get_states( $country );
					if ( isset( $states[ $final_data['billing_state'] ] ) ) {
						$final_data['billing_state'] = $final_data['billing_state'];
					} elseif ( in_array( $final_data['billing_state'], $states, true ) ) {
						$state_code                  = array_search( $final_data['billing_state'], $states, true );
						$final_data['billing_state'] = $state_code;
					}
				}
			}

			$user_email  = $final_data['user_email'];
			$seller_info = get_user_by( 'email', $user_email );

			if ( ! $seller_info instanceof \WP_User || ( $seller_info instanceof \WP_User && $user_email !== $seller_info->user_email ) ) {
				$user_data = array(
					'ID'         => $seller_id,
					'user_email' => $user_email,
				);
				wp_update_user( $user_data );
			}

			foreach ( $final_data as $meta_key => $meta_value ) {
				if ( ! in_array( $meta_key, array( 'user_email' ), true ) ) {
					update_user_meta( $seller_id, $meta_key, $meta_value );
				}
			}

			do_action( 'mp_save_seller_profile_details', $final_data, $seller_id );
			do_action( 'marketplace_save_seller_payment_details' ); // Deprecated, we'll be removed in future. Use above one.
		}

		/**
		 * Validate image.
		 *
		 * @param array $file File.
		 *
		 * @return string
		 */
		private function wkmp_validate_image( $file ) {
			$img_error = '';

			if ( isset( $file['size'] ) && $file['size'] > wp_max_upload_size() ) {
				$img_error = esc_html__( 'File size too large ', 'wk-marketplace' ) . '[ <= ' . number_format( wp_max_upload_size() / 1048576 ) . ' MB ]';
			}

			$file_type = $this->wkmp_get_mime_type( $file );

			$allowed_types = array(
				'image/png',
				'image/jpeg',
				'image/jpg',
				'image/webp',
			);

			if ( ! $img_error && ! in_array( $file_type, $allowed_types, true ) ) {
				$img_error = esc_html__( 'Upload valid image only', 'wk-marketplace' ) . '[ png, jpeg, jpg, webp ]';
			}

			return $img_error;
		}

		/**
		 * Custom Mime type content function if extension not installed on server.
		 * Or php version not supporting this function.
		 * Or issue due to incorrect php.ini file on client site.
		 *
		 * @param array $filename File name.
		 *
		 * @return string
		 */
		public function wkmp_get_mime_type( $filename ) {
			$mime_types = array(
				// Images.
				'png'  => 'image/png',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg'  => 'image/jpeg',
				'gif'  => 'image/gif',
				'bmp'  => 'image/bmp',
				'ico'  => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif'  => 'image/tiff',
				'svg'  => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
			);

			$file_name = empty( $filename['tmp_name'] ) ? '' : $filename['tmp_name'];
			$value     = empty( $file_name ) ? array() : explode( '.', $file_name );

			if ( is_iterable( $value ) && count( $value ) < 2 ) {
				$file_name = empty( $filename['name'] ) ? '' : $filename['name'];
				$value     = empty( $file_name ) ? array() : explode( '.', $file_name );
			}

			$ext = strtolower( array_pop( $value ) );

			if ( array_key_exists( $ext, $mime_types ) ) {
				return $mime_types[ $ext ];
			} elseif ( function_exists( 'finfo_open' ) ) {
				$finfo    = finfo_open( FILEINFO_MIME );
				$mimetype = finfo_file( $finfo, $file_name );
				finfo_close( $finfo );
				return $mimetype;
			}

			return 'application/octet-stream';
		}

		/**
		 * Validate sold by order item meta for correct seller profile link.
		 *
		 * @param string $value Meta value.
		 * @param object $meta Meta.
		 * @param string $return_type Return type.
		 *
		 * @return string
		 */
		public function wkmp_validate_sold_by_order_item_meta( $value, $meta, $return_type = '' ) {
			global $wkmarketplace;

			$sold_by = $this->wkmp_check_if_sold_by_item_meta( $value, $meta, $wkmarketplace );

			if ( $sold_by ) {
				$seller_id = is_numeric( $sold_by ) ? intval( $sold_by ) : 0;

				if ( $seller_id < 1 && false !== strpos( $value, 'href=' ) ) {
					$anchor_arr = new \SimpleXMLElement( $value );

					$href = empty( $anchor_arr['href'] ) ? '' : $anchor_arr['href'];

					if ( empty( $href ) ) {
						preg_match_all( '/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $value, $result );

						if ( ! empty( $result ) ) {
							$href = empty( $result['href'][0] ) ? '' : $result['href'][0];
						}
					}

					if ( ! empty( $href ) ) {
						$href_arr     = explode( '/', $href );
						$shop_address = empty( $href_arr ) ? '' : end( $href_arr );

						if ( ! empty( $shop_address ) ) {
							$seller_id = $wkmarketplace->wkmp_get_seller_id_by_shop_address( $shop_address );
						}
					}
				}

				if ( $seller_id > 0 ) {
					if ( 'product_seller_id' === $return_type ) {
						return $seller_id;
					}

					$shop_name = '';
					$shop_url  = '';

					if ( $wkmarketplace->wkmp_user_is_seller( $seller_id ) ) {
						$shop_name = get_user_meta( $seller_id, 'shop_name', true );
						$shop_url  = $wkmarketplace->wkmp_get_seller_store_url( $seller_id );
					}

					if ( empty( $shop_name ) && empty( $shop_url ) ) {
						$shop_page_id = wc_get_page_id( 'shop' );
						$shop_page    = get_post( $shop_page_id );
						$shop_url     = get_permalink( $shop_page );
					}

					$shop_name = empty( $shop_name ) ? get_bloginfo( 'name' ) : $shop_name;

					if ( ! empty( $shop_name ) && ! empty( $shop_url ) ) {
						return '<a href="' . esc_url( $shop_url ) . '">' . esc_html( $shop_name ) . '</a>';
					}
				}
			}

			return $value;
		}

		/**
		 * To validate if the current order item meta is Sold by meta.
		 *
		 * @param string $meta_value Meta value.
		 * @param object $meta_data Meta data.
		 * @param object $wkmp Marketplace object.
		 *
		 * @return bool|int
		 */
		public function wkmp_check_if_sold_by_item_meta( $meta_value, $meta_data, $wkmp ) {
			if ( is_string( $meta_value ) && false !== strpos( $meta_value, 'wkmp_seller_id=' ) ) {
				$arr = explode( '=', $meta_value );
				return ( count( $arr ) > 1 ) ? intval( $arr[1] ) : 1;
			}

			if ( false !== strpos( $wkmp->seller_page_slug, $meta_value ) && false !== strpos( get_option( '_wkmp_store_endpoint', 'seller-store' ), $meta_value ) ) {
				return true;
			}

			if ( in_array( $meta_data->key, array( 'Sold By', __( 'Sold By', 'wk-marketplace' ) ), true ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Rest seller total order count.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked 'woocommerce_order_status_completed' action hook.
		 */
		public function wkmp_reset_seller_order_count_cache( $order_id ) {
			if ( class_exists( 'WK_Caching_Core' ) ) {
				$commission = Common\WKMP_Commission::get_instance();
				$cache_obj  = \WK_Caching_Core::get_instance();
				$seller_ids = $commission->wkmp_get_sellers_in_order( $order_id );

				$cache_group = 'wkmp_seller_order_data';
				$reset       = array();

				foreach ( $seller_ids as $seller_id ) {
					$cache_key           = 'wkmp_get_total_seller_orders_' . $seller_id;
					$reset[ $seller_id ] = $cache_obj->reset( $cache_key, $cache_group );
				}
			}
		}

		/**
		 * Get product sku with prefix if enabled.
		 *
		 * @param int|\WC_Product $sell_product Product id or object.
		 *
		 * @return string
		 */
		public function wkmp_get_sku( $sell_product ) {
			$sell_product_id = is_numeric( $sell_product ) ? $sell_product : 0;

			if ( $sell_product_id > 0 ) {
				$sell_product = wc_get_product( $sell_product_id );
			} elseif ( $sell_product instanceof \WC_Product ) {
				$sell_product_id = $sell_product->get_id();
			}

			$seller_id   = 0;
			$product_sku = '';

			if ( $sell_product instanceof \WC_Product ) {
				$seller_id   = get_post_field( 'post_author', $sell_product_id );
				$product_sku = $sell_product->get_sku();
			}

			if ( $seller_id > 0 ) {
				$dynamic_sku_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_dynamic_sku', true );
				$dynamic_sku_prefix  = get_user_meta( $seller_id, '_wkmp_dynamic_sku_prefix', true );

				if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
					$prod_sku    = empty( $product_sku ) ? $sell_product_id : $product_sku;
					$product_sku = $dynamic_sku_prefix . $prod_sku;
				}
			}

			return empty( $product_sku ) ? $sell_product_id : $product_sku;
		}

		/**
		 * Adding dynamic style on seller dashboard.
		 *
		 * @return void
		 */
		public function wkmp_add_seller_dashboard_dynamic_style() {
			if ( get_current_user_id() ) {
				$primary_color = apply_filters( 'wkmp_active_color_code', '#96588a' );
				?>
				<style>
					/** Admin dashboard typography. */
					.wkmp-product-author-shop .wkmp_active_heart, .mp-dashboard-wrapper h2,.mp-dashboard-wrapper .summary-icon,.mp-dashboard-wrapper .mp-store-top-billing-country h4, .mp-dashboard-wrapper .mp-store-sale-order-history-section .header p {
						color:<?php echo esc_attr( $primary_color ); ?>;
					}
				</style>
				<?php
			}
		}
	}
}
