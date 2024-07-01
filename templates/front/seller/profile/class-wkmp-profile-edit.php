<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Profile;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Profile_Edit' ) ) {
	/**
	 * Seller Profile Edit.
	 *
	 * Class WKMP_Profile_Edit
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Profile
	 */
	class WKMP_Profile_Edit {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		protected $seller_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Errors.
		 *
		 * @var array $errors Errors.
		 */
		private $errors = array();

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Profile_Edit constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id = $seller_id;
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
		 * Seller profile form.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_form( $seller_id ) {
			$this->seller_id = empty( $this->seller_id ) ? $seller_id : $this->seller_id;

			$nonce       = \WK_Caching::wk_get_request_data( 'wkmp-user-nonce', array( 'method' => 'post' ) );
			$posted_data = array();

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-user-nonce-action' ) ) {
				$posted_data['about_shop']      = empty( $_POST['wkmp_about_shop'] ) ? '' : stripslashes( wp_kses_post( $_POST['wkmp_about_shop'] ) );
				$posted_data['wkmp_shop_phone'] = empty( $_POST['wkmp_shop_phone'] ) ? '' : wc_clean( $_POST['wkmp_shop_phone'] );

				do_action( 'wkmp_validate_update_seller_profile', $posted_data, $this->seller_id );

				$errors = isset( $_POST['wkmp_errors'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['wkmp_errors'] ) ) : array();

				$posted_data['wkmp_profile_data'] = empty( $_POST['wkmp_profile_data'] ) ? array() : wp_unslash( $_POST['wkmp_profile_data'] );

				if ( empty( $errors ) ) {
					wc_print_notice( esc_html__( 'Profile has been updated.', 'wk-marketplace' ), 'success' );
				} else {
					$this->errors = $errors;
				}
			}

			$edit_form_obj = WKMP_Seller_Profile_Form::get_instance();
			$edit_form_obj->wkmp_seller_profile_edit_form( $this->seller_id, $this->errors, $posted_data );
		}
	}
}
