<?php
/**
 * Seller Data Helper
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Product_Data' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Seller_Product_Data {
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
			return static::$instance;
		}

		/**
		 * Get all seller's product.
		 *
		 * @param array $filter_data filter data.
		 *
		 * @return array|int
		 */
		public function wkmp_get_products( $filter_data ) {
			$result = array();

			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_marketplace' );

			$allowed_roles = array( 'administrator' );
			if ( 'edit_posts' === $capability ) {
				$allowed_roles[] = 'editor';
			}

			$c_user_id = get_current_user_id();
			$user      = get_user_by( 'id', $c_user_id );
			$c_roles   = ( $user instanceof \WP_User ) ? $user->roles : array();

			if ( empty( array_intersect( $allowed_roles, $c_roles ) ) ) {
				return empty( $filter_data['total'] ) ? $result : 0;
			}

			$query_args = array(
				'post_type'   => 'product',
				'post_status' => array( 'publish', 'draft' ),
			);

			if ( ! empty( $filter_data['search'] ) ) {
				$query_args['s'] = $filter_data['search'];
			}

			$filter_assign    = empty( $filter_data['filter_assigned'] ) ? '' : $filter_data['filter_assigned'];
			$filter_seller_id = empty( $filter_data['filter_seller_id'] ) ? '' : $filter_data['filter_seller_id'];

			if ( 'assigned' === $filter_assign && ! empty( $filter_seller_id ) ) {
				$query_args['author__in'] = array( $filter_seller_id );
			} elseif ( 'assigned' !== $filter_assign && ! empty( $filter_seller_id ) ) {
				$query_args['author__not_in'] = array( $filter_seller_id );
			}

			if ( ! empty( $filter_data['total'] ) ) {
				$total_query    = new \WP_Query( $query_args );
				$total_products = empty( $total_query->found_posts ) ? 0 : $total_query->found_posts;

				wp_reset_postdata();

				return apply_filters( 'wkmp_get_total_products', $total_products );
			}

			$orderby    = \WK_Caching::wk_get_request_data( 'orderby', array( 'default' => 'product' ) );
			$sort_order = \WK_Caching::wk_get_request_data( 'order', array( 'default' => 'ASC' ) );

			$query_args['posts_per_page'] = $filter_data['limit'];
			$query_args['offset']         = $filter_data['start'];
			$query_args['orderby']        = 'post_title';
			$query_args['order']          = $sort_order;

			if ( 'date' === $orderby ) {
				$query_args['orderby'] = 'post_date';
			}

			if ( 'price' === $orderby ) {
				$query_args['orderby']   = 'meta_value_num';
				$query_args['meta_key']  = '_price';
				$query_args['meta_type'] = 'NUMERIC';
			}

			$query = new \WP_Query( $query_args );

			foreach ( $query->posts as $product_data ) {
				$result[ $product_data->ID ] = $product_data->post_author;
			}
			wp_reset_postdata();

			return apply_filters( 'wkmp_get_products', $result );
		}
	}
}
