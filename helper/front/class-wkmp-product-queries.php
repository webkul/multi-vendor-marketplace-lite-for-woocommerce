<?php
/**
 * Product queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
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
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class
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
		 * Seller Products data
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data Filter data.
		 */
		public function wkmp_get_seller_products( $seller_id, $data = array() ) {
			// Using wc_get_products.
			$search         = empty( $data['search'] ) ? '' : $data['search'];
			$filter         = empty( $data['filter'] ) ? '' : $data['filter'];
			$get_count      = empty( $data['get_count'] ) ? false : $data['get_count'];
			$allowed_status = array( 'publish', 'draft' );

			$args = array(
				's'       => $search,
				'limit'   => -1,
				'return'  => 'ids',
				'author'  => $seller_id,
				'status'  => $allowed_status,
				'orderby' => 'ID',
				'order'   => 'DESC',
			);

			if ( in_array( $filter, $allowed_status, true ) ) {
				$args['status'] = array( $filter );
			}

			if ( 'outofstock' === $filter ) {
				$args['stock_status'] = $filter;
			}

			$on_sale_ids   = array();
			$low_stock_ids = array();

			if ( in_array( $filter, array( 'onsale', 'low_stock' ), true ) ) {
				$all_product_ids = wc_get_products( $args );

				if ( 'onsale' === $filter ) {
					$on_sale_ids = array_filter(
						$all_product_ids,
						function ( $product_id ) {
							$wc_product = wc_get_product( $product_id );
							return $wc_product->is_on_sale();
						}
					);
				}

				if ( 'low_stock' === $filter ) {
					$low_stock_ids = array_filter(
						$all_product_ids,
						function ( $product_id ) {
							$wc_product   = wc_get_product( $product_id );
							$stock_amount = $wc_product->get_stock_quantity();

							return ( $wc_product->managing_stock() && $stock_amount <= wc_get_low_stock_amount( $wc_product ) );
						}
					);
				}
			}

			if ( $get_count ) {
				$product_count_ids = ( 'onsale' === $filter ) ? $on_sale_ids : $low_stock_ids;

				if ( ! in_array( $filter, array( 'onsale', 'low_stock' ), true ) ) {
					$product_count_ids = wc_get_products( $args );
				}

				return apply_filters( 'wkmp_get_seller_total_products', empty( $product_count_ids ) ? 0 : count( $product_count_ids ) );
			}

			$page_no = empty( $data['page_no'] ) ? 0 : intval( $data['page_no'] );
			$limit   = empty( $data['limit'] ) ? 0 : intval( $data['limit'] );

			if ( ! empty( $limit ) ) {
				$args['page']  = $page_no;
				$args['limit'] = $limit;

				if ( 'onsale' === $filter ) {
					$args['include'] = $on_sale_ids;
				}
				if ( 'low_stock' === $filter ) {
					$args['include'] = $low_stock_ids;
				}
			}

			if ( ( 'onsale' === $filter && empty( $on_sale_ids ) || ( 'low_stock' === $filter ) && empty( $low_stock_ids ) ) ) {
				$product_ids = array();
			} else {
				$product_ids = wc_get_products( $args );
			}

			return apply_filters( 'wkmp_get_seller_products', $product_ids, $seller_id, $data );
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
