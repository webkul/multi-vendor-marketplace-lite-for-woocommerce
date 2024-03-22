<?php
/**
 * File Handler
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Emails;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! trait_exists( 'WC_Email_WKMP_Settings' ) ) {
	require_once __DIR__ . '/trait-wc-email-wkmp-settings.php';
}

if ( ! class_exists( 'WC_Email_WKMP_Registration_Details_To_Seller' ) ) {
	/**
	 * Class WC_Email_WKMP_Registration_Details_To_Seller
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Registration_Details_To_Seller extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Registration_Details_To_Seller constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_registration_details_to_seller';
			$this->title       = esc_html__( 'Registration Details To Seller', 'wk-marketplace' );
			$this->description = esc_html__( 'New seller emails are sent to chosen recipient(s) ', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-registration-to-seller.php';
			$this->template_plain = 'emails/plain/wkmp-registration-to-seller.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			// Call parent constructor.
			parent::__construct();

			add_action( 'wkmp_registration_details_to_seller_notification', array( $this, 'trigger' ), 10, 1 );

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param string $info Information.
		 */
		public function trigger( $info ) {
			$this->setup_locale();
			$info              = is_array( $info ) ? $info : array();
			$seller_email      = empty( $info['user_email'] ) ? '' : $info['user_email'];
			$this->recipient   = empty( $seller_email ) ? $this->recipient : $seller_email;
			$mail_to           = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();
			$info['mail_to']   = $mail_to;
			$info['mail_data'] = $this->wkmp_get_common_mail_data();

			$this->data = $info;

			if ( $this->is_enabled() && $mail_to ) {
				$this->send( $mail_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
			$this->restore_locale();
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Your seller account details', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: Your seller account details.', 'wk-marketplace' );
		}
	}
}
