<?php
/**
 * Seller order at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Orders;

use WkMarketplace\Helper\Front;
use WkMarketplace\Helper\Common;
use WkMarketplace\Includes\Common as IncludeCommon;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Orders' ) ) {
	/**
	 * Seller orders class.
	 *
	 * Class WKMP_Orders
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Orders
	 */
	class WKMP_Orders {
		/**
		 * DB Order object.
		 *
		 * @var Front\WKMP_Order_Queries
		 */
		private $db_order_obj;

		/**
		 * Seller ids.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Orders constructor.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function __construct( $seller_id = 0 ) {
			global $wpdb;

			$this->wpdb         = $wpdb;
			$this->db_order_obj = Front\WKMP_Order_Queries::get_instance();
			$this->seller_id    = $seller_id;
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
		 * Method for display seller order list.
		 *
		 * @param int $seller_id Seller id.
		 * @param int $page_no Page no.
		 *
		 * @return void
		 */
		public function wkmp_order_list( $seller_id, $page_no = 1 ) {
			global $wkmarketplace;

			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			$search_order_id = '';
			$nonce           = \WK_Caching::wk_get_request_data( 'wkmp_order_search_nonce', array( 'method' => 'post' ) );

			// Filter Orders.
			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp_order_search_nonce_action' ) ) {
				$search_order_id = empty( $_POST['wkmp_search'] ) ? $search_order_id : intval( wp_unslash( $_POST['wkmp_search'] ) );
			}

			$limit = apply_filters( 'wkmp_sellers_per_page_orders', 20 );

			$filter_data = array(
				'user_id'  => $this->seller_id,
				'search'   => $search_order_id,
				'per_page' => $limit,
				'page_no'  => $page_no,
				'offset'   => ( $page_no - 1 ) * $limit,
			);

			$final_data = $wkmarketplace->wkmp_get_seller_order_table_data( $filter_data );

			$orders      = empty( $final_data['data'] ) ? array() : $final_data['data'];
			$total_count = empty( $final_data['total_orders'] ) ? 0 : $final_data['total_orders'];

			$url        = get_permalink() . get_option( '_wkmp_order_history_endpoint', 'sellers-orders' );
			$pagination = $wkmarketplace->wkmp_get_pagination( $total_count, $page_no, $limit, $url );

			require_once __DIR__ . '/wkmp-order-list.php';
		}

		/**
		 * Seller order views
		 *
		 * @param int $seller_id Seller id.
		 * @param int $order_id Order id.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function wkmp_order_views( $seller_id, $order_id ) {
			global $wkmarketplace;

			$order_id     = empty( $order_id ) ? 0 : intval( $order_id );
			$seller_order = wc_get_order( $order_id );

			if ( ! $seller_order instanceof \WC_Order ) {
				echo '<h2>' . esc_html__( 'Order not found!!', 'wk-marketplace' ) . '</h2>';

				return false;
			}

			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;
			$wpdb_obj        = $this->wpdb;
			$obj_commission  = Common\WKMP_Commission::get_instance();
			$order_refund    = Common\WKMP_Order_Refund::get_instance();

			$mp_order_data            = $obj_commission->wkmp_get_seller_final_order_info( $order_id, $this->seller_id );
			$seller_order_refund_data = $obj_commission->wkmp_get_seller_order_refund_data( $order_id, $this->seller_id );

			$nonce_status = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_status ) && wp_verify_nonce( $nonce_status, 'mp_order_status_nonce_action' ) ) {
				$posted_seller_id = empty( $_POST['mp-seller-id'] ) ? 0 : intval( wp_unslash( $_POST['mp-seller-id'] ) );
				$posted_order_id  = empty( $_POST['mp-order-id'] ) ? 0 : intval( wp_unslash( $_POST['mp-order-id'] ) );

				if ( $posted_seller_id === $this->seller_id && $posted_order_id === $order_id ) {
					$mp_order_status = empty( $_POST['mp-order-status'] ) ? '' : wc_clean( wp_unslash( $_POST['mp-order-status'] ) );

					$posted_data = array(
						'mp-order-status'     => $mp_order_status,
						'mp-order-id'         => $posted_order_id,
						'mp-seller-id'        => $posted_seller_id,
						'mp-old-order-status' => empty( $_POST['mp-old-order-status'] ) ? '' : wc_clean( wp_unslash( $_POST['mp-old-order-status'] ) ),
					);

					if ( ! empty( $mp_order_status ) && 'wc-refunded' === $mp_order_status ) {
						$refund_amount = ! empty( $seller_order_refund_data['refunded_amount'] ) ? $mp_order_data['total_seller_amount'] - $seller_order_refund_data['refunded_amount'] : $mp_order_data['total_seller_amount'];
						if ( ! empty( $refund_amount ) ) {
							$refund_args = array(
								'amount'     => $refund_amount,
								'reason'     => esc_html__( 'Order fully refunded by Seller.', 'wk-marketplace' ),
								'order_id'   => $order_id,
								'line_items' => array(),
							);

							$order_refund->wkmp_set_refund_args( $refund_args );
							$order_refund->wkmp_process_refund();
						}
					}
					$this->wkmp_order_update_status( $posted_data );
				} elseif ( ! empty( $posted_seller_id ) ) {
					$msg = esc_html__( 'Sorry! You are not allowed to perform this action.', 'wk-marketplace' );
					if ( is_admin() ) {
						?>
						<div class="wrap">
							<div class="notice notice-error">
								<p> <?php echo esc_html( $msg ); ?> </p>
							</div>
						</div>
							<?php
					} else {
						wc_print_notice( $msg, 'error' );
					}
				}
			} elseif ( ! empty( $nonce_status ) ) {
				$error = new \WP_Error();
				$error->add( 'nonce-error', esc_html__( 'Sorry, your nonce did not verify.', 'wk-marketplace' ) );
			}

			$nonce_refund = \WK_Caching::wk_get_request_data( 'wkmp-seller-refund-nonce-value', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_refund ) && wp_verify_nonce( $nonce_refund, 'wkmp-seller-refund-nonce-action' ) ) {
				$posted_seller_id = empty( $_POST['mp-seller-id'] ) ? 0 : intval( wp_unslash( $_POST['mp-seller-id'] ) );
				$posted_order_id  = empty( $_POST['mp-order-id'] ) ? 0 : intval( wp_unslash( $_POST['mp-order-id'] ) );

				if ( $posted_seller_id === $this->seller_id && $posted_order_id === $order_id ) {
					$line_items = array();

					$restock_refunded_items = empty( $_POST['restock_refunded_items'] ) ? 0 : intval( wp_unslash( $_POST['restock_refunded_items'] ) );
					$restock_refunded_items = ( 1 === $restock_refunded_items );

					$refund_reason = empty( $_POST['refund_reason'] ) ? '' : wc_clean( wp_unslash( $_POST['refund_reason'] ) );
					$api_refund    = empty( $_POST['do_api_refund'] ) ? '' : wc_clean( wp_unslash( $_POST['do_api_refund'] ) );
					$api_refund    = ! empty( $api_refund );

					$order_items            = empty( $_POST['item_refund_amount'] ) ? array() : wc_clean( wp_unslash( $_POST['item_refund_amount'] ) );
					$order_item_total       = empty( $_POST['refund_line_total'] ) ? array() : wc_clean( wp_unslash( $_POST['refund_line_total'] ) );
					$refund_tax_items       = empty( $_POST['refund_line_tax'] ) ? array() : wc_clean( wp_unslash( $_POST['refund_line_tax'] ) );
					$refund_line_tax_amount = empty( $_POST['refund_line_tax_amount'] ) ? array() : wc_clean( wp_unslash( $_POST['refund_line_tax_amount'] ) );

					$total_refund_amount = 0;

					foreach ( $order_items as $item_id => $order_item ) {
						$qty = ! empty( $order_item_total[ $item_id ] ) ? $order_item_total[ $item_id ] : 0;
						if ( $qty > 0 ) {
							$line_items[ $item_id ]['qty']          = $qty;
							$line_items[ $item_id ]['refund_total'] = round( floatval( $order_item ) * $qty, 2 );
							$total_refund_amount                   += round( $line_items[ $item_id ]['refund_total'], 2 );
						}
					}

					foreach ( $refund_tax_items as $refund_item_key => $refund_item_value ) {
						if ( isset( $refund_line_tax_amount[ $refund_item_key ] ) ) {
							$line_items[ $refund_item_key ]['refund_tax'] = array_filter( array_map( 'wc_format_decimal', $refund_line_tax_amount[ $refund_item_key ] ) );
							$total_refund_amount                         += array_sum( $refund_line_tax_amount[ $refund_item_key ] );
						} elseif ( ! empty( $refund_item_value ) ) {
							$line_items[ $refund_item_key ]['refund_tax'] = array_filter( array_map( 'wc_format_decimal', $refund_item_value ) );
							$total_refund_amount                         += array_sum( $refund_item_value );
						}
					}

					if ( ! empty( $total_refund_amount ) ) {
						$args = array(
							'amount'         => $total_refund_amount,
							'reason'         => $refund_reason,
							'order_id'       => $order_id,
							'line_items'     => $line_items,
							'refund_payment' => $api_refund,
							'restock_items'  => $restock_refunded_items,
						);

						$order_refund->wkmp_set_refund_args( $args );
						$order_refund->wkmp_process_refund();
						$seller_order_refund_data = $obj_commission->wkmp_get_seller_order_refund_data( $order_id, $this->seller_id );

						if ( ! empty( $seller_order_refund_data['refunded_amount'] ) && trim( $seller_order_refund_data['refunded_amount'] ) === trim( $mp_order_data['total_seller_amount'] ) ) {
							$wpdb_obj->update(
								$wpdb_obj->prefix . 'mpseller_orders',
								array( 'order_status' => 'wc-refunded' ),
								array(
									'order_id'  => $order_id,
									'seller_id' => $this->seller_id,
								),
								array( '%s' ),
								array( '%d', '%d' )
							);
						}
					} else {
						$msg = esc_html__( 'Please select items to refund.', 'wk-marketplace' );
						if ( is_admin() ) {
							?>
						<div class="wrap">
							<div class="notice notice-error">
								<p> <?php echo esc_html( $msg ); ?> </p>
							</div>
						</div>
							<?php
						} else {
							wc_print_notice( $msg, 'error' );
						}
					}
				}
			} elseif ( ! empty( $nonce_refund ) ) {
				$error = new \WP_Error();
				$error->add( 'nonce-error', esc_html__( 'Sorry, your nonce did not verify.', 'wk-marketplace' ) );
			}

			$tax_list_name = array();

			foreach ( $seller_order->get_items( 'tax' ) as $item_tax ) {
				$tax_list_name[ $item_tax->get_rate_id() ] = $item_tax->get_label();
			}

			$tax_table_name      = "{$wpdb_obj->prefix}mp_seller_tax_rates";
			$seller_tax_rate_ids = array();

			if ( $wpdb_obj->get_var( "SHOW TABLES LIKE '$tax_table_name'" ) === $tax_table_name ) {
				$seller_tax_rate_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT `tax_rate_id` FROM {$wpdb_obj->prefix}mp_seller_tax_rates WHERE `user_id`=%d", $this->seller_id ), ARRAY_A );
				$seller_tax_rate_ids = array_map( 'intval', wp_list_pluck( $seller_tax_rate_ids, 'tax_rate_id' ) );
			}

			$seller_order_refund_data = $obj_commission->wkmp_get_seller_order_refund_data( $order_id, $this->seller_id );
			$order_status             = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT order_status from {$wpdb_obj->prefix}mpseller_orders where order_id = %d and seller_id = %d", $order_id, $this->seller_id ) );

			if ( empty( $order_status ) ) {
				$order_status = ( $seller_order instanceof \WC_Order ) ? $seller_order->get_status() : $order_status;
			}

			// Preparing order views data.
			$payment_gateway = wc_get_payment_gateway_by_order( $seller_order );
			$gateway_name    = __( 'Payment gateway', 'wk-marketplace' );

			if ( ! empty( $payment_gateway ) ) {
				$gateway_name = empty( $payment_gateway->method_title ) ? $payment_gateway->get_title() : $payment_gateway->method_title;
			}

			$reward_points = empty( $GLOBALS['reward'] ) ? 0 : $GLOBALS['reward']->get_woocommerce_reward_point_weightage();
			$order_details = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders mo WHERE mo.seller_id = %d and order_id=%d", $this->seller_id, $order_id ), ARRAY_A );
			$order_data    = array();
			$order_items   = $seller_order->get_items();

			foreach ( $order_details as $details ) {
				$product_id = empty( $details['product_id'] ) ? 0 : intval( $details['product_id'] );
				if ( empty( $product_id ) ) {
					continue;
				}

				$product_name = '';
				$variable_id  = 0;
				$item_key     = '';
				$meta_data    = array();
				$item_data    = array();
				$tax_total    = 0;

				$product_parent_id = wp_get_post_parent_id( $product_id );
				$order_product_id  = empty( $product_parent_id ) ? $product_id : $product_parent_id;

				foreach ( $order_items as $order_item_key => $order_item ) {
					if ( ! $order_item instanceof \WC_Order_Item_Product ) {
						continue;
					}

					$order_item_id = $order_item->get_product_id();
					$product_name  = $order_item->get_name();

					if ( intval( $order_product_id ) === $order_item_id ) {
						$variable_id = $order_item->get_variation_id();
						$item_key    = $order_item_key;
						$meta_data   = $order_item->get_formatted_meta_data();
						$tax_total   = $order_item->get_taxes()['total'];
						break;
					}
				}

				if ( empty( $product_name ) ) {
					$product_obj  = wc_get_product( $order_product_id );
					$product_name = ( $product_obj instanceof \WC_Product ) ? $product_obj->get_title() : __( '(no title)', 'wk-marketplace' );
				}

				if ( ! empty( $meta_data ) ) {
					foreach ( $meta_data as $meta ) {
						$item_data[] = array(
							'key'   => $meta->display_key,
							'value' => $meta->display_value,
						);
					}
				}

				$order_data[ $order_product_id ] = array(
					'product_name'        => $product_name,
					'variable_id'         => $variable_id,
					'qty'                 => empty( $details['quantity'] ) ? 0 : $details['quantity'],
					'item_key'            => $item_key,
					'product_total_price' => empty( $details['amount'] ) ? 0 : $details['amount'],
					'meta_data'           => $item_data,
					'tax_rates'           => $tax_total,
				);
			}

			$views_data = array(
				'seller_id'         => $this->seller_id,
				'order_id'          => $order_id,
				'gateway_name'      => $gateway_name,
				'reward_points'     => $reward_points,
				'seller_order_data' => $order_data,
			);

			require_once __DIR__ . '/wkmp-order-views.php';
		}

		/**
		 * Update order status
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		public function wkmp_order_update_status( $data ) {
			$wpdb_obj   = $this->wpdb;
			$table_name = $wpdb_obj->prefix . 'mpseller_orders';
			$error      = new \WP_Error();

			$order_status = empty( $data['mp-order-status'] ) ? '' : $data['mp-order-status'];
			$order_id     = empty( $data['mp-order-id'] ) ? 0 : intval( $data['mp-order-id'] );
			$seller_id    = empty( $data['mp-seller-id'] ) ? 0 : intval( $data['mp-seller-id'] );
			$old_status   = empty( $data['mp-old-order-status'] ) ? '' : $data['mp-old-order-status'];

			$order        = new \WC_Order( $order_id );
			$items        = $order->get_items();
			$author_array = array();

			foreach ( $items as $value ) {
				$author_array[] = get_post_field( 'post_author', $value->get_product_id() );
			}

			$order_author_count = count( $author_array );

			if ( $wpdb_obj->get_var( $wpdb_obj->prepare( 'SHOW TABLES LIKE %s;', $wpdb_obj->prefix . 'mpseller_orders' ) ) === $wpdb_obj->prefix . 'mpseller_orders' ) {
				if ( empty( $order_status ) ) {
					$error->add( 'status-error', esc_html__( 'Select status for order.', 'wk-marketplace' ) );
				} elseif ( $order_status === $old_status ) {
					$status = str_replace( 'wc-', '', $order_status );
					$error->add( 'status-error', esc_html__( 'Order status is already "', 'wk-marketplace' ) . ucfirst( $status ) . '".' );
				} else {
					$wpdb_obj->update(
						$table_name,
						array( 'order_status' => $order_status ),
						array(
							'order_id'  => $order_id,
							'seller_id' => $seller_id,
						),
						array( '%s' ),
						array( '%d', '%d' )
					);

					$author       = get_user_by( 'ID', $seller_id );
					$status_array = wc_get_order_statuses();

					$old_status = strpos( $old_status, 'wc-' ) ? $old_status : 'wc-' . $old_status;
					$new_status = strpos( $order_status, 'wc-' ) ? $order_status : 'wc-' . $order_status;

					$old_status = empty( $status_array[ $old_status ] ) ? str_replace( 'wc-', '', $order_status ) : $status_array[ $old_status ];
					$new_status = empty( $status_array[ $new_status ] ) ? str_replace( 'wc-', '', $new_status ) : $status_array[ $new_status ];

					$author_name = ( $author instanceof \WP_User ) ? $author->user_nicename : '';
					$note        = wp_sprintf( /* translators: %1$s: Vendor name, %2$s: Order status, %3$s: New order status. */ esc_html__( 'Vendor `{%1$s}` changed Order Status from {%2$s} to {%3$s} for it\'s own products.', 'wk-marketplace' ), $author_name, $old_status, $new_status );

					$query_result = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT count(*) as total FROM $table_name WHERE order_id = %d AND order_status = %s", $order_id, $order_status ) );

					if ( intval( $query_result[0]->total ) === $order_author_count ) {
						$order->update_status( $order_status, sprintf( /* translators: %s: Order status. */ esc_html__( "Status updated to {%s} based on status updated by vendor's.", 'wk-marketplace' ), $status_array[ $order_status ] ) );
					} else {
						$order->add_order_note( $note, 1 );
					}

					if ( is_admin() ) {
						?>
							<div class="wrap">
								<div class="notice notice-success">
									<p><?php esc_html_e( 'Order status updated.', 'wk-marketplace' ); ?></p>
								</div>
							</div>
							<?php
					} else {
						wc_print_notice( esc_html__( 'Order status updated.', 'wk-marketplace' ), 'success' );
					}

					do_action( 'wkmp_after_seller_update_order_status', $order, $data );
				}
			} else {
				$error->add( 'status-error', esc_html__( 'Database table does not exist.', 'wk-marketplace' ) );
			}

			if ( is_wp_error( $error ) ) {
				foreach ( $error->get_error_messages() as $value ) {
					if ( is_admin() ) {
						?>
						<div class="wrap">
							<div class="notice notice-error">
								<p><?php echo esc_html( $value ); ?></p>
							</div>
						</div>
						<?php
					} else {
						wc_print_notice( $value, 'error' );
					}
				}
			}
		}

		/**
		 * Display seller order invoice
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_order_invoice( $seller_id ) {
			global $wkmarketplace;

			$order_id = get_query_var( 'order_id' );
			$wpdb_obj = $this->wpdb;
			$order_id = base64_decode( $order_id );

			$this->seller_id   = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;
			$commission_helper = Common\WKMP_Commission::get_instance();
			$refund_data       = $commission_helper->wkmp_get_seller_order_refund_data( $order_id, $this->seller_id );
			$seller_order      = new \WC_Order( $order_id );

			if ( ! $seller_order instanceof \WC_Order || ( $seller_order instanceof \WC_Order && $seller_order->get_id() < 1 ) ) {
				wp_die( __( 'Invalid order request for invoice.', 'wk-marketplace' ) );
			}

			$obj_commission = Common\WKMP_Commission::get_instance();
			$mp_order_data  = $obj_commission->wkmp_get_seller_final_order_info( $order_id, $this->seller_id );

			if ( empty( $mp_order_data['product'] ) ) {
				wp_die( __( 'You don\'t have sufficient permission to view the invoice of this order.', 'wk-marketplace' ) );
			}

			$total_commission = empty( $mp_order_data['total_commission'] ) ? 0 : $mp_order_data['total_commission'];

			$data = array(
				'date_created'     => $seller_order->get_date_created()->format( 'Y F j, g:i a' ),
				'currency_symbol'  => get_woocommerce_currency_symbol( $seller_order->get_currency() ),
				'shipping_method'  => $seller_order->get_shipping_method(),
				'payment_method'   => $seller_order->get_payment_method_title(),
				'seller_info'      => $wkmarketplace->wkmp_get_seller_info( $this->seller_id ),
				'store_url'        => $wkmarketplace->wkmp_get_seller_store_url( $this->seller_id ),
				'billing_address'  => wp_kses_post( $seller_order->get_formatted_billing_address( esc_html__( 'N/A', 'wk-marketplace' ) ) ),
				'shipping_address' => wp_kses_post( $seller_order->get_formatted_shipping_address( esc_html__( 'N/A', 'wk-marketplace' ) ) ),
				'total_discount'   => empty( $mp_order_data['discount'] ) ? 0 : array_sum( $mp_order_data['discount'] ),
				'total_commission' => $total_commission,
				'customer_details' => array(
					'name'      => $seller_order->get_billing_first_name() . ' ' . $seller_order->get_billing_last_name(),
					'email'     => $seller_order->get_billing_email(),
					'telephone' => $seller_order->get_billing_phone(),
				),
			);

			$shipping_cost = 0;

			if ( 'null' !== $seller_order->get_total_shipping() ) {
				$ship_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'shipping_cost' ", $this->seller_id, $order_id ) );

				if ( ! empty( $ship_data ) ) {
					$shipping_cost         = $ship_data[0]->meta_value;
					$data['shipping_cost'] = wc_format_decimal( $shipping_cost, 2 );
				}
			}

			$seller_order_tax = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'seller_order_tax' ", $this->seller_id, $order_id ) );

			if ( empty( $seller_order_tax ) ) {
				$seller_order_tax = 0;
			}

			$data['ordered_products'] = array();
			$subtotal                 = 0;

			foreach ( $seller_order->get_items() as $product ) {
				$item_data = array();

				$value_data   = $product->get_data();
				$product_id   = $product->get_product_id();
				$product_post = get_post( $product_id );
				$meta_data    = $product->get_meta_data();

				$prod_seller_id = ( $product_post instanceof \WC_Post ) ? $product_post->post_author : 0;

				if ( ! empty( $meta_data ) ) {
					foreach ( $meta_data as $value1 ) {
						$item_data[] = $this->wkmp_validate_order_invoice_item_meta( $value1->get_data() );

						if ( empty( $prod_seller_id ) ) {
							$prod_meta      = $this->wkmp_validate_order_invoice_item_meta( $value1->get_data(), 'product_seller_id' );
							$prod_seller_id = ( ! empty( $prod_meta ) && ! empty( $prod_meta['value'] ) && is_numeric( $prod_meta['value'] ) ) ? intval( $prod_meta['value'] ) : $prod_seller_id;
						}
					}
				}

				$is_seller_product = apply_filters( 'wkmp_is_seller_product_invoice_data', intval( $prod_seller_id ) === intval( $this->seller_id ), $product, $this->seller_id );

				if ( $is_seller_product ) {
					$subtotal += $value_data['total'];

					$data['ordered_products'][ $product_id ] = array(
						'product_name' => $product['name'],
						'quantity'     => $value_data['quantity'],
						'variable_id'  => $product->get_variation_id(),
						'unit_price'   => number_format( $value_data['total'] / $value_data['quantity'], 2 ),
						'total_price'  => number_format( $value_data['total'], 2 ),
						'meta_data'    => $item_data,
					);
				}
			}

			$data['sub_total'] = number_format( $subtotal, 2 );

			if ( ! empty( $refund_data['refunded_amount'] ) ) {
				$data['total']             = number_format( $seller_order_tax + $subtotal + $shipping_cost - $refund_data['refunded_amount'] - $total_commission, 2 );
				$data['subtotal_refunded'] = number_format( $seller_order_tax + $subtotal + $shipping_cost, 2 );
			} else {
				$data['total'] = number_format( $seller_order_tax + $subtotal + $shipping_cost - $total_commission, 2 );
			}
			require_once __DIR__ . '/wkmp-order-invoice.php';
			die;
		}

		/**
		 * Validate metadata and replace seller id with seller shop link.
		 *
		 * @param array  $meta_data Meta data.
		 * @param string $return_type Return type.
		 *
		 * @return array
		 */
		public function wkmp_validate_order_invoice_item_meta( $meta_data, $return_type = '' ) {
			$common_functions = IncludeCommon\WKMP_Common_Functions::get_instance();
			$meta_value       = empty( $meta_data['value'] ) ? '' : $meta_data['value'];

			if ( ! empty( $meta_value ) ) {
				$meta_data['value'] = $common_functions->wkmp_validate_sold_by_order_item_meta( $meta_value, (object) $meta_data, $return_type );
			}

			return apply_filters( 'wkmp_formatted_order_meta_data', $meta_data );
		}
	}
}
