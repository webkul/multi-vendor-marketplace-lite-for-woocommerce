<?php
/**
 * Seller Store info class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Store;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Store_Info' ) ) {
	/**
	 * Class WKMP_Seller_Store_Info
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Store
	 */
	class WKMP_Seller_Store_Info {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Marketplace class object.
		 *
		 * @var \Marketplace $marketplace Marketplace object.
		 */
		private $marketplace;

		/**
		 * Feedback Object.
		 *
		 * @var Common\WKMP_Seller_Feedback
		 */
		private $feedback_obj;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Seller_Store_Info constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->marketplace  = $wkmarketplace;
			$this->feedback_obj = Common\WKMP_Seller_Feedback::get_instance();
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
		 * Display seller all feedback.
		 *
		 * @param int $seller_id Seller Id.
		 *
		 * @return void
		 */
		public function wkmp_seller_all_feedback( $seller_id ) {
			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			$end_point = get_option( '_wkmp_feedbacks_endpoint', 'seller-feedbacks' );
			$main_page = get_query_var( 'main_page' );

			if ( ! empty( $end_point ) && ! empty( $main_page ) && false !== strpos( $main_page, $end_point ) ) {
				$query_vars      = explode( '/', $main_page );
				$this->seller_id = ( is_array( $query_vars ) && 2 === count( $query_vars ) ) ? $query_vars[1] : 0;

				if ( ! is_numeric( $this->seller_id ) || ( is_numeric( $this->seller_id ) && empty( get_user_by( 'ID', $this->seller_id ) ) ) ) {
					$this->seller_id = $this->marketplace->wkmp_get_seller_id_by_shop_address( $this->seller_id );
				}
			}

			$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );

			if ( ! empty( $seller_info ) ) {
				$page  = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;
				$limit = apply_filters( 'wkmp_front_per_page_feedback', 20 );

				$filter_data = array(
					'filter_seller_id' => $this->seller_id,
					'status'           => 1,
					'start'            => ( $page - 1 ) * $limit,
					'limit'            => $limit,
				);

				$reviews    = $this->feedback_obj->wkmp_get_seller_feedbacks( $filter_data );
				$total      = $this->feedback_obj->wkmp_get_seller_total_feedbacks( $filter_data );
				$url        = home_url( $this->marketplace->seller_page_slug . '/feedback/' . $this->seller_id );
				$pagination = $this->marketplace->wkmp_get_pagination( $total, $page, $limit, $url );

				require_once __DIR__ . '/wkmp-seller-all-feedback.php';
			}
		}

		/**
		 * Add seller feedback.
		 *
		 * @param int $seller_id Seller Id.
		 *
		 * @return void
		 */
		public function wkmp_seller_add_feedback_template( $seller_id ) {
			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			$end_point = get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' );
			$main_page = get_query_var( 'main_page' );

			if ( ! empty( $end_point ) && ! empty( $main_page ) && false !== strpos( $main_page, $end_point ) ) {
				$query_vars      = explode( '/', $main_page );
				$this->seller_id = ( is_array( $query_vars ) && 2 === count( $query_vars ) ) ? $query_vars[1] : 0;

				if ( ! is_numeric( $this->seller_id ) || ( is_numeric( $this->seller_id ) && empty( get_user_by( 'ID', $this->seller_id ) ) ) ) {
					$this->seller_id = $this->marketplace->wkmp_get_seller_id_by_shop_address( $this->seller_id );
				}
			}

			$seller_info       = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			$validation_errors = array();
			$posted_data       = array();
			$review_added      = false;

			if ( ! empty( $seller_info ) ) {
				$c_user_id = get_current_user_id();

				$review_check = $this->feedback_obj->wkmp_get_seller_feedbacks(
					array(
						'filter_seller_id' => $this->seller_id,
						'filter_user_id'   => $c_user_id,
					)
				);

				$review_status = isset( $review_check[0]->status ) ? $review_check[0]->status : '3';

				if ( $c_user_id > 0 && 0 !== intval( $review_status ) ) {
					$nonce = \WK_Caching::wk_get_request_data( 'wkmp-add-feedback-nonce', array( 'method' => 'post' ) );

					if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-add-feedback-nonce-action' ) ) {
						$posted_data['review_summary'] = empty( $_POST['feed_summary'] ) ? '-' : wc_clean( wp_unslash( $_POST['feed_summary'] ) );
						$posted_data['review_desc']    = empty( $_POST['feed_review'] ) ? '-' : wc_clean( wp_unslash( $_POST['feed_review'] ) );

						$posted_data['price_r']   = empty( $_POST['feed_price'] ) ? 0 : wc_clean( wp_unslash( $_POST['feed_price'] ) );
						$posted_data['value_r']   = empty( $_POST['feed_value'] ) ? 0 : wc_clean( wp_unslash( $_POST['feed_value'] ) );
						$posted_data['quality_r'] = empty( $_POST['feed_quality'] ) ? 0 : wc_clean( wp_unslash( $_POST['feed_quality'] ) );

						$validation_errors = $this->wkmp_validate_add_feedback_form( $posted_data );

						if ( empty( $validation_errors ) ) {
							$posted_data['seller_id'] = $this->seller_id;
							$posted_data['user_id']   = $c_user_id;
							$posted_data['nickname']  = $this->marketplace->wkmp_get_user_display_name( $c_user_id, '', 'nick' );
							$posted_data['status']    = apply_filters( 'wkmp_default_seller_review_status', 0, $posted_data );

							$obj = Common\WKMP_Seller_Feedback::get_instance();
							$obj->wkmp_insert_seller_feedback( $posted_data );

							do_action( 'wkmp_save_seller_feedback', $posted_data, $this->seller_id );

							$review_added = true;

							wc_print_notice( esc_html__( 'Feedback added successfully.', 'wk-marketplace' ), 'success' );
						}
					}
				}

				if ( ! empty( $validation_errors ) ) {
					wc_print_notice( esc_html__( 'Warning: Please check the form carefully for the errors', 'wk-marketplace' ), 'error' );
				}

				if ( $review_added ) {
					$review_check = $this->feedback_obj->wkmp_get_seller_feedbacks(
						array(
							'filter_seller_id' => $this->seller_id,
							'filter_user_id'   => $c_user_id,
						)
					);
				}

				require_once __DIR__ . '/wkmp-seller-add-feedback.php';
			} else {
				esc_html_e( 'Seller info is not available', 'wk-marketplace' );
			}
		}

		/**
		 * Validate seller feedback form.
		 *
		 * @param array $posted_data For data to validate.
		 *
		 * @return array
		 */
		private function wkmp_validate_add_feedback_form( $posted_data ) {
			$result = array();

			if ( empty( $posted_data['price_r'] ) ) {
				$result['feed_price_error'] = esc_html__( 'Price field require', 'wk-marketplace' );
			}

			if ( empty( $posted_data['value_r'] ) ) {
				$result['feed_value_error'] = esc_html__( 'Value field require', 'wk-marketplace' );
			}

			if ( empty( $posted_data['quality_r'] ) ) {
				$result['feed_quality_error'] = esc_html__( 'Quality field require', 'wk-marketplace' );
			}

			if ( strlen( $posted_data['review_summary'] ) < 3 ) {
				$result['feed_summary_error'] = esc_html__( 'Please add a summary of more than 3 character', 'wk-marketplace' );
			}

			if ( strlen( $posted_data['review_desc'] ) < 5 ) {
				$result['feed_description_error'] = esc_html__( 'Please add a review of more than 5 character', 'wk-marketplace' );
			}

			return $result;
		}

		/**
		 * Display seller store.
		 *
		 * @param int $seller_id Seller Id.
		 *
		 * @return void
		 */
		public function wkmp_display_seller_store( $seller_id ) {
			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			$end_point = get_option( '_wkmp_store_endpoint', 'seller-store' );
			$main_page = get_query_var( 'main_page' );

			if ( ! empty( $end_point ) && ! empty( $main_page ) && false !== strpos( $main_page, $end_point ) ) {
				$query_vars      = explode( '/', $main_page );
				$this->seller_id = ( is_array( $query_vars ) && count( $query_vars ) > 1 ) ? $query_vars[1] : 0;
				$store_paged     = ( is_array( $query_vars ) && count( $query_vars ) > 3 && 'page' === $query_vars[2] ) ? $query_vars[3] : 1;

				if ( ! is_numeric( $this->seller_id ) || ( is_numeric( $this->seller_id ) && empty( get_user_by( 'ID', $this->seller_id ) ) ) ) {
					$this->seller_id = $this->marketplace->wkmp_get_seller_id_by_shop_address( $this->seller_id );
				}
			}

			if ( $this->seller_id > 0 ) {
				$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
				$shop_banner = '';

				if ( isset( $seller_info->shop_banner_visibility ) && 'yes' === $seller_info->shop_banner_visibility ) {
					$shop_banner = WKMP_LITE_PLUGIN_URL . 'assets/images/mp-banner.png';
					$shop_banner = empty( $seller_info->shop_banner ) ? $shop_banner : $seller_info->shop_banner;
				}

				$shop_logo = WKMP_LITE_PLUGIN_URL . 'assets/images/shop-logo.png';
				$shop_logo = empty( $seller_info->shop_logo ) ? $shop_logo : $seller_info->shop_logo;

				$avatar_image = WKMP_LITE_PLUGIN_URL . 'assets/images/generic-male.png';
				$avatar_image = empty( $seller_info->avatar_image ) ? $avatar_image : $seller_info->avatar_image;

				$shopurl_visibility = get_option( 'wkmp_shop_url_visibility', 'required' );

				$seller_collection = site_url() . '/' . $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_seller_product_endpoint', 'seller-products' ) . '/';
				$add_review        = site_url() . '/' . $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' ) . '/';
				$all_review        = site_url() . '/' . $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_feedbacks_endpoint', 'seller-feedbacks' ) . '/';

				$no_slug = true;

				if ( 'remove' !== $shopurl_visibility ) {
					$shop_slug = get_user_meta( $this->seller_id, 'shop_address', true );
					if ( ! empty( $shop_slug ) ) {
						$seller_collection .= $shop_slug;
						$add_review        .= $shop_slug;
						$all_review        .= $shop_slug;
						$no_slug            = false;
					}
				}

				if ( $no_slug ) {
					$seller_collection .= $this->seller_id;
					$add_review        .= $this->seller_id;
					$all_review        .= $this->seller_id;
				}

				$filter_data = array(
					'filter_seller_id' => $this->seller_id,
					'status'           => 1,
					'start'            => 0,
					'limit'            => $this->feedback_obj->wkmp_get_seller_total_feedbacks(
						array(
							'filter_seller_id' => $this->seller_id,
							'status'           => 1,
						)
					),
				);

				$reviews = $this->feedback_obj->wkmp_get_seller_feedbacks( $filter_data );

				$review_check = $this->feedback_obj->wkmp_get_seller_feedbacks(
					array(
						'filter_seller_id' => $this->seller_id,
						'filter_user_id'   => get_current_user_id(),
					)
				);

				$num_of_stars   = 0;
				$total_feedback = 0;
				$price_stars    = 0;
				$value_stars    = 0;
				$quality_stars  = 0;

				if ( $reviews ) {
					foreach ( $reviews as $item ) {
						$num_of_stars  += $item->price_r;
						$price_stars   += $item->price_r;
						$num_of_stars  += $item->value_r;
						$value_stars   += $item->value_r;
						$num_of_stars  += $item->quality_r;
						$quality_stars += $item->quality_r;
						++$total_feedback;
					}
				}

				$quality = 0;
				if ( $num_of_stars > 0 ) {
					$quality = $num_of_stars / ( $total_feedback * 3 );

					$price_stars   /= $total_feedback;
					$value_stars   /= $total_feedback;
					$quality_stars /= $total_feedback;
				}
				if ( apply_filters( 'wkmp_show_default_seller_store_info', true ) ) {
					require_once __DIR__ . '/wkmp-seller-store-info.php';
				}

				$seller_data = array(
					'seller_id'      => $this->seller_id,
					'seller_info'    => $seller_info,
					'shop_banner'    => $shop_banner,
					'shop_logo'      => $shop_logo,
					'add_review'     => $add_review,
					'all_review'     => $all_review,
					'reviews'        => $reviews,
					'review_check'   => $review_check,
					'num_of_stars'   => $num_of_stars,
					'total_feedback' => $total_feedback,
					'price_stars'    => $price_stars,
					'value_stars'    => $value_stars,
					'quality'        => $quality,
					'quality_stars'  => $quality_stars,
				);

				do_action( 'wkmp_after_seller_store_info', $seller_data );
			} else {
				esc_html_e( 'No seller info', 'wk-marketplace' );
			}
		}

		/**
		 * Seller product collection.
		 *
		 * @param int $seller_id Seller Id.
		 *
		 * @return void
		 */
		public function wkmp_seller_store_collection( $seller_id ) {
			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			$end_point = get_option( '_wkmp_seller_product_endpoint', 'seller-products' );
			$main_page = get_query_var( 'main_page' );

			if ( ! empty( $end_point ) && ! empty( $main_page ) && false !== strpos( $main_page, $end_point ) ) {
				$query_vars      = explode( '/', $main_page );
				$this->seller_id = ( is_array( $query_vars ) && count( $query_vars ) > 1 ) ? $query_vars[1] : 0;
				$store_paged     = ( is_array( $query_vars ) && count( $query_vars ) > 3 && 'page' === $query_vars[2] ) ? $query_vars[3] : 1;

				if ( ! is_numeric( $this->seller_id ) || ( is_numeric( $this->seller_id ) && empty( get_user_by( 'ID', $this->seller_id ) ) ) ) {
					$this->seller_id = $this->marketplace->wkmp_get_seller_id_by_shop_address( $this->seller_id );
				}
			}

			if ( $this->seller_id > 0 ) {
				$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
				require_once __DIR__ . '/wkmp-seller-store-collection.php';
			}
		}

		/**
		 * Display seller profile details section.
		 *
		 * @param string $endpoint Endpoint.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_details_section( $endpoint = '' ) {
			$seller_info = $this->marketplace->wkmp_get_seller_info( $this->seller_id );
			$shop_logo   = WKMP_LITE_PLUGIN_URL . 'assets/images/shop-logo.png';

			if ( isset( $seller_info->_thumbnail_id_company_logo ) && $seller_info->_thumbnail_id_company_logo ) {
				$shop_logo = wp_get_attachment_image_src( $seller_info->_thumbnail_id_company_logo )[0];
			}

			$add_review        = site_url() . '/' . $this->marketplace->seller_page_slug . '/add-feedback/';
			$seller_collection = site_url() . '/' . $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_seller_product_endpoint', 'seller-products' ) . '/';

			$shopurl_visibility = get_option( 'wkmp_shop_url_visibility', 'required' );

			$no_slug = true;

			if ( 'remove' !== $shopurl_visibility ) {
				$shop_slug = get_user_meta( $this->seller_id, 'shop_address', true );
				if ( ! empty( $shop_slug ) ) {
					$add_review        .= $shop_slug;
					$seller_collection .= $shop_slug;
					$no_slug            = false;
				}
			}

			if ( $no_slug ) {
				$add_review        .= $this->seller_id;
				$seller_collection .= $this->seller_id;
			}

			$seller_store = $this->marketplace->wkmp_get_seller_store_url( $this->seller_id );

			$filter_data = array(
				'filter_seller_id' => $this->seller_id,
				'status'           => 1,
				'start'            => 0,
				'limit'            => $this->feedback_obj->wkmp_get_seller_total_feedbacks(
					array(
						'filter_seller_id' => $this->seller_id,
						'status'           => 1,
					)
				),
			);

			$reviews = $this->feedback_obj->wkmp_get_seller_feedbacks( $filter_data );

			$review_check = $this->feedback_obj->wkmp_get_seller_feedbacks(
				array(
					'filter_seller_id' => $this->seller_id,
					'filter_user_id'   => get_current_user_id(),
				)
			);

			$num_of_stars   = 0;
			$total_feedback = 0;
			$price_stars    = 0;
			$value_stars    = 0;
			$quality_stars  = 0;

			if ( $reviews ) {
				foreach ( $reviews as $item ) {
					$num_of_stars  += $item->price_r;
					$price_stars   += $item->price_r;
					$num_of_stars  += $item->value_r;
					$value_stars   += $item->value_r;
					$num_of_stars  += $item->quality_r;
					$quality_stars += $item->quality_r;
					++$total_feedback;
				}
			}

			$quality = 0;
			if ( $num_of_stars > 0 ) {
				$quality = $num_of_stars / ( $total_feedback * 3 );

				$price_stars   /= $total_feedback;
				$value_stars   /= $total_feedback;
				$quality_stars /= $total_feedback;
			}

			$end_point = $endpoint;

			require_once __DIR__ . '/wkmp-seller-store-details-section.php';
		}
	}
}
