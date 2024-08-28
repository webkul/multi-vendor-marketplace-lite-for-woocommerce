<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Product;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Product_Form' ) ) {
	/**
	 * Seller Add / Edit Product class.
	 *
	 * Class WKMP_Product_Form
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Product
	 */
	class WKMP_Product_Form {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Product id.
		 *
		 * @var int $product_id Product id.
		 */
		protected $product_id;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		protected $seller_id;

		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		protected $wpdb;

		/**
		 * Marketplace class object.
		 *
		 * @var $wkmarketplace WKMarketplace.
		 */
		protected $wkmarketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Product_Form constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			global $wkmarketplace, $wpdb;

			$this->wkmarketplace = $wkmarketplace;
			$this->wpdb          = $wpdb;

			$this->seller_id = intval( $seller_id );
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
		 * Show add product form.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_add_product_form( $seller_id ) {
			$this->seller_id     = empty( $this->seller_id ) ? $seller_id : $this->seller_id;
			$categories          = array();
			$allowed_cat         = get_user_meta( $seller_id, 'wkmp_seller_allowed_categories', true );
			$dynamic_sku_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_dynamic_sku', true );
			$dynamic_sku_prefix  = get_user_meta( $seller_id, '_wkmp_dynamic_sku_prefix', true );

			$wc_product_types = wc_get_product_types();
			$allowed_types    = apply_filters( 'wkmp_allowed_product_types', array( 'simple', 'variable', 'grouped', 'external' ) );
			$final_types      = array_intersect_key( $wc_product_types, array_flip( $allowed_types ) );
			$seller_types     = get_option( '_wkmp_seller_allowed_product_types', array() );
			$mp_product_types = empty( $seller_types ) ? $final_types : array_intersect_key( $final_types, array_flip( $seller_types ) );

			if ( ! $allowed_cat ) {
				$allowed_cat = get_option( '_wkmp_seller_allowed_categories', array() );
			}

			$product_categories = wp_dropdown_categories(
				array(
					'show_option_none' => '',
					'hierarchical'     => 1,
					'hide_empty'       => 0,
					'name'             => 'product_cate[]',
					'id'               => 'mp_seller_product_categories',
					'taxonomy'         => 'product_cat',
					'title_li'         => '',
					'orderby'          => 'name',
					'order'            => 'ASC',
					'class'            => '',
					'exclude'          => '',
					'selected'         => $categories,
					'echo'             => 0,
					'value_field'      => 'slug',
					'walker'           => new WKMP_Category_Filter( $allowed_cat ),
				)
			);

			?>
			<div class="form wkmp_container wkmp-add-product-form">
				<?php
				$nonce_first = \WK_Caching::wk_get_request_data( 'wkmp_select_type_cat_nonce_name', array( 'method' => 'post' ) );

				if ( ! empty( $nonce_first ) && wp_verify_nonce( $nonce_first, 'wkmp_select_type_cat_nonce_action' ) ) {
					$product_cats = empty( $_POST['product_cate'] ) ? '' : wc_clean( wp_unslash( $_POST['product_cate'] ) );
					$product_type = empty( $_POST['product_type'] ) ? '' : wc_clean( wp_unslash( $_POST['product_type'] ) );
					$next_clicked = empty( $_POST['wkmp_add_product_next_step'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_add_product_next_step'] ) );

					if ( ! empty( $product_type ) && $next_clicked && ! empty( $product_cats ) ) {
						require_once __DIR__ . '/wkmp-add-product.php';
					} else {
						wc_print_notice( esc_html__( 'Sorry, Firstly select product category(s) and type.', 'wk-marketplace' ), 'error' );
						require_once __DIR__ . '/wkmp-add-product-first-step.php';
					}
				} else {
					$nonce_submit = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );
					$next_clicked = empty( $_POST['wkmp_add_product_next_step'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_add_product_next_step'] ) );

					if ( ! empty( $nonce_first ) && ! wp_verify_nonce( $nonce_first, 'wkmp_select_type_cat_nonce_action' ) || ( ! empty( $nonce_submit ) && ! wp_verify_nonce( $nonce_submit, 'wkmp_add_product_submit_nonce_action' ) ) ) {
						wc_print_notice( esc_html__( 'Security nonce not validated!!', 'wk-marketplace' ), 'error' );
					}

					require_once __DIR__ . '/wkmp-add-product-first-step.php';
				}
				?>
			</div>
			<?php
		}

		/**
		 * Edit product form.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_edit_product_form( $seller_id = 0 ) {
			global $wp;

			$nonce_add    = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );
			$nonce_update = \WK_Caching::wk_get_request_data( 'wkmp_edit_product_nonce_field', array( 'method' => 'post' ) );

			$nonce_failed   = false;
			$form_submitted = ! ( empty( $nonce_add ) && empty( $nonce_update ) );

			if ( ! empty( $nonce_add ) && ! wp_verify_nonce( $nonce_add, 'wkmp_add_product_submit_nonce_action' ) ) {
				$nonce_failed = true;
			}

			if ( ! empty( $nonce_update ) && ! wp_verify_nonce( $nonce_update, 'wkmp_edit_product_nonce_action' ) ) {
				$nonce_failed = true;
			}

			$this->seller_id = empty( $this->seller_id ) ? $seller_id : $this->seller_id;

			$posted_data = array(
				'dynamic_sku_enabled' => get_user_meta( $this->seller_id, '_wkmp_enable_seller_dynamic_sku', true ),
				'dynamic_sku_prefix'  => get_user_meta( $this->seller_id, '_wkmp_dynamic_sku_prefix', true ),
			);

			if ( $form_submitted && ! $nonce_failed ) {
				$posted_data['product_sku']     = empty( $_POST['product_sku'] ) ? '' : wc_clean( wp_unslash( $_POST['product_sku'] ) );
				$posted_data['seller_id']       = empty( $_POST['seller_id'] ) ? '' : intval( wp_unslash( $_POST['seller_id'] ) );
				$posted_data['sell_pr_id']      = empty( $_POST['sell_pr_id'] ) ? '' : intval( wp_unslash( $_POST['sell_pr_id'] ) );
				$posted_data['wk-mp-stock-qty'] = empty( $_POST['wk-mp-stock-qty'] ) ? '' : wc_clean( wp_unslash( $_POST['wk-mp-stock-qty'] ) );
				$posted_data['regu_price']      = empty( $_POST['regu_price'] ) ? '' : wc_clean( $_POST['regu_price'] );
				$posted_data['sale_price']      = empty( $_POST['sale_price'] ) ? '' : wc_clean( $_POST['sale_price'] );
				$posted_data['product_desc']    = empty( $_POST['product_desc'] ) ? '' : wp_kses_post( $_POST['product_desc'] );
				$posted_data['short_desc']      = empty( $_POST['short_desc'] ) ? '' : wp_kses_post( $_POST['short_desc'] );

				$this->wkmp_product_add_update( $posted_data );
			} elseif ( $nonce_failed ) {
				wc_print_notice( esc_html__( 'Sorry!! security check failed. Please try again!!', 'wk-marketplace' ), 'error' );
			}

			$wpdb_obj = $this->wpdb;

			$query_vars       = $wp->query_vars;
			$edit_product     = get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' );
			$this->product_id = empty( $query_vars[ $edit_product ] ) ? 0 : intval( $query_vars[ $edit_product ] );

			if ( $this->product_id > 0 ) {
				$wk_pro_id = $this->product_id;

				$allowed_cat = get_user_meta( $this->seller_id, 'wkmp_seller_allowed_categories', true );

				if ( ! $allowed_cat ) {
					$allowed_cat = get_option( '_wkmp_seller_allowed_categories', array() );
				}

				$categories         = wp_get_post_terms( $wk_pro_id, 'product_cat', array( 'fields' => 'slugs' ) );
				$product_categories = wp_dropdown_categories(
					array(
						'show_option_none' => '',
						'hierarchical'     => 1,
						'hide_empty'       => 0,
						'name'             => 'product_cate[]',
						'id'               => 'mp_seller_product_categories',
						'taxonomy'         => 'product_cat',
						'title_li'         => '',
						'orderby'          => 'name',
						'order'            => 'ASC',
						'class'            => '',
						'exclude'          => '',
						'selected'         => $categories,
						'echo'             => 0,
						'value_field'      => 'slug',
						'walker'           => new WKMP_Category_Filter( $allowed_cat ),
					)
				);

				$product_auth  = get_post_field( 'post_author', $this->product_id );
				$post_row_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}posts WHERE ID = %s", $this->product_id ) );
				$product_array = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}posts WHERE post_type = 'product' AND post_status = 'publish' AND post_author = %d ORDER BY ID DESC", $this->seller_id ) );

				require_once __DIR__ . '/wkmp-edit-product.php';
			}
		}

		/**
		 * Add/Update product into database.
		 *
		 * @param array $posted_data Posted data.
		 *
		 * @return void
		 */
		public function wkmp_product_add_update( $posted_data ) {
			global $current_user;

			$nonce_add    = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );
			$nonce_update = \WK_Caching::wk_get_request_data( 'wkmp_edit_product_nonce_field', array( 'method' => 'post' ) );
			$nonce_failed = false;
			$errors       = array();

			if ( ! empty( $nonce_add ) && ! wp_verify_nonce( $nonce_add, 'wkmp_add_product_submit_nonce_action' ) ) {
				$nonce_failed = true;
			}

			if ( ! empty( $nonce_update ) && ! wp_verify_nonce( $nonce_update, 'wkmp_edit_product_nonce_action' ) ) {
				$nonce_failed = true;
			}

			if ( $nonce_failed ) {
				$errors['nonce_failed'] = esc_html__( 'Sorry!! security check failed. Please try again!!', 'wk-marketplace' );
			}

			if ( empty( $errors ) ) {
				$errors = $this->wkmp_product_validation( $posted_data );
			}

			if ( ! empty( $errors ) ) {
				foreach ( $errors as $value ) {
					wc_print_notice( $value, 'error' );
				}
			} else {
				$posted_data['seller_id'] = empty( $posted_data['seller_id'] ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $posted_data['seller_id'];

				$sell_pr_id = isset( $posted_data['sell_pr_id'] ) ? intval( $posted_data['sell_pr_id'] ) : 0;
				$sell_pr_id = empty( $sell_pr_id ) ? $this->product_id : $sell_pr_id;

				$variation_att_ids = empty( $_POST['mp_attribute_variation_name'] ) ? '' : wc_clean( wp_unslash( $_POST['mp_attribute_variation_name'] ) );
				$att_val           = empty( $_POST['pro_att'] ) ? '' : wc_clean( wp_unslash( $_POST['pro_att'] ) );

				if ( isset( $posted_data['sale_price'] ) && '' === $posted_data['sale_price'] ) {
					unset( $posted_data['sale_price'] );
				}

				$min_max_prices = array();

				if ( ! empty( $variation_att_ids ) && ! empty( $att_val ) ) {
					$min_max_prices = $this->wkmp_update_product_variation_data( $posted_data, $variation_att_ids );
				}

				$att = array();

				if ( ! empty( $att_val ) ) {
					foreach ( $att_val as $attribute ) {
						if ( empty( $attribute['name'] ) || empty( $attribute['value'] ) ) {
							continue;
						}

						$rep_str            = $attribute['value'];
						$rep_str            = preg_replace( '/\s+/', ' ', $rep_str );
						$attribute['name']  = str_replace( ' ', '-', $attribute['name'] );
						$attribute['value'] = str_replace( '|', '|', $rep_str );

						if ( isset( $attribute['is_visible'] ) ) {
							$attribute['is_visible'] = (int) $attribute['is_visible'];
						} else {
							$attribute['is_visible'] = 0;
						}

						if ( isset( $attribute['is_variation'] ) ) {
							$attribute['is_variation'] = (int) $attribute['is_variation'];
						} else {
							$attribute['is_variation'] = 0;
						}

						$attribute['is_taxonomy']                           = 0;
						$att[ str_replace( ' ', '-', $attribute['name'] ) ] = $attribute;
					}
				}

				$product_auth = ( $sell_pr_id > 0 ) ? get_post_field( 'post_author', $sell_pr_id ) : 0;
				$product_name = empty( $_POST['product_name'] ) ? '' : wc_clean( $_POST['product_name'] );

				if ( ! empty( $product_name ) ) {
					$product_name = wp_strip_all_tags( $product_name );

					$product_dsc = empty( $posted_data['product_desc'] ) ? '' : $posted_data['product_desc'];

					$max_qty_limit = empty( $_POST['_wkmp_max_product_qty_limit'] ) ? '' : intval( wp_unslash( $_POST['_wkmp_max_product_qty_limit'] ) );
					$threshold     = empty( $_POST['wk-mp-stock-threshold'] ) ? 0 : intval( wp_unslash( $_POST['wk-mp-stock-threshold'] ) );

					$downloadable           = empty( $_POST['_downloadable'] ) ? '' : wc_clean( wp_unslash( $_POST['_downloadable'] ) );
					$posted_sku             = empty( $_POST['product_sku'] ) ? '' : wc_clean( wp_unslash( $_POST['product_sku'] ) );
					$product_status         = empty( $_POST['mp_product_status'] ) ? '' : wc_clean( wp_unslash( $_POST['mp_product_status'] ) );
					$product_gallery_images = empty( $_POST['product_image_Galary_ids'] ) ? '' : wc_clean( wp_unslash( $_POST['product_image_Galary_ids'] ) );

					$product_type    = empty( $_POST['product_type'] ) ? 'simple' : wc_clean( wp_unslash( $_POST['product_type'] ) );
					$virtual         = empty( $_POST['_virtual'] ) ? 'no' : wc_clean( wp_unslash( $_POST['_virtual'] ) );
					$back_order      = empty( $_POST['_backorders'] ) ? 'no' : wc_clean( wp_unslash( $_POST['_backorders'] ) );
					$sold_individual = empty( $_POST['wk_sold_individual'] ) ? 'no' : wc_clean( wp_unslash( $_POST['wk_sold_individual'] ) );
					$manage_stock    = empty( $_POST['wk_stock_management'] ) ? 'no' : wc_clean( wp_unslash( $_POST['wk_stock_management'] ) );
					$limit           = empty( $_POST['_download_limit'] ) ? '-1' : wc_clean( wp_unslash( $_POST['_download_limit'] ) );
					$expiry          = empty( $_POST['_download_expiry'] ) ? '-1' : wc_clean( wp_unslash( $_POST['_download_expiry'] ) );

					$simple    = ( 'simple' === $product_type ) ? 'yes' : 'no';
					$stock_qty = ( 'yes' === $manage_stock ) ? $posted_data['wk-mp-stock-qty'] : '';

					if ( empty( $posted_sku ) && $sell_pr_id > 0 ) {
						$posted_sku = get_post_meta( $sell_pr_id, '_sku', true );
					}

					$price       = empty( $posted_data['regu_price'] ) ? '' : $posted_data['regu_price'];
					$sales_price = empty( $posted_data['sale_price'] ) ? '' : $posted_data['sale_price'];

					$price       = is_null( $price ) ? '' : wc_format_decimal( trim( stripslashes( $price ) ) );
					$sales_price = is_null( $sales_price ) ? '' : wc_format_decimal( trim( stripslashes( $sales_price ) ) );

					$product_short_desc = empty( $posted_data['short_desc'] ) ? '' : $posted_data['short_desc'];

					$product_data = array(
						'post_author'           => $this->seller_id,
						'post_content'          => $product_dsc,
						'post_content_filtered' => $product_short_desc,
						'post_title'            => htmlspecialchars( $product_name ),
						'post_excerpt'          => $product_short_desc,
						'post_status'           => $product_status,
						'post_type'             => 'product',
						'comment_status'        => 'open',
						'ping_status'           => 'open',
						'post_password'         => '',
						'post_name'             => wp_strip_all_tags( $product_name ),
						'to_ping'               => '',
						'pinged'                => '',
						'post_parent'           => '',
						'menu_order'            => '',
						'guid'                  => '',
					);

					$nonce_update = \WK_Caching::wk_get_request_data( 'wkmp_edit_product_nonce_field', array( 'method' => 'post' ) );

					if ( $sell_pr_id > 0 && intval( $product_auth ) === $this->seller_id && ! empty( $nonce_update ) ) {
						// Add mp shipping per product addon data.
						$p_shipping_class_slug = '';

						if ( 'external' !== $product_type ) {
							$p_shipping_class_id = empty( $_POST['product_shipping_class'] ) ? 0 : intval( wp_unslash( $_POST['product_shipping_class'] ) );

							if ( $p_shipping_class_id > 0 ) {
								$ship_class_term       = get_term_by( 'ID', $p_shipping_class_id, 'product_shipping_class' );
								$p_shipping_class_slug = ( $ship_class_term instanceof \WP_Term ) ? $ship_class_term->slug : $p_shipping_class_slug;
							}
						}

						if ( ! empty( $p_shipping_class_slug ) ) {
							wp_set_object_terms( $sell_pr_id, $p_shipping_class_slug, 'product_shipping_class' );
						}

						$product_data['ID'] = $sell_pr_id;

						if ( wp_update_post( $product_data ) ) {
							wc_print_notice( __( 'Product Updated Successfully.', 'wk-marketplace' ) );

							if ( ! empty( $posted_sku ) ) {
								update_post_meta( $sell_pr_id, '_sku', wp_strip_all_tags( $posted_sku ) );
							}

							$visibility = ( 'publish' === $product_status && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) ? 'visible' : '';

							update_post_meta( $sell_pr_id, '_visibility', $visibility );

							if ( is_numeric( $price ) || empty( $price ) ) {
								update_post_meta( $sell_pr_id, '_regular_price', $price );
								update_post_meta( $sell_pr_id, '_price', $price );
							}

							if ( 'variable' !== $product_type ) {
								if ( ! empty( $sales_price ) && is_numeric( $sales_price ) && $sales_price < $price ) {
									update_post_meta( $sell_pr_id, '_sale_price', $sales_price );
									update_post_meta( $sell_pr_id, '_price', $sales_price );
								} else {
									update_post_meta( $sell_pr_id, '_sale_price', '' );
								}
							} else {
								foreach ( $min_max_prices as $price ) {
									update_post_meta( $sell_pr_id, '_price', $price );
								}
							}

							$stock_status = empty( $_POST['_stock_status'] ) ? 'instock' : wc_clean( wp_unslash( $_POST['_stock_status'] ) );

							if ( ! empty( $variation_att_ids ) ) {
								$stock_status = ( $manage_stock ) ? 'instock' : 'outofstock';
							} elseif ( 'yes' === $manage_stock ) {
								$stock_status = ( $stock_qty ) ? 'instock' : 'outofstock';
							}

							update_post_meta( $sell_pr_id, '_sold_individually', $sold_individual );
							update_post_meta( $sell_pr_id, '_low_stock_amount', $threshold );
							update_post_meta( $sell_pr_id, '_backorders', $back_order );
							update_post_meta( $sell_pr_id, '_stock_status', $stock_status );
							update_post_meta( $sell_pr_id, '_manage_stock', $manage_stock );
							update_post_meta( $sell_pr_id, '_virtual', $virtual );
							update_post_meta( $sell_pr_id, '_simple', $simple );
							update_post_meta( $sell_pr_id, '_wkmp_max_product_qty_limit', $max_qty_limit );

							if ( 'yes' === $virtual ) {
								update_post_meta( $sell_pr_id, '_weight', '' );
								update_post_meta( $sell_pr_id, '_length', '' );
								update_post_meta( $sell_pr_id, '_width', '' );
								update_post_meta( $sell_pr_id, '_height', '' );
							} else {
								$weight = empty( $_POST['_weight'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_weight'] ) ) );
								$length = empty( $_POST['_length'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_length'] ) ) );
								$width  = empty( $_POST['_width'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_width'] ) ) );
								$height = empty( $_POST['_height'] ) ? '' : wc_format_decimal( wc_clean( wp_unslash( $_POST['_height'] ) ) );

								update_post_meta( $sell_pr_id, '_weight', $weight );
								update_post_meta( $sell_pr_id, '_length', $length );
								update_post_meta( $sell_pr_id, '_width', $width );
								update_post_meta( $sell_pr_id, '_height', $height );
							}

							if ( 'external' === $product_type ) {
								$pro_url = empty( $_POST['product_url'] ) ? '' : wc_clean( wp_unslash( $_POST['product_url'] ) );
								$btn_txt = empty( $_POST['button_txt'] ) ? '' : wc_clean( wp_unslash( $_POST['button_txt'] ) );

								if ( ! empty( $pro_url ) && ! empty( $btn_txt ) ) {
									update_post_meta( $sell_pr_id, '_product_url', esc_url_raw( $pro_url ) );
									update_post_meta( $sell_pr_id, '_button_text', $btn_txt );
								}
							}

							// Save upsells && Cross sells data.
							$upsell_ids    = empty( $_POST['upsell_ids'] ) ? '' : wc_clean( wp_unslash( $_POST['upsell_ids'] ) );
							$crosssell_ids = empty( $_POST['crosssell_ids'] ) ? '' : wc_clean( wp_unslash( $_POST['crosssell_ids'] ) );

							update_post_meta( $sell_pr_id, '_upsell_ids', $upsell_ids );
							update_post_meta( $sell_pr_id, '_crosssell_ids', $crosssell_ids );

							if ( 'grouped' === $product_type ) {
								$group_product_ids = empty( $_POST['mp_grouped_products'] ) ? '' : wc_clean( wp_unslash( $_POST['mp_grouped_products'] ) );
								update_post_meta( $sell_pr_id, '_children', $group_product_ids );
							}

							if ( 'yes' === $downloadable ) {
								$upload_file_url = array();
								$download_urls   = empty( $_POST['_mp_dwnld_file_urls'] ) ? '' : wc_clean( wp_unslash( $_POST['_mp_dwnld_file_urls'] ) );
								$download_names  = empty( $_POST['_mp_dwnld_file_names'] ) ? '' : wc_clean( wp_unslash( $_POST['_mp_dwnld_file_names'] ) );
								$file_hashes     = empty( $_POST['_mp_dwnld_file_hashes'] ) ? '' : wc_clean( wp_unslash( $_POST['_mp_dwnld_file_hashes'] ) );

								update_post_meta( $sell_pr_id, '_downloadable', $downloadable );
								update_post_meta( $sell_pr_id, '_virtual', 'yes' );

								foreach ( $download_urls as $key => $value ) {
									$dw_file_name = ( ! empty( $download_names[ $key ] ) ) ? $download_names[ $key ] : '';

									$upload_file_url[ md5( $value ) ] = array(
										'id'            => md5( $value ),
										'name'          => $dw_file_name,
										'file'          => $value,
										'previous_hash' => $file_hashes[ $key ],
									);
								}

								$data_store = \WC_Data_Store::load( 'customer-download' );

								if ( $upload_file_url ) {
									foreach ( $upload_file_url as $download ) {
										$new_hash = md5( $download['file'] );

										if ( $download['previous_hash'] && $download['previous_hash'] !== $new_hash ) {
											// Update permissions.
											$data_store->update_download_id( $sell_pr_id, $download['previous_hash'], $new_hash );
										}
									}
								}
								update_post_meta( $sell_pr_id, '_downloadable_files', $upload_file_url );
							} else {
								update_post_meta( $sell_pr_id, '_downloadable', 'no' );
							}

							$att = empty( $att ) ? array() : $att;

							foreach ( $att as $key => $attr_data ) {
								if ( ! empty( $attr_data['value'] ) && is_array( $attr_data['value'] ) ) {
									$att[ $key ]['value'] = implode( '|', $attr_data['value'] );
								}
							}

							update_post_meta( $sell_pr_id, '_product_attributes', $att );

							if ( '' !== $stock_qty ) {
								update_post_meta( $sell_pr_id, '_stock', $stock_qty );
							} else {
								delete_post_meta( $sell_pr_id, '_stock' );
							}

							update_post_meta( $sell_pr_id, '_download_limit', $limit );
							update_post_meta( $sell_pr_id, '_download_expiry', $expiry );
							update_post_meta( $sell_pr_id, '_product_image_gallery', $product_gallery_images );

							$thumbnail_id = empty( $_POST['product_thumb_image_mp'] ) ? '' : intval( wp_unslash( $_POST['product_thumb_image_mp'] ) );

							if ( ! empty( $thumbnail_id ) ) {
								update_post_meta( $sell_pr_id, '_thumbnail_id', $thumbnail_id );
							} else {
								delete_post_meta( $sell_pr_id, '_thumbnail_id' );
							}
						}

						$product_cats = empty( $_POST['product_cate'] ) ? '' : wc_clean( wp_unslash( $_POST['product_cate'] ) );

						$this->wkmp_update_pro_category( $product_cats, $sell_pr_id );
						wp_set_object_terms( $sell_pr_id, $product_type, 'product_type', false );

						do_action( 'wkmp_after_seller_product_update', $sell_pr_id, $att_val );
					}

					do_action( 'marketplace_process_product_meta', $sell_pr_id );

					if ( ! get_option( '_wkmp_allow_seller_to_publish', true ) ) {
						if ( ! get_post_meta( $sell_pr_id, 'mp_added_noti' ) ) {
							delete_post_meta( $sell_pr_id, 'mp_admin_view' );
							update_option( 'wkmp_approved_product_count', (int) ( get_option( 'wkmp_approved_product_count', 0 ) + 1 ) );
							update_post_meta( $sell_pr_id, 'mp_added_noti', true );
						}

						do_action( 'wkmp_seller_published_product', $this->seller_id, $sell_pr_id );
					}

					$nonce_add = \WK_Caching::wk_get_request_data( 'wkmp_add_product_submit_nonce_name', array( 'method' => 'post' ) );

					if ( $sell_pr_id > 0 && intval( $product_auth ) === intval( $this->seller_id ) && ! empty( $nonce_add ) ) {
						if ( 'simple' === $product_type ) {
							$obj_product = new \WC_Product_Simple( $sell_pr_id );
							$obj_product->save();
						} elseif ( 'variable' === $product_type ) {
							$obj_product = new \WC_Product_Variable( $sell_pr_id );
							$obj_product->save();

							foreach ( $variation_att_ids as $variation_id ) {
								$variation = new \WC_Product_Variation( $variation_id );
								$variation->save();
							}
						}
					}
					do_action( 'wkmp_after_seller_created_product', $this->seller_id, $sell_pr_id );
				}
			}
		}

		/**
		 * Adding variation attribute of product.
		 *
		 * @param array $posted_data Posted data.
		 * @param array $var_attr_ids Variation attr ids.
		 *
		 * @return array
		 */
		public function wkmp_update_product_variation_data( $posted_data, $var_attr_ids ) {
			$nonce_update = \WK_Caching::wk_get_request_data( 'wkmp_edit_product_nonce_field', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_update ) && ! wp_verify_nonce( $nonce_update, 'wkmp_edit_product_nonce_action' ) ) {
				return array();
			}

			$wpdb_obj               = $this->wpdb;
			$variation_data         = array();
			$variation_data['_sku'] = array();
			$temp_var_sku           = array();
			$var_regu_price         = array();
			$var_sale_price         = array();

			$mp_attr_names       = empty( $_POST['mp_attribute_name'] ) ? array() : wc_clean( wp_unslash( $_POST['mp_attribute_name'] ) );
			$is_downloadables    = empty( $_POST['wkmp_variable_is_downloadable'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_is_downloadable'] ) );
			$vars_is_virtual     = empty( $_POST['wkmp_variable_is_virtual'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_is_virtual'] ) );
			$sales_from          = empty( $_POST['wkmp_variable_sale_price_dates_from'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_sale_price_dates_from'] ) );
			$sales_to            = empty( $_POST['wkmp_variable_sale_price_dates_to'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_sale_price_dates_to'] ) );
			$backorders          = empty( $_POST['wkmp_variable_backorders'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_backorders'] ) );
			$manage_stocks       = empty( $_POST['wkmp_variable_manage_stock'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_manage_stock'] ) );
			$stocks_status       = empty( $_POST['wkmp_variable_stock_status'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_stock_status'] ) );
			$variable_skus       = empty( $_POST['wkmp_variable_sku'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_variable_sku'] ) );
			$download_file_urls  = empty( $_POST['_mp_variation_downloads_files_url'] ) ? array() : wc_clean( wp_unslash( $_POST['_mp_variation_downloads_files_url'] ) );
			$download_file_names = empty( $_POST['_mp_variation_downloads_files_name'] ) ? array() : wc_clean( wp_unslash( $_POST['_mp_variation_downloads_files_name'] ) );

			$downloads_expiry = empty( $_POST['wkmp_variable_download_expiry'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_download_expiry'] ) );
			$downloads_limit  = empty( $_POST['wkmp_variable_download_limit'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_download_limit'] ) );
			$variable_stocks  = empty( $_POST['wkmp_variable_stock'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variable_stock'] ) );
			$variable_img_ids = empty( $_POST['upload_var_img'] ) ? array() : wc_clean( wp_unslash( $_POST['upload_var_img'] ) );
			$var_menu_orders  = empty( $_POST['wkmp_variation_menu_order'] ) ? array() : wc_clean( wp_unslash( $_POST['wkmp_variation_menu_order'] ) );

			$regular_prices   = empty( $_POST['wkmp_variable_regular_price'] ) ? array() : wc_clean( $_POST['wkmp_variable_regular_price'] );
			$sale_prices      = empty( $_POST['wkmp_variable_sale_price'] ) ? array() : wc_clean( $_POST['wkmp_variable_sale_price'] );
			$variable_widths  = empty( $_POST['wkmp_variable_width'] ) ? array() : wc_clean( $_POST['wkmp_variable_width'] );
			$variable_heights = empty( $_POST['wkmp_variable_height'] ) ? array() : wc_clean( $_POST['wkmp_variable_height'] );
			$variable_lengths = empty( $_POST['wkmp_variable_length'] ) ? array() : wc_clean( $_POST['wkmp_variable_length'] );
			$variable_weights = empty( $_POST['wkmp_variable_weight'] ) ? array() : wc_clean( $_POST['wkmp_variable_weight'] );

			foreach ( $var_attr_ids as $var_id ) {
				$var_regu_price[ $var_id ] = is_numeric( wc_format_decimal( trim( stripslashes( $regular_prices[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $regular_prices[ $var_id ] ) ) ) : '';

				$sale_var_price = ( isset( $sale_prices[ $var_id ] ) && is_numeric( wc_format_decimal( trim( stripslashes( $sale_prices[ $var_id ] ) ) ) ) ) ? wc_format_decimal( trim( stripslashes( $sale_prices[ $var_id ] ) ) ) : '';

				if ( '' !== $sale_var_price && $sale_var_price < $var_regu_price[ $var_id ] ) {
					$var_sale_price[ $var_id ] = $sale_var_price;
				} else {
					$var_sale_price[ $var_id ] = '';
				}

				foreach ( $mp_attr_names[ $var_id ] as $variation_type ) {
					$attr_names = empty( $_POST[ 'attribute_' . $variation_type ] ) ? array() : wc_clean( $_POST[ 'attribute_' . $variation_type ] );
					$variation_data[ 'attribute_' . sanitize_title( $variation_type ) ][] = trim( $attr_names[ $var_id ] );
				}
				$downloadable_variable = 'no';
				if ( isset( $is_downloadables[ $var_id ] ) ) {
					$downloadable_variable = ( 'yes' === $is_downloadables[ $var_id ] ) ? 'yes' : $downloadable_variable;
				}

				$virtual_variable = 'no';
				if ( isset( $vars_is_virtual[ $var_id ] ) ) {
					$virtual_variable = ( 'yes' === $vars_is_virtual[ $var_id ] ) ? 'yes' : $virtual_variable;
				}

				if ( 'yes' === $downloadable_variable ) {
					if ( isset( $downloads_expiry[ $var_id ] ) && is_numeric( $downloads_expiry[ $var_id ] ) ) {
						$downloadable_variable = $downloads_expiry[ $var_id ];
					}
					if ( isset( $downloads_limit[ $var_id ] ) && is_numeric( $downloads_limit[ $var_id ] ) ) {
						$downloadable_variable = $downloads_limit[ $var_id ];
					}
				}

				$_sale_var_price = ( isset( $sale_prices[ $var_id ] ) && is_numeric( wc_format_decimal( trim( stripslashes( $sale_prices[ $var_id ] ) ) ) ) ) ? wc_format_decimal( trim( stripslashes( $sale_prices[ $var_id ] ) ) ) : '';

				if ( '' !== $_sale_var_price && is_numeric( $_sale_var_price ) && $_sale_var_price < $regular_prices[ $var_id ] ) {
					$variation_data['_sale_price'][] = $_sale_var_price;
				} else {
					$variation_data['_sale_price'][] = '';
				}

				if ( empty( $sale_prices[ $var_id ] ) ) {
					$variation_data['_price'][] = is_numeric( wc_format_decimal( trim( stripslashes( $regular_prices[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $regular_prices[ $var_id ] ) ) ) : '';
				} else {
					$variation_data['_price'][] = is_numeric( wc_format_decimal( trim( stripslashes( $sale_prices[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $sale_prices[ $var_id ] ) ) ) : '';
				}

				$variation_data['_regular_price'][] = is_numeric( wc_format_decimal( trim( stripslashes( $regular_prices[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $regular_prices[ $var_id ] ) ) ) : '';

				if ( ! empty( $sales_to ) ) {
					$variation_data['_sale_price_dates_to'][] = wc_format_decimal( trim( stripslashes( $sales_to[ $var_id ] ) ) );
				}

				if ( isset( $sales_from ) ) {
					$variation_data['_sale_price_dates_from'][] = wc_format_decimal( trim( stripslashes( $sales_from[ $var_id ] ) ) );
				}

				$variation_data['_backorders'][] = $backorders[ $var_id ];

				$manage_stock = 'no';
				if ( isset( $manage_stocks ) && isset( $manage_stocks[ $var_id ] ) ) {
					$manage_stock = ( 'yes' === $manage_stocks[ $var_id ] ) ? 'yes' : $manage_stock;
				}

				$variation_data['_manage_stock'][] = $manage_stock;

				if ( 'yes' === $manage_stock ) {
					$stk_qty                           = empty( $variable_stocks[ $var_id ] ) ? 0 : intval( $variable_stocks[ $var_id ] );
					$variation_data['_stock'][]        = $stk_qty;
					$variation_data['_stock_status'][] = ( $stk_qty > 0 ) ? 'instock' : 'outofstock';
				} else {
					$variation_data['_stock_status'][] = isset( $stocks_status[ $var_id ] ) ? $stocks_status[ $var_id ] : '';
					$variation_data['_stock'][]        = '';
				}

				$var_sku_check = wp_strip_all_tags( $variable_skus[ $var_id ] );

				if ( isset( $variable_skus[ $var_id ] ) && ! empty( $variable_skus[ $var_id ] ) ) {
					$var_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_id FROM {$wpdb_obj->prefix}postmeta WHERE meta_key='_sku' AND meta_value=%s AND post_id != %d", $var_sku_check, $var_id ) );

					if ( empty( $var_data ) && ! in_array( $variable_skus[ $var_id ], $temp_var_sku, true ) ) {
						$variation_data['_sku'][] = $var_sku_check;
						$temp_var_sku[]           = $var_sku_check;
					} else {
						$variation_data['_sku'][] = '';
						wc_add_notice( esc_html__( 'Invalid or Duplicate SKU.', 'wk-marketplace' ), 'error' );
					}
				} else {
					$variation_data['_sku'][] = '';
				}

				$variation_data['_width'][]        = is_numeric( wc_format_decimal( trim( stripslashes( $variable_widths[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $variable_widths[ $var_id ] ) ) ) : '';
				$variation_data['_height'][]       = is_numeric( wc_format_decimal( trim( stripslashes( $variable_heights[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $variable_heights[ $var_id ] ) ) ) : '';
				$variation_data['_length'][]       = is_numeric( wc_format_decimal( trim( stripslashes( $variable_lengths[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $variable_lengths[ $var_id ] ) ) ) : '';
				$variation_data['_virtual'][]      = $virtual_variable;
				$variation_data['_downloadable'][] = $downloadable_variable;
				$thumbnail_id                      = $variable_img_ids[ $var_id ];

				if ( ! empty( $thumbnail_id ) ) {
					$variation_data['_thumbnail_id'][] = $thumbnail_id;
				} else {
					$variation_data['_thumbnail_id'][] = 0;
				}

				$variation_data['_weight'][]     = is_numeric( wc_format_decimal( trim( stripslashes( $variable_weights[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $variable_weights[ $var_id ] ) ) ) : '';
				$variation_data['_menu_order'][] = is_numeric( wc_format_decimal( trim( stripslashes( $var_menu_orders[ $var_id ] ) ) ) ) ? wc_format_decimal( trim( stripslashes( $var_menu_orders[ $var_id ] ) ) ) : '';

				/* Variation for downloadable product */
				if ( 'yes' === $downloadable_variable ) {
					$variation_files = $download_file_urls[ $var_id ];
					$variation_names = $download_file_names[ $var_id ];

					if ( isset( $download_file_urls[ $var_id ] ) && count( $download_file_urls[ $var_id ] ) > 0 ) {
						$files = array();

						if ( ! empty( $variation_files ) ) {
							$variation_count = count( $variation_files );
							for ( $i = 0; $i < $variation_count; ++$i ) {
								$file_url = wp_unslash( trim( $variation_files[ $i ] ) );
								if ( '' !== $file_url ) {
									$files[ md5( $file_url ) ] = array(
										'name' => $variation_names[ $i ],
										'file' => $file_url,
									);
								}
							}
						}
						update_post_meta( $var_id, '_downloadable_files', $files );
					}
				}
			}

			$variation_data_key     = array_keys( $variation_data );
			$variations_values      = array_values( $variation_data );
			$variation_data_count   = count( $variation_data );
			$variation_att_id_count = count( $var_attr_ids );

			for ( $i = 0; $i < $variation_data_count; ++$i ) {
				for ( $x = 0; $x < $variation_att_id_count; ++$x ) {
					update_post_meta( $var_attr_ids[ $x ], $variation_data_key[ $i ], $variations_values[ $i ][ $x ] );
					if ( '_sale_price' === $variation_data_key[ $i ] && '' === $variations_values[ $i ][ $x ] ) {
						delete_post_meta( $var_attr_ids[ $x ], '_sale_price' );
					}
				}
			}

			$regular_prices = map_deep( $regular_prices, 'floatval' );
			$sale_prices    = map_deep( $sale_prices, 'floatval' );

			return array( min( array_merge( $regular_prices, $sale_prices ) ), max( array_merge( $regular_prices, $sale_prices ) ) );
		}

		/**
		 * Validate product fields before adding or updating the product.
		 *
		 * @param array $data Data.
		 *
		 * @return array
		 */
		public function wkmp_product_validation( $data ) {
			$errors   = array();
			$wpdb_obj = $this->wpdb;

			$regu_price = is_null( $data['regu_price'] ) ? '' : wc_format_decimal( trim( stripslashes( $data['regu_price'] ) ) );
			$sale_price = is_null( $data['sale_price'] ) ? '' : wc_format_decimal( trim( stripslashes( $data['sale_price'] ) ) );

			if ( ! is_numeric( $regu_price ) && ! empty( $regu_price ) ) {
				$errors[] = esc_html__( 'Regular Price is not valid.', 'wk-marketplace' );
			}

			if ( ! is_numeric( $sale_price ) && ! empty( $sale_price ) ) {
				$errors[] = esc_html__( 'Sale Price is not a number.', 'wk-marketplace' );
			}

			if ( ! is_numeric( $data['wk-mp-stock-qty'] ) && ! empty( $data['wk-mp-stock-qty'] ) ) {
				$errors[] = esc_html__( 'Stock Quantity is not a number.', 'wk-marketplace' );
			}

			$posted_sku = empty( $data['product_sku'] ) ? '' : $data['product_sku'];
			$sell_pr_id = empty( $data['sell_pr_id'] ) ? 0 : intval( $data['sell_pr_id'] );

			if ( ! empty( $posted_sku ) ) {
				$dynamic_sku_prefix = empty( $data['dynamic_sku_prefix'] ) ? '' : $data['dynamic_sku_prefix'];

				$sku_post_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT post_id FROM {$wpdb_obj->prefix}postmeta WHERE meta_key='_sku' AND meta_value=%s", $posted_sku ) );

				if ( intval( $sku_post_id ) > 0 && $data['dynamic_sku_enabled'] && ! empty( $dynamic_sku_prefix ) ) {
					$post_sku_prefix = get_post_meta( $sku_post_id, '_sku_prefix', true );
					$sku_post_id     = ( $post_sku_prefix === $dynamic_sku_prefix );
				}

				$prod_sku = ( $sell_pr_id > 0 ) ? get_post_meta( $sell_pr_id, '_sku', true ) : '';

				if ( $prod_sku !== $posted_sku && ! empty( $sku_post_id ) ) {
					$errors[] = esc_html__( 'Invalid or Duplicate SKUs.', 'wk-marketplace' );
				}
			}

			return apply_filters( 'wkmp_product_validation_errors', $errors, $data );
		}

		/**
		 * Update product category.
		 *
		 * @param int $cat_id Category id.
		 * @param int $post_id Post id.
		 *
		 * @return void
		 */
		public function wkmp_update_pro_category( $cat_id, $post_id ) {
			if ( is_array( $cat_id ) && array_key_exists( '1', $cat_id ) ) {
				wp_set_object_terms( $post_id, $cat_id, 'product_cat' );
			} elseif ( is_array( $cat_id ) ) {
				$term = get_term_by( 'slug', $cat_id[0], 'product_cat' );
				wp_set_object_terms( $post_id, $term->term_id, 'product_cat' );
			}
		}

		/**
		 * Get product image.
		 *
		 * @param int    $pro_id int prod id.
		 * @param string $meta_value meta value.
		 *
		 * @return string $product_image
		 */
		public function wkmp_get_product_image( $pro_id, $meta_value ) {
			$p = get_post_meta( $pro_id, $meta_value, true );
			if ( is_null( $p ) ) {
				return '';
			}

			return get_post_meta( $p, '_wp_attached_file', true );
		}

		/**
		 * Display attribute variations
		 *
		 * @param int $var_id variable id.
		 *
		 * @return void
		 */
		public function wkmp_attributes_variation( $var_id ) {
			$wk_pro_id = $var_id;
			$args      = array(
				'post_parent'    => $wk_pro_id,
				'post_type'      => 'product_variation',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			);

			$children_array = get_children( $args );

			$i = 0;

			foreach ( $children_array as $var_att ) {
				$this->wkmp_attribute_variation_data( $var_att->ID, $wk_pro_id );
				++$i;
			}
		}

		/**
		 * Include variations HTML
		 *
		 * @param int $variation_id Variable id.
		 * @param int $wk_pro_id variable id.
		 *
		 * @return void
		 */
		public function wkmp_attribute_variation_data( $variation_id, $wk_pro_id ) {
			$thumb_id            = get_post_meta( $variation_id, '_thumbnail_id', true );
			$product_ping_status = array(
				'ID'          => $wk_pro_id,
				'ping_status' => 'closed',
			);

			wp_update_post( $product_ping_status );
			require __DIR__ . '/wkmp-variations.php';
		}

		/**
		 * WordPress text input
		 *
		 * @param int $field Field.
		 * @param int $wk_pro_id Product id.
		 *
		 * @return void
		 */
		public function wkmp_wp_text_input( $field, $wk_pro_id ) {
			global $post;

			$the_post_id            = empty( $wk_pro_id ) ? $post->ID : $wk_pro_id;
			$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
			$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
			$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
			$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
			$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $the_post_id, $field['id'], true );
			$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
			$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
			$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

			switch ( $data_type ) {
				case 'price':
					$field['class'] .= ' wc_input_price';

					$field['value'] = wc_format_localized_price( $field['value'] );
					break;
				case 'decimal':
					$field['class'] .= ' wc_input_decimal';

					$field['value'] = wc_format_localized_decimal( $field['value'] );
					break;
				case 'stock':
					$field['class'] .= ' wc_input_stock';

					$field['value'] = wc_stock_amount( $field['value'] );
					break;
				case 'url':
					$field['class'] .= ' wc_input_url';

					$field['value'] = esc_url( $field['value'] );
					break;
				default:
					break;
			}

			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
				foreach ( $field['custom_attributes'] as $attribute => $value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
				}
			}

			$custom_attributes = implode( ' ', $custom_attributes );

			echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . esc_attr( $custom_attributes ) . ' /> ';

			if ( ! empty( $field['description'] ) ) {
				if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
					echo wp_kses(
						wc_help_tip( $field['description'] ),
						array(
							'span' => array(
								'tabindex'   => array(),
								'aria-label' => array(),
								'data-tip'   => array(),
								'class'      => array(),
							),
						)
					);
				} else {
					echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
				}
			}
			echo '</p>';
		}
	}
}
