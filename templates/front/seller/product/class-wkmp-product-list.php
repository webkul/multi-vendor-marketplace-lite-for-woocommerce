<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Product;

use WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Product_List' ) ) {
	/**
	 * Seller products list class.
	 *
	 * Class WKMP_Product_List
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Product
	 */
	class WKMP_Product_List {
		/**
		 * DB Product Object.
		 *
		 * @var Front\WKMP_Product_Queries
		 */
		private $db_product_obj;

		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Product_List constructor.
		 */
		public function __construct() {
			$this->db_product_obj = Front\WKMP_Product_Queries::get_instance();

			$nonce = \WK_Caching::wk_get_request_data( 'wkmp-delete-product-nonce', array( 'method' => 'post' ) );

			// Delete multiple product.
			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-delete-product-nonce-action' ) ) {
				$product_ids = empty( $_POST['selected'] ) ? array() : wc_clean( wp_unslash( $_POST['selected'] ) );

				$this->wkmp_delete_product( $product_ids );
			}

			$this->update_minimum_order_amount();
			$this->update_per_product_settings();
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
		 * Product list.
		 *
		 * @param int    $seller_id Seller id.
		 * @param int    $page_no Page no.
		 * @param string $filter Filter to apply.
		 *
		 * @return void
		 */
		public function wkmp_product_list( $seller_id, $page_no = 1, $filter = '' ) {
			global $wkmarketplace;

			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;
			$nonce_add       = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_add ) && wp_verify_nonce( $nonce_add, 'wkmp_add_product_submit_nonce_action' ) ) {
				$posted_data = array(
					'dynamic_sku_enabled' => get_user_meta( $this->seller_id, '_wkmp_enable_seller_dynamic_sku', true ),
					'dynamic_sku_prefix'  => get_user_meta( $this->seller_id, '_wkmp_dynamic_sku_prefix', true ),
				);

				$posted_data['product_sku']     = empty( $_POST['product_sku'] ) ? '' : wc_clean( wp_unslash( $_POST['product_sku'] ) );
				$posted_data['product_name']    = empty( $_POST['product_name'] ) ? '' : wc_clean( wp_unslash( $_POST['product_name'] ) );
				$posted_data['seller_id']       = empty( $_POST['seller_id'] ) ? '' : intval( wp_unslash( $_POST['seller_id'] ) );
				$posted_data['sell_pr_id']      = empty( $_POST['sell_pr_id'] ) ? '' : intval( wp_unslash( $_POST['sell_pr_id'] ) );
				$posted_data['wk-mp-stock-qty'] = empty( $_POST['wk-mp-stock-qty'] ) ? '' : intval( wp_unslash( $_POST['wk-mp-stock-qty'] ) );
				$posted_data['regu_price']      = empty( $_POST['regu_price'] ) ? '' : wc_clean( $_POST['regu_price'] );
				$posted_data['sale_price']      = empty( $_POST['sale_price'] ) ? '' : wc_clean( $_POST['sale_price'] );

				$product_form = WKMP_Product_Form::get_instance();
				$errors       = $product_form->wkmp_product_validation( $posted_data );

				if ( ! empty( $errors ) ) {
					foreach ( $errors as $value ) {
						wc_print_notice( $value, 'error' );
					}
				} else {
					$posted_data['seller_id'] = empty( $posted_data['seller_id'] ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $posted_data['seller_id'];

					$posted_data['product_desc'] = empty( $_POST['product_desc'] ) ? '' : wp_kses_post( $_POST['product_desc'] );
					$posted_data['short_desc']   = empty( $_POST['short_desc'] ) ? '' : wp_kses_post( $_POST['short_desc'] );

					if ( did_action( 'marketplace_process_product_meta' ) < 1 ) { // To avoid double product creation for case my-account is created with Elementor and its Powerpack and the WC My-Account shortcode running more than 1 time. Case: Ticket: #496209.
						$this->wkmp_add_new_product( $posted_data );
					}
				}
			} elseif ( ! empty( $nonce_add ) ) {
				wc_print_notice( esc_html__( 'Sorry!! security check failed. Please try again to add product!!', 'wk-marketplace' ), 'error' );
			}

			$search = '';
			$nonce  = \WK_Caching::wk_get_request_data( 'wkmp_product_search_nonce' );

			// Filter product.
			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp_product_search_nonce_action' ) ) {
				$search = \WK_Caching::wk_get_request_data( 'wkmp_search' );
			}

			$limit = get_user_meta( $this->seller_id, '_wkmp_products_per_page', true );
			$limit = apply_filters( 'wkmp_front_per_page_products', empty( $limit ) ? 10 : intval( $limit ) );

			$filter_data = array(
				'page_no' => $page_no,
				'limit'   => $limit,
				'search'  => $search,
				'filter'  => $filter,
			);

			$product_ids = $this->db_product_obj->wkmp_get_seller_products( $this->seller_id, $filter_data );

			$filter_data['get_count'] = true;

			$total_count = $this->db_product_obj->wkmp_get_seller_products( $this->seller_id, $filter_data );

			$products             = array();
			$stock_status_options = wc_get_product_stock_status_options();

			foreach ( $product_ids as $product_id ) {
				$product_obj = wc_get_product( $product_id );

				$img   = wp_get_attachment_image_src( get_post_meta( $product_id, '_thumbnail_id', true ) );
				$image = wc_placeholder_img_src();

				if ( $img ) {
					$image = $img[0];
				}

				$price                = $product_obj->get_price_html() ? wp_kses_post( $product_obj->get_price_html() ) : '<span class="na">&ndash;</span>';
				$product_stock_status = $product_obj->get_stock_status();

				$products[] = array(
					'product_id'     => $product_id,
					'name'           => $product_obj->get_title(),
					'product_href'   => get_permalink( $product_id ),
					'status'         => ucfirst( $product_obj->get_status() ),
					'image'          => $image,
					'stock'          => ! empty( $stock_status_options[ $product_stock_status ] ) ? $stock_status_options[ $product_stock_status ] : ucfirst( $product_stock_status ),
					'stock_quantity' => 'outofstock' === $product_stock_status ? 0 : $product_obj->get_stock_quantity(),
					'price'          => $price,
					'edit'           => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' ) . '/' . intval( $product_id ),
				);
			}

			$url        = wc_get_endpoint_url( get_option( '_wkmp_product_list_endpoint', 'seller-products' ) );
			$url       .= empty( $filter ) ? '' : '/filter/' . $filter;
			$pagination = $wkmarketplace->wkmp_get_pagination( $total_count, $page_no, $limit, $url );

			$wkmp_min_order_enabled         = get_option( '_wkmp_enable_minimum_order_amount', false );
			$wkmp_min_order_amount          = get_user_meta( $this->seller_id, '_wkmp_minimum_order_amount', true );
			$wkmp_product_qty_limit_enabled = get_option( '_wkmp_enable_product_qty_limit', false );
			$wkmp_max_product_qty           = get_user_meta( $this->seller_id, '_wkmp_max_product_qty_limit', true );

			require_once __DIR__ . '/wkmp-seller-product-list.php';
		}

		/**
		 * Delete seller product
		 *
		 * @param array $product_ids product ids.
		 */
		public function wkmp_delete_product( $product_ids ) {
			$seller_id = empty( $this->seller_id ) ? get_current_user_id() : intval( $this->seller_id );
			foreach ( $product_ids as $product_id ) {
				$product_author = get_post_field( 'post_author', $product_id );

				if ( intval( $product_author ) === $seller_id ) {
					wp_delete_post( $product_id );
				}
			}

			wc_print_notice( esc_html__( 'Product(s) deleted successfully.', 'wk-marketplace' ), 'success' );
		}

		/**
		 * Updating minimum order settings.
		 */
		public function update_minimum_order_amount() {
			$nonce_min_update = \WK_Caching::wk_get_request_data( 'wkmp-min-order-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_min_update ) && wp_verify_nonce( $nonce_min_update, 'wkmp-min-order-nonce-action' ) ) {
				$qty    = empty( $_POST['_wkmp_max_product_qty_limit'] ) ? 0 : intval( wp_unslash( $_POST['_wkmp_max_product_qty_limit'] ) );
				$amount = empty( $_POST['_wkmp_minimum_order_amount'] ) ? '' : wc_clean( $_POST['_wkmp_minimum_order_amount'] );

				$seller_id = $this->seller_id > 0 ? $this->seller_id : get_current_user_id();

				if ( empty( $amount ) ) {
					delete_user_meta( $seller_id, '_wkmp_minimum_order_amount' );
				} else {
					$amount = number_format( $amount, 2 );
					update_user_meta( $seller_id, '_wkmp_minimum_order_amount', $amount );
				}

				if ( empty( $qty ) ) {
					delete_user_meta( $seller_id, '_wkmp_max_product_qty_limit' );
				} else {
					update_user_meta( $seller_id, '_wkmp_max_product_qty_limit', $qty );
				}
			}
		}

		/**
		 * Adding a new product.
		 *
		 * @param array $posted_data Posted form data.
		 *
		 * @return void
		 */
		public function wkmp_add_new_product( $posted_data ) {
			$nonce_add    = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );
			$product_name = wp_strip_all_tags( $posted_data['product_name'] );

			if ( ! empty( $product_name ) && ! empty( $nonce_add ) && wp_verify_nonce( $nonce_add, 'wkmp_add_product_submit_nonce_action' ) ) {
				$product_dsc        = empty( $posted_data['product_desc'] ) ? '' : $posted_data['product_desc'];
				$product_short_desc = empty( $posted_data['short_desc'] ) ? '' : $posted_data['short_desc'];

				$product_status = get_option( '_wkmp_allow_seller_to_publish', true ) ? 'publish' : 'draft';

				$seller_id   = empty( $posted_data['seller_id'] ) ? get_current_user_id() : $posted_data['seller_id'];
				$price       = is_null( $posted_data['regu_price'] ) ? '' : wc_format_decimal( trim( stripslashes( $posted_data['regu_price'] ) ) );
				$sales_price = is_null( $posted_data['sale_price'] ) ? '' : wc_format_decimal( trim( stripslashes( $posted_data['sale_price'] ) ) );

				$product_data = array(
					'post_author'           => $seller_id,
					'post_content'          => $product_dsc,
					'post_content_filtered' => $product_short_desc,
					'post_title'            => htmlspecialchars( $product_name ),
					'post_excerpt'          => $product_short_desc,
					'post_status'           => $product_status,
					'post_type'             => 'product',
					'post_name'             => wp_strip_all_tags( $product_name ),
				);

				$sell_pr_id = wp_insert_post( $product_data );

				if ( $sell_pr_id > 0 ) {
					$thumbnail_id = empty( $_POST['product_thumb_image_mp'] ) ? '' : intval( wp_unslash( $_POST['product_thumb_image_mp'] ) );

					add_post_meta( $sell_pr_id, '_thumbnail_id', $thumbnail_id );

					$data = array(
						'ID' => $sell_pr_id,
					);

					if ( wp_update_post( $data ) ) {
						$field = array(); // custom fields.

						do_action( 'marketplace_insert_product_meta', $sell_pr_id, $field );

						$posted_sku = $posted_data['product_sku'];

						if ( ! empty( $posted_sku ) ) {
							update_post_meta( $sell_pr_id, '_sku', wp_strip_all_tags( $posted_sku ) );
							if ( $posted_data['dynamic_sku_enabled'] && ! empty( $posted_data['dynamic_sku_prefix'] ) ) {
								update_post_meta( $sell_pr_id, '_sku_prefix', wp_strip_all_tags( $posted_data['dynamic_sku_prefix'] ) );
							}
						}

						if ( is_numeric( $price ) || empty( $price ) ) {
							add_post_meta( $sell_pr_id, '_regular_price', $price );
						}

						if ( is_numeric( $sales_price ) && is_numeric( $price ) && $sales_price < $price ) {
							add_post_meta( $sell_pr_id, '_sale_price', $sales_price );
							add_post_meta( $sell_pr_id, '_price', $sales_price );
						} else {
							add_post_meta( $sell_pr_id, '_sale_price', '' );
							if ( is_numeric( $price ) || empty( $price ) ) {
								add_post_meta( $sell_pr_id, '_price', $price );
							}
						}

						$downloadable   = empty( $_POST['_downloadable'] ) ? '' : wc_clean( wp_unslash( $_POST['_downloadable'] ) );
						$product_status = empty( $_POST['mp_product_status'] ) ? '' : wc_clean( wp_unslash( $_POST['mp_product_status'] ) );
						$sale_from      = empty( $_POST['sale_from'] ) ? '' : wc_clean( wp_unslash( $_POST['sale_from'] ) );
						$sale_to        = empty( $_POST['sale_to'] ) ? '' : wc_clean( wp_unslash( $_POST['sale_to'] ) );
						$virtual        = empty( $_POST['_virtual'] ) ? 'no' : wc_clean( wp_unslash( $_POST['_virtual'] ) );
						$manage_stock   = empty( $_POST['wk_stock_management'] ) ? 'no' : wc_clean( wp_unslash( $_POST['wk_stock_management'] ) );
						$product_type   = empty( $_POST['product_type'] ) ? 'simple' : wc_clean( wp_unslash( $_POST['product_type'] ) );
						$simple         = ( 'simple' === $product_type ) ? 'yes' : 'no';

						add_post_meta( $sell_pr_id, '_manage_stock', $manage_stock );
						add_post_meta( $sell_pr_id, '_sale_price_dates_from', $sale_from );
						add_post_meta( $sell_pr_id, '_sale_price_dates_to', $sale_to );
						add_post_meta( $sell_pr_id, '_downloadable', $downloadable );
						add_post_meta( $sell_pr_id, '_virtual', $virtual );
						add_post_meta( $sell_pr_id, '_simple', $simple );

						if ( 'variable' === $product_type ) {
							update_post_meta( $sell_pr_id, '_min_variation_price', '' );
							update_post_meta( $sell_pr_id, '_max_variation_price', '' );
							update_post_meta( $sell_pr_id, '_min_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_max_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_min_variation_regular_price', '' );
							update_post_meta( $sell_pr_id, '_max_variation_regular_price', '' );
							update_post_meta( $sell_pr_id, '_min_regular_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_max_regular_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_min_variation_sale_price', null );
							update_post_meta( $sell_pr_id, '_max_variation_sale_price', null );
							update_post_meta( $sell_pr_id, '_min_sale_price_variation_id', null );
							update_post_meta( $sell_pr_id, '_max_sale_price_variation_id', null );
						}

						wp_set_object_terms( $sell_pr_id, $product_type, 'product_type', false );
					}

					$product_cats = empty( $_POST['product_cate'] ) ? '' : wc_clean( wp_unslash( $_POST['product_cate'] ) );

					$this->wkmp_add_pro_category( $product_cats, $sell_pr_id );
					wp_set_object_terms( $sell_pr_id, $product_type, 'product_type', false );

					do_action( 'marketplace_process_product_meta', $sell_pr_id );

					$edit_url = esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' ) . '/' . $sell_pr_id );

					wc_print_notice( wp_sprintf( /* translators: %s: Edit link. */ __( 'Product Created Successfully. To edit it %s', 'wk-marketplace' ), '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'click here', 'wk-marketplace' ) . '</a>' ) );
				}
			}
		}

		/**
		 * Add product category.
		 *
		 * @param int $cat_ids Category ids.
		 * @param int $post_id Post id.
		 */
		public function wkmp_add_pro_category( $cat_ids, $post_id ) {
			if ( ! empty( $cat_ids ) && ! empty( $post_id ) ) {
				$cat_ids = is_array( $cat_ids ) ? $cat_ids : ( strpos( $cat_ids, ',' ) ? explode( ',', $cat_ids ) : array( $cat_ids ) );

				$term_ids = array();

				foreach ( $cat_ids as $cat_id ) {
					$term = get_term_by( 'slug', $cat_id, 'product_cat' );
					if ( is_a( $term, 'WP_Term' ) ) {
						$term_ids[] = $term->term_id;
					} else {
						break;
					}
				}

				$cat_ids = empty( $term_ids ) ? $cat_ids : $term_ids;

				wp_set_object_terms( $post_id, $cat_ids, 'product_cat' );
			}
		}

		/**
		 * Updating per product settings.
		 */
		public function update_per_product_settings() {
			$nonce_per_page_update = \WK_Caching::wk_get_request_data( 'wkmp-product-per-page-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_per_page_update ) && wp_verify_nonce( $nonce_per_page_update, 'wkmp-per_page_product-nonce-action' ) ) {
				$per_page  = empty( $_POST['_wkmp_products_per_page'] ) ? 0 : intval( wp_unslash( $_POST['_wkmp_products_per_page'] ) );
				$seller_id = $this->seller_id > 0 ? $this->seller_id : get_current_user_id();

				if ( ! empty( $per_page ) && ! empty( $seller_id ) ) {
					update_user_meta( $seller_id, '_wkmp_products_per_page', $per_page );
				}
			}
		}
	}
}
