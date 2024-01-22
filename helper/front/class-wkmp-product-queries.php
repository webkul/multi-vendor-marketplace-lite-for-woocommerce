<?php
/**
 * Product queries class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Product_Queries' ) ) {

	/**
	 * General queries class
	 */
	class WKMP_Product_Queries {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Seller Products data
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data Filter data.
		 */
		public function wkmp_get_seller_products( $seller_id, $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT ID, post_title, post_status FROM {$wpdb_obj->prefix}posts WHERE post_type = 'product' AND post_status IN ('draft','publish') AND post_author = %d", esc_attr( $seller_id ) );

			if ( isset( $data['filter_name'] ) && $data['filter_name'] ) {
				$sql .= $wpdb_obj->prepare( ' AND post_title LIKE %s', '%' . esc_attr( $data['filter_name'] ) . '%' );
			}

			$sql .= $wpdb_obj->prepare( ' ORDER BY ID DESC LIMIT %d, %d', esc_attr( $data['start'] ), esc_attr( $data['limit'] ) );

			$products = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_seller_products', $products, $seller_id );
		}

		/**
		 * Seller Products total count
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data Filter data.
		 *
		 * @return array $products
		 */
		public function wkmp_get_seller_total_products( $seller_id, $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$wpdb_obj->prefix}posts WHERE post_type = 'product' AND post_status IN ('draft','publish') AND post_author = %d", esc_attr( $seller_id ) );

			if ( isset( $data['filter_name'] ) && $data['filter_name'] ) {
				$sql .= $wpdb_obj->prepare( ' AND post_title LIKE %s', '%' . esc_attr( $data['filter_name'] ) . '%' );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_seller_total_products', $total );
		}

		/**
		 * Get product image
		 *
		 * @param int    $product_id ID.
		 * @param string $meta_key Key string.
		 *
		 * @return mixed|string|void
		 */
		public function wkmp_get_product_image( $product_id, $meta_key ) {
			$id = get_post_meta( $product_id, $meta_key, true );

			if ( ! $id ) {
				return '';
			}

			$product_image = get_post_meta( $id, '_wp_attached_file', true );

			return apply_filters( 'wkmp_product_image', $product_image, $product_id, $meta_key );
		}
	}
}
