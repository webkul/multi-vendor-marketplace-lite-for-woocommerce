<?php
/**
 * Order queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WKMP_Dashboard_Queries' ) ) {
	/**
	 * Order queries class
	 */
	class WKMP_Dashboard_Queries {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Dashboard_Queries constructor.
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
			return static::$instance;
		}

		/**
		 * Get seller top 3 products
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return array $order_items
		 */
		public function wkmp_top_3_product( $seller_id ) {
			$wpdb_obj    = $this->wpdb;
			$order_items = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT wois.order_item_name AS 'item_name', post.ID, SUM( woi.meta_value ) AS 'sales', SUM( woi6.meta_value ) AS 'Total' FROM {$wpdb_obj->prefix}woocommerce_order_items AS wois LEFT JOIN {$wpdb_obj->prefix}woocommerce_order_itemmeta AS woi ON woi.order_item_id = wois.order_item_id LEFT JOIN {$wpdb_obj->prefix}woocommerce_order_itemmeta AS woi6 ON woi6.order_item_id = wois.order_item_id LEFT JOIN {$wpdb_obj->prefix}woocommerce_order_itemmeta AS woi_auther ON woi_auther.order_item_id = wois.order_item_id JOIN {$wpdb_obj->prefix}posts post ON post.ID = woi_auther.meta_value WHERE post.post_author=%d and post.post_status = 'publish' and woi.meta_key ='_qty' AND woi6.meta_key ='_line_total' AND woi_auther.meta_key =  '_product_id' GROUP BY wois.order_item_name ORDER BY sales DESC LIMIT 3", $seller_id ) );

			return apply_filters( 'wkmp_top_3_product', $order_items, $seller_id );
		}

		/**
		 * Get seller total product count
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return int $count
		 */
		public function wkmp_get_total_products_count( $seller_id ) {
			$product_ids = wc_get_products(
				array(
					'status' => array( 'publish' ),
					'author' => $seller_id,
					'return' => 'ids',
				)
			);

			$count = empty( $product_ids ) ? 0 : count( $product_ids );

			return apply_filters( 'wkmp_get_total_products_count', $count, $seller_id );
		}

		/**
		 * Get seller order item id
		 *
		 * @param array $filter_data Filter data.
		 *
		 * @return array array
		 */
		public function wkmp_get_order_item_id( $filter_data ) {
			global $wkmarketplace;
			$seller_id = empty( $filter_data['user_id'] ) ? 0 : $filter_data['user_id'];

			$final_data   = $wkmarketplace->wkmp_get_seller_order_table_data( $filter_data );
			$order_data   = empty( $final_data['data'] ) ? array() : $final_data['data'];
			$total_orders = empty( $final_data['total_orders'] ) ? 0 : $final_data['total_orders'];

			$order_ids = wp_list_pluck( $order_data, 'order_id' );
			$items     = wp_list_pluck( $order_data, 'item_ids' );
			$item_ids  = array();

			foreach ( $items as $ids ) {
				$item_ids = array_unique( array_merge( $item_ids, $ids ) );
			}

			$item_ids_str  = implode( ',', $item_ids );
			$order_ids_str = implode( ',', $order_ids );

			$results = array(
				'order_item_id' => $item_ids_str,
				'order_id'      => $order_ids_str,
				'total_orders'  => $total_orders,
			);

			return apply_filters( 'wkmp_get_order_item_id', $results, $seller_id );
		}

		/**
		 * Get seller stats
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return array $result
		 */
		public function wkmp_get_sale_stats( $seller_id ) {
			$commission_helper = Common\WKMP_Commission::get_instance();
			$result            = $commission_helper->wkmp_get_seller_commission_info( $seller_id, 'seller_total_ammount, paid_amount, total_refunded_amount' );

			return apply_filters( 'wkmp_get_sale_stats', $result, $seller_id );
		}

		/**
		 * Get seller round charts
		 *
		 * @param int $amount Amount.
		 *
		 * @return array|mixed
		 */
		public function wkmp_round_chart_totals( $amount ) {
			if ( is_array( $amount ) ) {
				return array( $amount[0], wc_format_decimal( $amount[1], wc_get_price_decimals() ) );
			} else {
				return wc_format_decimal( $amount, wc_get_price_decimals() );
			}
		}
	}
}
