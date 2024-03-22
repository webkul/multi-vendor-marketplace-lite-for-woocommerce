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

if ( ! class_exists( 'WC_Email_WKMP_Seller_To_Shop_Followers' ) ) {
	/**
	 * Class WC_Email_WKMP_Seller_To_Shop_Followers
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_To_Shop_Followers extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_To_Shop_Followers constructor.
		 */
		public function __construct() {
			$this->id             = 'wkmp_seller_to_shop_followers';
			$this->title          = esc_html__( 'Seller to Shop Followers', 'wk-marketplace' );
			$this->description    = esc_html__( 'Notification emails from sellers are sent to chosen recipient(s) ', 'wk-marketplace' );
			$this->customer_email = true;

			$this->template_html = 'emails/wkmp-seller-to-shop-followers.php';
			$this->template_base = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_to_shop_followers_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger.
		 *
		 * @param string $customer_email Customer Email.
		 * @param string $subject Subject.
		 * @param string $message Seller message.
		 */
		public function trigger( $customer_email, $subject, $message ) {
			$this->setup_locale();
			$this->recipient = $customer_email;

			if ( ! empty( $subject ) ) {
				$this->subject = $subject;
			}

			$this->recipient = empty( $customer_email ) ? $this->recipient : $customer_email;

			$this->data = array(
				'message' => $message,
				'mail_to' => $customer_email,
			);

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
			$this->restore_locale();
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 * @since  3.1.0
		 */
		public function get_default_heading() {
			return __( 'Notification from your favorite seller.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( 'You have a new notification from one of your favorite seller on {site_title}.', 'wk-marketplace' );
		}
	}
}
