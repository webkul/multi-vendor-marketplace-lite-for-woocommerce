<?php
/**
 * File Handler
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Emails;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! trait_exists( 'WC_Email_WKMP_Settings' ) ) {
	require_once __DIR__ . '/trait-wc-email-wkmp-settings.php';
}

if ( ! class_exists( 'WC_Email_WKMP_Ask_To_Admin' ) ) {
	/**
	 * Ask to Admin Email.
	 *
	 * Class WC_Email_WKMP_Ask_To_Admin
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Ask_To_Admin extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Ask_To_Admin constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_ask_to_admin';
			$this->title       = esc_html__( 'Ask To Admin', 'wk-marketplace' );
			$this->description = esc_html__( 'Seller Query emails are sent to chosen recipient(s) ', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-ask-to-admin.php';
			$this->template_plain = 'emails/plain/wkmp-ask-to-admin.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_ask_to_admin_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email', false ) );
		}

		/**
		 * Trigger.
		 *
		 * @param string $seller_email Email.
		 * @param string $subject Subject.
		 * @param string $message Seller message.
		 */
		public function trigger( $seller_email, $subject, $message ) {
			if ( ! empty( $seller_email ) && ! empty( $subject ) && ! empty( $message ) ) {
				$this->setup_locale();

				$seller_email = filter_var( $seller_email, FILTER_SANITIZE_EMAIL );
				$subject      = filter_var( $subject, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$message      = filter_var( $message, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$mail_to      = empty( $this->get_recipient() ) ? get_option( 'admin_email' ) : $this->get_recipient();

				$this->data = array(
					'seller_email' => $seller_email,
					'subject'      => $subject,
					'message'      => $message,
					'mail_to'      => $mail_to,
					'mail_data'    => $this->wkmp_get_common_mail_data(),
				);

				$this->subject = $subject;

				$headers       = 'MIME-Version: 1.0' . "\n";
				$headers      .= 'From: ' . $seller_email . "\n";
				$headers      .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
				$headers      .= "X-Priority: 1 (Highest)\n";
				$headers      .= "X-MSMail-Priority: High\n";
				$headers      .= "Importance: High\n";
				$this->headers = $headers;

				if ( $this->is_enabled() && $mail_to ) {
					$this->send( $mail_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				}
				$this->restore_locale();
			}
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'New Seller Query', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: New Seller query.', 'wk-marketplace' );
		}
	}
}
