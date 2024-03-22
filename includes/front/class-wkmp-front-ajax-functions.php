<?php
/**
 * Front ajax functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper;

if ( ! class_exists( 'WKMP_Front_Ajax_Functions' ) ) {
	/**
	 * Front ajax functions.
	 */
	class WKMP_Front_Ajax_Functions {
		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		private $wpdb;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Front_Ajax_Functions constructor.
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
		 * Check availability of shop url requested.
		 *
		 * @return void
		 */
		public function wkmp_check_for_shop_url() {
			global $wkmarketplace;
			$response = array();

			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$slug = empty( $_POST['shop_slug'] ) ? 0 : wc_clean( wp_unslash( $_POST['shop_slug'] ) );

				if ( ! empty( $slug ) ) {
					$user = $wkmarketplace->wkmp_get_seller_id_by_shop_address( $slug );

					if ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $slug ) ) {
						$response = array(
							'error'   => true,
							'message' => esc_html__( 'You can not use special characters in shop url except HYPHEN(-).', 'wk-marketplace' ),
						);
					} elseif ( ctype_space( $slug ) ) {
						$response = array(
							'error'   => true,
							'message' => esc_html__( 'White space(s) aren\'t allowed in shop url.', 'wk-marketplace' ),
						);
					} elseif ( $user ) {
						$response = array(
							'error'   => true,
							'message' => esc_html__( 'This shop URl already EXISTS, please try different shop url.', 'wk-marketplace' ),
						);
					} else {
						$response = array(
							'error'   => false,
							'message' => esc_html__( 'This shop URl is available, kindly proceed.', 'wk-marketplace' ),
						);
					}
				} else {
					$response = array(
						'error'   => true,
						'message' => esc_html__( 'Shop url not found!', 'wk-marketplace' ),
					);
				}
			} else {
				$response = array(
					'error'   => true,
					'message' => esc_html__( 'Security check failed!', 'wk-marketplace' ),
				);
			}

			wp_send_json( $response );
		}

		/**
		 * Add\update favourite seller.
		 */
		public function wkmp_update_favourite_seller() {
			$json = array();
			if ( ! check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) || ! current_user_can( 'read' ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
			}

			$seller_id   = empty( $_POST['seller_id'] ) ? 0 : intval( wp_unslash( $_POST['seller_id'] ) );
			$customer_id = empty( $_POST['customer_id'] ) ? 0 : intval( wp_unslash( $_POST['customer_id'] ) );

			if ( $seller_id > 0 && $customer_id > 0 ) {
				$db_obj = Helper\WKMP_General_Queries::get_instance();
				$result = $db_obj->wkmp_update_shop_followers( $seller_id, $customer_id );

				$json['success'] = $result;
				$json['message'] = ( 'removed' === $result ) ? esc_html__( 'Seller removed from your favorite seller list.', 'wk-marketplace' ) : esc_html__( 'Seller added to your favorite seller list.', 'wk-marketplace' );
			}
			wp_send_json( $json );
		}

		/**
		 * State by country code.
		 */
		public function wkmp_get_seller_state_by_country_code() {
			$json = array();

			if ( ! ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) || check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) ) || ! current_user_can( 'wk_marketplace_seller' ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
			}

			$country_code = empty( $_POST['country_code'] ) ? '' : wc_clean( wp_unslash( $_POST['country_code'] ) );

			if ( ! empty( $country_code ) ) {
				$states = WC()->countries->get_states( $country_code );
				$html   = '';
				if ( $states ) {
					$html .= '<select name="wkmp_shop_state" id="wkmp_shop_state" class="form-control">';
					$html .= '<option value="">' . esc_html__( 'Select state', 'wk-marketplace' ) . '</option>';
					foreach ( $states as $key => $state ) {
						$html .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $state ) . '</option>';
					}
					$html .= '</select>';
				} else {
					$html .= '<input id="wkmp_shop_state" type="text" placeholder="' . esc_attr__( 'State', 'wk-marketplace' ) . '" name="wkmp_shop_state" class="form-control" />';
				}
				$json['success'] = true;
				$json['html']    = $html;
			}

			wp_send_json( $json );
		}

		/**
		 * Marketplace variation function
		 *
		 * @param int $variation_id Variable id.
		 */
		public function wkmp_marketplace_add_variation_attribute( $variation_id = 0 ) {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) && current_user_can( 'wk_marketplace_seller' ) ) {
				$wk_pro_id = empty( $_POST['product'] ) ? 0 : intval( wp_unslash( $_POST['product'] ) );

				if ( ! empty( $wk_pro_id ) ) {
					$post_title = sprintf( /* translators: %d Product id. */ esc_html__( 'Variation # %d of Product', 'wk-marketplace' ), $wk_pro_id );
					$post_name  = 'product-' . $wk_pro_id . '-variation';

					$product_data = array(
						'post_author'           => get_post_field( 'post_author', $wk_pro_id ),
						'post_date'             => '',
						'post_date_gmt'         => '',
						'post_content'          => '',
						'post_content_filtered' => '',
						'post_title'            => $post_title,
						'post_excerpt'          => '',
						'post_status'           => 'publish',
						'post_type'             => 'product_variation',
						'comment_status'        => 'open',
						'ping_status'           => 'open',
						'post_password'         => '',
						'post_name'             => $post_name,
						'to_ping'               => '',
						'pinged'                => '',
						'post_modified'         => '',
						'post_modified_gmt'     => '',
						'post_parent'           => $wk_pro_id,
						'menu_order'            => '',
						'guid'                  => '',
					);

					wp_set_object_terms( $wk_pro_id, 'variable', 'product_type' );
					$variation_id = wp_insert_post( $product_data );
					\WC_Product_Variable::sync( $wk_pro_id );

					$thumb_id = get_post_meta( $variation_id, '_thumbnail_id', true );

					require_once WKMP_LITE_PLUGIN_FILE . 'templates/front/seller/product/wkmp-variations.php';
					die;
				} else {
					$wk_pro_id = $variation_id;

					$args = array(
						'post_parent'    => $wk_pro_id,
						'post_type'      => 'product_variation',
						'posts_per_page' => - 1,
						'post_status'    => 'publish',
					);

					$children_array = get_children( $args );
					$i              = 0;

					foreach ( $children_array as $var_att ) {
						$this->wkmp_attribute_variation_data( $var_att->ID, $wk_pro_id );
						++$i;
					}
				}
				if ( $wk_pro_id ) {
					wp_die();
				}
			}
		}

		/**
		 * Attribute variation data.
		 *
		 * @param int $variation_id Variation id.
		 * @param int $wk_pro_id Product id.
		 */
		public function wkmp_attribute_variation_data( $variation_id, $wk_pro_id ) {
			$thumb_id            = get_post_meta( $variation_id, '_thumbnail_id', true );
			$product_ping_status = array(
				'ID'          => $wk_pro_id,
				'ping_status' => 'closed',
			);

			wp_update_post( $product_ping_status );

			require_once WKMP_LITE_PLUGIN_FILE . 'templates/front/seller/product/wkmp-variations.php';
		}

		/**
		 * Remove variation attribute.
		 */
		public function wkmp_attributes_remove_variation() {
			$result = array(
				'success' => false,
				'msg'     => esc_html__( 'Some error in removing, kindly reload the page and try again!!', 'wk-marketplace' ),
			);

			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) && current_user_can( 'wk_marketplace_seller' ) ) {
				$var_id = empty( $_POST['var_id'] ) ? '' : intval( wp_unslash( $_POST['var_id'] ) );

				if ( $var_id > 0 ) {
					wp_delete_post( $var_id );
					$result['success'] = true;
					$result['msg']     = esc_html__( 'The variation has been removed successfully.', 'wk-marketplace' );
				}
			}
			wp_send_json( $result );
		}

		/**
		 * Product sku validation.
		 */
		public function wkmp_validate_seller_product_sku() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) && current_user_can( 'wk_marketplace_seller' ) ) {
				$wpdb_obj = $this->wpdb;
				$chk_sku  = empty( $_POST['psku'] ) ? '' : wc_clean( wp_unslash( $_POST['psku'] ) );

				$response = array(
					'success' => false,
					'message' => esc_html__( 'Please enter a valid alphanumeric SKU', 'wk-marketplace' ),
				);

				if ( ! empty( $chk_sku ) ) {
					$seller_id           = get_current_user_id();
					$dynamic_sku_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_dynamic_sku', true );
					$dynamic_sku_prefix  = get_user_meta( $seller_id, '_wkmp_dynamic_sku_prefix', true );

					$sku_post_id = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT post_id FROM {$wpdb_obj->prefix}postmeta WHERE meta_key='_sku' AND meta_value=%s", $chk_sku ) );

					if ( intval( $sku_post_id ) > 0 && $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
						$post_sku_prefix = get_post_meta( $sku_post_id, '_sku_prefix', true );
						$sku_post_id     = ( $post_sku_prefix === $dynamic_sku_prefix );
					}

					if ( ! empty( $sku_post_id ) ) {
						$response = array(
							'success' => false,
							'message' => esc_html__( 'SKU already exist please select another SKU', 'wk-marketplace' ),
						);
					} else {
						$response['success'] = true;
						$response['message'] = esc_html__( 'SKU is OK', 'wk-marketplace' );
					}
				}
				wp_send_json( $response );
			}
		}

		/**
		 * Gallery image delete.
		 */
		public function wkmp_productgallary_image_delete() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$img_id = empty( $_POST['img_id'] ) ? '' : wc_clean( wp_unslash( $_POST['img_id'] ) );

				$ip         = explode( 'i_', $img_id );
				$img_id     = get_post_meta( $ip[0], '_product_image_gallery', true );
				$arr        = array_diff( explode( ',', $img_id ), array( $ip[1] ) );
				$remain_ids = implode( ',', $arr );
				update_post_meta( $ip[0], '_product_image_gallery', $remain_ids );
				wp_send_json( $remain_ids );
			}
		}

		/**
		 * Downloadable file adding.
		 */
		public function wkmp_downloadable_file_add() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) ) {
				$y = empty( $_POST['var_id'] ) ? 0 : intval( wp_unslash( $_POST['var_id'] ) );
				$i = empty( $_POST['eleme_no'] ) ? 0 : intval( wp_unslash( $_POST['eleme_no'] ) );
				?>
				<div class="tr_div">
					<div>
						<label for="downloadable_upload_file_name_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'File Name', 'wk-marketplace' ); ?></label>
						<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File Name', 'wk-marketplace' ); ?>" id="downloadable_upload_file_name_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>" name="_mp_variation_downloads_files_name[<?php echo esc_attr( $y ); ?>][<?php echo esc_attr( $i ); ?>]" value="">
					</div>
					<div class="file_url">
						<label for="downloadable_upload_file_url_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'File Url', 'wk-marketplace' ); ?></label>
						<input type="text" class="input_text" placeholder="http://" id="downloadable_upload_file_url_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>" name="_mp_variation_downloads_files_url[<?php echo esc_attr( $y ); ?>][<?php echo esc_attr( $i ); ?>]" value="">
						<a href="javascript:void(0);" class="button wkmp_downloadable_upload_file" id="<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'Choose&nbsp;file', 'wk-marketplace' ); ?></a>
						<a href="javascript:void(0);" class="delete mp_var_del" id="mp_var_del_<?php echo esc_attr( $y ) . '_' . esc_attr( $i ); ?>"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></a>
					</div>
					<div class="file_url_choose">

					</div>
				</div>
				<?php
				die;
			}
		}

		/**
		 * Change seller dashboard settings.
		 */
		public function wkmp_change_dashboard_to_backend_seller() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) && current_user_can( 'wk_marketplace_seller' ) ) {
				global $wkmarketplace;
				$data      = array();
				$change_to = empty( $_POST['change_to'] ) ? '' : wc_clean( wp_unslash( $_POST['change_to'] ) );

				if ( ! empty( $change_to ) ) {
					$c_user_id    = get_current_user_id();
					$current_dash = get_user_meta( $c_user_id, 'wkmp_seller_backend_dashboard', true );

					if ( 'front_dashboard' === $change_to ) {
						if ( $current_dash ) {
							update_user_meta( $c_user_id, 'wkmp_seller_backend_dashboard', null );
							$data['redirect'] = esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/' . get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ) );
						}
					} elseif ( 'backend_dashboard' === $change_to ) {
						update_user_meta( $c_user_id, 'wkmp_seller_backend_dashboard', true );
						$wkmarketplace->wkmp_add_role_cap( $c_user_id );
						$data['redirect'] = esc_url( admin_url( 'admin.php?page=seller' ) );
					}
				}

				wp_send_json( $data );
			}
		}

		/**
		 * Delete seller product.
		 *
		 * @return void
		 */
		public function wkmp_delete_seller_selected_product() {
			if ( check_ajax_referer( 'wkmp-front-nonce', 'wkmp_nonce', false ) && current_user_can( 'wk_marketplace_seller' ) ) {
				$product_id = empty( $_POST['product_id'] ) ? 0 : intval( wp_unslash( $_POST['product_id'] ) );

				$resp = array(
					'success' => false,
					'message' => esc_html__( 'Unable to delete the product. Please try again later!!', 'wk-marketplace' ),
				);

				if ( $product_id > 0 ) {
					$product_author = get_post_field( 'post_author', $product_id );
					$seller_id      = get_current_user_id();

					if ( $seller_id > 0 && intval( $seller_id ) === intval( $product_author ) && wp_delete_post( $product_id ) ) {
						$resp['message'] = esc_html__( 'Product(s) deleted successfully.', 'wk-marketplace' );
						$resp['success'] = true;
					}
				}

				wp_send_json( $resp );
			}
		}
	}
}
