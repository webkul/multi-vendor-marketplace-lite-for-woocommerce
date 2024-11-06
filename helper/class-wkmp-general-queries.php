<?php
/**
 * General queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WkMarketplace\Helper\Admin as AdminHelper;
use WkMarketplacePro\Helper;

if ( ! class_exists( 'WKMP_General_Queries' ) ) {

	/**
	 * General queries class
	 */
	class WKMP_General_Queries {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Env data.
		 *
		 * @var $instance
		 */
		private static $env = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
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
			self::$env = 'pur(has?_';
			return static::$instance;
		}

		/**
		 * Get seller page slug.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_seller_page_slug() {
			$seller_page_id = get_option( 'wkmp_seller_page_id' );
			$page_name      = apply_filters( 'wkmp_seller_page_slug', get_option( 'wkmp_seller_page_slug', 'seller' ) );
			if ( $seller_page_id > 0 ) {
				$seller_page = get_post( $seller_page_id );
				$page_name   = isset( $seller_page->post_name ) ? $seller_page->post_name : $page_name;
			}

			return apply_filters( 'wkmp_seller_page_slug', $page_name, $seller_page_id );
		}

		/**
		 * Check if user is seller.
		 *
		 * @param int $user_id User id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_check_if_seller( $user_id ) {
			$wpdb_obj = $this->wpdb;
			$data     = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->prefix}mpsellerinfo WHERE user_id=%d and seller_value=%s", intval( $user_id ), esc_sql( 'seller' ) ) );

			return apply_filters( 'wkmp_is_user_seller', $data, $user_id );
		}

		/**
		 * Set new seller meta data
		 *
		 * @param int $user_id User ID.
		 *
		 * @return $meta_id
		 */
		public function wkmp_set_seller_meta( $user_id ) {
			$wpdb_obj     = $this->wpdb;
			$seller_table = $wpdb_obj->prefix . 'mpsellerinfo';
			$user         = get_user_by( 'ID', $user_id );

			$seller_data = array(
				'user_id'      => intval( $user_id ),
				'seller_key'   => 'role',
				'seller_value' => 'customer',
			);

			if ( get_option( '_wkmp_auto_approve_seller', true ) ) {
				$seller_data['seller_value'] = 'seller';
				$user->set_role( 'wk_marketplace_seller' );
			} else {
				$user->set_role( get_option( 'default_role' ) );
			}

			$meta_id = $wpdb_obj->insert( $seller_table, $seller_data );

			return apply_filters( 'wkmp_new_sellerinfo_id', $meta_id, $user_id );
		}

		/**
		 * Get Seller id by shop address
		 *
		 * @param string $shop_address shop address.
		 *
		 * @return int $seller_id Seller id.
		 */
		public function wkmp_get_seller_id_by_shop_address( $shop_address ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s AND meta_value=%s", esc_sql( 'shop_address' ), esc_sql( $shop_address ) ) );

			return apply_filters( 'wkmp_get_seller_id_by_shop_address', $seller_id, $shop_address );
		}

		/**
		 * Get Seller id by shop name
		 *
		 * @param string $shop_name shop address.
		 *
		 * @return int $seller_id seller id.
		 */
		public function wkmp_get_seller_id_by_shop_name( $shop_name ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_id FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s AND meta_value=%s", esc_sql( 'shop_name' ), esc_sql( $shop_name ) ) );

			return apply_filters( 'wkmp_get_seller_id_by_shop_name', $seller_id, $shop_name );
		}

		/**
		 * Get pending seller id for given user id.
		 *
		 * @param int $user_id User id.
		 *
		 * @return int
		 */
		public function wkmp_get_pending_seller_id( $user_id ) {
			$wpdb_obj = $this->wpdb;
			return $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT `seller_id` FROM {$wpdb_obj->prefix}mpsellerinfo WHERE user_id=%d AND `seller_value`='customer'", $user_id ) );
		}

		/**
		 * Delete ICL user meta for WPML.
		 *
		 * @param int    $user_id User id.
		 * @param string $key User meta key.
		 *
		 * @return void
		 */
		public function wkmp_delete_icl_user_meta( $user_id, $key = '' ) {
			$user_id  = empty( $user_id ) ? get_current_user_id() : $user_id;
			$wpdb_obj = $this->wpdb;
			delete_user_meta( $user_id, $wpdb_obj->prefix . $key );
		}

		/**
		 * Get Seller order data for Seller front end order history and seller separate page order list.
		 *
		 * @param array $query_args Query args.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_order_data( $query_args ) {
			$wpdb_obj   = $this->wpdb;
			$user_id    = empty( $query_args['user_id'] ) ? get_current_user_id() : intval( $query_args['user_id'] );
			$search     = empty( $query_args['search'] ) ? 0 : intval( $query_args['search'] );
			$orderby    = empty( $query_args['order_by'] ) ? 'order_id' : $query_args['order_by'];
			$sort_order = empty( $query_args['sort_order'] ) ? 'desc' : $query_args['sort_order'];
			$per_page   = empty( $query_args['per_page'] ) ? '-1' : intval( $query_args['per_page'] );
			$offset     = empty( $query_args['offset'] ) ? 0 : intval( $query_args['offset'] );
			$filter     = empty( $query_args['filter'] ) ? '' : $query_args['filter'];
			$filter     = empty( $filter ) ? '' : ( strpos( $filter, 'wc-' ) !== false ? $filter : 'wc-' . $filter );

			$order_approval_enabled = get_user_meta( $user_id, '_wkmp_enable_seller_order_approval', true );

			$order_ids_sql = "SELECT count(mo.order_id), mo.order_id FROM {$wpdb_obj->prefix}mporders mo";

			if ( $order_approval_enabled ) {
				$order_ids_sql .= " LEFT JOIN {$wpdb_obj->prefix}mporders_meta mpom ON ( mo.order_id = mpom.order_id )";
			}

			if ( ! empty( $filter ) ) {
				$order_ids_sql .= " LEFT JOIN {$wpdb_obj->prefix}posts p ON ( mo.order_id = p.ID ) LEFT JOIN {$wpdb_obj->prefix}wc_orders wco ON ( mo.order_id = wco.id)";
			}

			$order_ids_sql = apply_filters( 'wkmp_get_seller_orders_before_where_query', $order_ids_sql, $query_args, $user_id );

			$order_ids_sql .= $wpdb_obj->prepare( ' WHERE mo.seller_id = %d', $user_id );

			if ( $order_approval_enabled ) {
				$order_ids_sql .= " AND mpom.meta_key='paid_status' AND mpom.meta_value IN ('paid','approved')";
			}

			if ( ! empty( $search ) ) {
				$order_ids_sql .= $wpdb_obj->prepare( ' AND mo.order_id = %d', $search );
			}

			if ( ! empty( $filter ) ) {
				$order_ids_sql .= $wpdb_obj->prepare( ' AND  (p.post_status = %s OR wco.status = %s)', $filter, $filter );
			}

			$order_ids_sql = apply_filters( 'wkmp_get_seller_orders_after_where_query', $order_ids_sql, $query_args, $user_id );

			$order_ids_sql   .= $wpdb_obj->prepare( ' GROUP BY mo.order_id ORDER BY mo.order_id %1s', $sort_order );
			$order_ids_sql    = apply_filters( 'wkmp_get_seller_orders_query', $order_ids_sql, $query_args, $user_id );
			$order_ids_result = $wpdb_obj->get_results( apply_filters( 'wkmp_get_seller_orders_total_query', $order_ids_sql, $query_args, $user_id ), ARRAY_A );

			$order_ids    = wp_list_pluck( $order_ids_result, 'order_id' );
			$total_orders = is_iterable( $order_ids ) ? count( $order_ids ) : 0;

			if ( intval( $per_page ) > 0 && $total_orders > 0 ) {
				$order_ids_sql   .= $wpdb_obj->prepare( ' LIMIT %d, %d', $offset, $per_page );
				$order_ids_result = $wpdb_obj->get_results( apply_filters( 'wkmp_get_seller_orders_limit_query', $order_ids_sql, $query_args, $user_id ), ARRAY_A );
				$order_ids        = wp_list_pluck( $order_ids_result, 'order_id' );
			}

			$order_details = array();
			$order_id_list = array();

			if ( ! empty( $order_ids ) ) {
				$order_ids_str = implode( ',', $order_ids );

				$order_details_sql = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders mo WHERE mo.order_id IN (%1s) ORDER BY mo.%2s %3s", $order_ids_str, $orderby, $sort_order );
				$order_details     = $wpdb_obj->get_results( apply_filters( 'wkmp_get_seller_orders_details_query', $order_details_sql, $query_args, $user_id ), ARRAY_A );

				$order_details = apply_filters( 'mp_vendor_split_orders', $order_details, $user_id );
				$order_id_list = apply_filters( 'wkmp_get_seller_orders', $order_ids, $user_id );
				$order_id_list = array_map( 'intval', $order_ids );
			}

			$data         = array();
			$status_array = wc_get_order_statuses();

			foreach ( $order_details as $mp_order_data ) {
				$order_id = empty( $mp_order_data['order_id'] ) ? 0 : intval( $mp_order_data['order_id'] );
				if ( in_array( $order_id, $order_id_list, true ) ) {
					$seller_order = wc_get_order( $order_id );

					if ( ! is_a( $seller_order, 'WC_Order' ) ) {
						--$total_orders;
						continue;
					}

					$qty     = empty( $mp_order_data['quantity'] ) ? 0 : $mp_order_data['quantity'];
					$item_id = empty( $mp_order_data['product_id'] ) ? 0 : intval( $mp_order_data['product_id'] );
					$total   = empty( $mp_order_data['seller_amount'] ) ? 0 : $mp_order_data['seller_amount'];

					if ( array_key_exists( $order_id, $data ) ) {
						$item_ids = empty( $data[ $order_id ]['item_ids'] ) ? array() : $data[ $order_id ]['item_ids'];

						if ( ! empty( $item_id ) && ! in_array( $item_id, $item_ids, true ) ) {
							array_push( $item_ids, $item_id );
						}

						$total_qty  = $data[ $order_id ]['total_qty'];
						$total_qty += $qty;

						$total_amount  = $data[ $order_id ]['order_total'];
						$total_amount += $total;

						$data[ $order_id ]['total_qty']   = $total_qty;
						$data[ $order_id ]['order_total'] = $total_amount;
						$data[ $order_id ]['item_ids']    = $item_ids;
					} else {
						$item_ids        = array( $item_id );
						$mp_order_status = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT order_status from {$wpdb_obj->prefix}mpseller_orders where order_id = %d and seller_id = %d", $order_id, $user_id ) );
						$date_created    = '';
						$order_currency  = get_woocommerce_currency();

						if ( $seller_order instanceof \WC_Order ) {
							$mp_order_status = empty( $mp_order_status ) ? 'wc-' . $seller_order->get_status() : $mp_order_status;
							$date_created    = date_format( $seller_order->get_date_created(), 'Y-m-d H:i:s' );
							$order_currency  = $seller_order->get_currency();
						}

						$display_status = empty( $status_array[ $mp_order_status ] ) ? '-' : $status_array[ $mp_order_status ];

						$data[ $order_id ] = array(
							'order_id'        => $order_id,
							'order_status'    => ucfirst( $display_status ),
							'wc_order_status' => $mp_order_status,
							'order_date'      => $date_created,
							'total_qty'       => $qty,
							'item_ids'        => $item_ids,
							'order_total'     => $total,
							'order_currency'  => get_woocommerce_currency_symbol( $order_currency ),
							'action'          => '<a href="' . admin_url( 'admin.php?page=order-history&action=view&oid=' . $order_id ) . '" class="button button-primary">' . esc_html__( 'View', 'wk-marketplace' ) . '</a>',
							'view'            => wc_get_endpoint_url( get_option( '_wkmp_order_history_endpoint', 'sellers-orders' ) . '/' . intval( $order_id ) ),
						);
					}
				}
			}

			foreach ( $data as $order_id => $order_data ) {
				$seller_order_meta = $wpdb_obj->get_results( $wpdb_obj->prepare( "Select meta_key, meta_value from {$wpdb_obj->prefix}mporders_meta where seller_id = %d and order_id = %d and meta_key IN ('seller_order_tax','_wkmp_refund_status','shipping_cost') ", $user_id, $order_id ), ARRAY_A );
				$ship_total        = 0;
				$tax_total         = 0;
				$refund_data       = array();

				foreach ( $seller_order_meta as $order_meta ) {
					if ( 'shipping_cost' === $order_meta['meta_key'] ) {
						$ship_total = floatval( $order_meta['meta_value'] );
					}
					if ( 'seller_order_tax' === $order_meta['meta_key'] ) {
						$tax_total = floatval( $order_meta['meta_value'] );
					}
					if ( '_wkmp_refund_status' === $order_meta['meta_key'] ) {
						$refund_data = maybe_unserialize( $order_meta['meta_value'] );
					}
				}
				$total = $order_data['order_total'] + $ship_total + $tax_total;

				if ( ! empty( $refund_data['refunded_amount'] ) ) {
					$total = '<del>' . $total . '</del> ' . $order_data['order_currency'] . round( round( $total, 2 ) - $refund_data['refunded_amount'], 2 );
				}
				$total = apply_filters( 'wkmp_add_order_fee_to_total', $total, $order_id );

				$data[ $order_id ]['order_total'] = $order_data['order_currency'] . $total . ' ' . esc_html__( 'for', 'wk-marketplace' ) . ' ' . $order_data['total_qty'] . ' ' . esc_html__( ' items', 'wk-marketplace' );
			}

			return array(
				'data'         => $data,
				'total_orders' => $total_orders,
			);
		}

		/**
		 * Validate seller registrations. If new seller registration is allowed.
		 *
		 * @return bool
		 */
		public function wkmp_validate_seller_registration() {
			global $wkmarketplace;

			$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();

			if ( ! $pro_disabled && class_exists( 'WkMarketplacePro\Helper\WKMP_General_Queries_Pro' ) ) {
				$pro = Helper\WKMP_General_Queries_Pro::get_instance();
				return method_exists( $pro, __FUNCTION__ ) ? $pro->{__FUNCTION__}() : false;
			}

			$seller_db_obj = AdminHelper\WKMP_Seller_Data::get_instance();

			return ( intval( $seller_db_obj->wkmp_get_total_sellers( array( 'verified' => true ) ) ) < absint( $seller_db_obj->wkmp_get_lite_allowed_sellers() ) );
		}

		/**
		 * Get Pro sec data.
		 *
		 * @param string $data Serialized data.
		 *
		 * @return int
		 */
		public static function wkmp_get_pro_sec_data( $data ) {
			$srd = maybe_unserialize( $data );
			$srd = empty( $srd ) ? array() : $srd;
			$pcd = str_replace( '?', 'e', str_replace( '(', 'c', self::$env . '(od?' ) );

			return ( 5 === count( $srd ) && isset( $srd[ $pcd ] ) ) ? strlen( $srd[ $pcd ] ) : count( $srd );
		}

		/**
		 * Get Shop followers ids by seller id
		 *
		 * @param int $seller_id seller id.
		 *
		 * @return array $follower ids.
		 */
		public function wkmp_get_seller_follower_ids( $seller_id ) {
			$wpdb_obj          = $this->wpdb;
			$shop_follower_ids = array();

			$seller_id = empty( $seller_id ) ? 0 : intval( $seller_id );

			if ( empty( $seller_id ) ) {
				return $shop_follower_ids;
			}

			$rows = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT user_id, meta_value FROM {$wpdb_obj->base_prefix}usermeta WHERE meta_key=%s", esc_sql( 'favourite_seller' ) ) );

			if ( count( $rows ) > 0 ) {
				foreach ( $rows as $row ) {
					if ( ! empty( $row->meta_value ) ) {
						$follower_id = empty( $row->user_id ) ? 0 : intval( $row->user_id );
						$seller_ids  = array_map( 'intval', explode( ',', $row->meta_value ) );

						if ( in_array( $seller_id, $seller_ids, true ) && $follower_id > 0 && ! in_array( $follower_id, $shop_follower_ids, true ) ) {
							array_push( $shop_follower_ids, $follower_id );
						}
					}
				}
			}

			return apply_filters( 'wkmp_get_seller_followers', $shop_follower_ids, $seller_id );
		}

		/**
		 * Add\update Shop follower.
		 *
		 * @param int $seller_id Seller id.
		 * @param int $customer_id Customer id.
		 *
		 * @return string
		 */
		public function wkmp_update_shop_followers( $seller_id, $customer_id ) {
			$result = 'added';
			if ( $seller_id > 0 && $customer_id > 0 ) {
				$sellers    = get_user_meta( $customer_id, 'favourite_seller', true );
				$sellers    = $sellers ? explode( ',', $sellers ) : array();
				$seller_ids = empty( $sellers ) ? array() : array_map( 'intval', $sellers );
				$key        = array_search( $seller_id, $seller_ids, true );

				if ( false !== $key ) {
					unset( $sellers[ $key ] );
					$result = 'removed';
				} else {
					$sellers[] = $seller_id;
				}

				update_user_meta( $customer_id, 'favourite_seller', implode( ',', $sellers ) );
			}
			return $result;
		}

		/**
		 * Get all Favorite Sellers for a customer.
		 *
		 * @param int $customer_id Customer id.
		 *
		 * @return array Favorite seller ids.
		 */
		public function wkmp_get_customer_favorite_seller_ids( $customer_id ) {
			global $wkmarketplace;

			$favorite_seller_ids = array();

			if ( $customer_id > 0 ) {
				$favorite_seller_ids = get_user_meta( $customer_id, 'favourite_seller', true );
				$favorite_seller_ids = empty( $favorite_seller_ids ) ? array() : explode( ',', $favorite_seller_ids );
				$favorite_seller_ids = empty( $favorite_seller_ids ) ? array() : array_map( 'intval', $favorite_seller_ids );
				$favorite_seller_ids = array_filter(
					$favorite_seller_ids,
					function ( $seller_id ) use ( $wkmarketplace ) {
						return $wkmarketplace->wkmp_user_is_seller( $seller_id );
					}
				);
			}

			return $favorite_seller_ids;
		}
	}
}
