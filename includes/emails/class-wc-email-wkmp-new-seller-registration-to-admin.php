<?php
/**
 * File Handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Emails;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! trait_exists( 'WC_Email_WKMP_Settings' ) ) {
	require_once __DIR__ . '/trait-wc-email-wkmp-settings.php';
}

if ( ! class_exists( 'WC_Email_WKMP_New_Seller_Registration_To_Admin' ) ) {
	/**
	 * Class WC_Email_WKMP_New_Seller_Registration_To_Admin
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_New_Seller_Registration_To_Admin extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_New_Seller_Registration_To_Admin constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_new_seller_registration_to_admin';
			$this->title       = esc_html__( 'Seller Registration to Admin', 'wk-marketplace' );
			$this->description = esc_html__( 'New seller emails are sent to chosen recipient(s).', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-new-seller-registration-to-admin.php';
			$this->template_plain = 'emails/plain/wkmp-new-seller-registration-to-admin.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			// Call parent constructor.
			parent::__construct();

			add_action( 'wkmp_new_seller_registration_to_admin_notification', array( $this, 'trigger' ), 10, 1 );

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email', false ) );
		}

		/**
		 * Trigger.
		 *
		 * @param array $info Information.
		 */
		public function trigger( $info ) {
			$this->setup_locale();

			$mail_to           = empty( $this->get_recipient() ) ? get_option( 'admin_email' ) : $this->get_recipient();
			$info              = is_array( $info ) ? $info : array();
			$info['mail_to']   = $mail_to;
			$info['mail_data'] = $this->wkmp_get_common_mail_data();
			$this->data        = $info;

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
			return __( 'New Seller Registration.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: New Seller Registration Details.', 'wk-marketplace' );
		}
	}
}
