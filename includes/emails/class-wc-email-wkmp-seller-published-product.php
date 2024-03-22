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

if ( ! class_exists( 'WC_Email_WKMP_Seller_Published_Product' ) ) {

	/**
	 * Product Publish Email.
	 */
	class WC_Email_WKMP_Seller_Published_Product extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Published_Product constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_seller_published_product';
			$this->title       = esc_html__( 'Seller Published Product', 'wk-marketplace' );
			$this->description = esc_html__( 'Seller Published Product emails are sent to chosen recipient(s) ', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-seller-published-product.php';
			$this->template_plain = 'emails/plain/wkmp-seller-published-product.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_published_product_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email', false ) );
		}

		/**
		 * Trigger.
		 *
		 * @param int $user_id User id.
		 * @param int $product_id Product id.
		 */
		public function trigger( $user_id, $product_id ) {
			$this->setup_locale();

			$mail_to = empty( $this->get_recipient() ) ? get_option( 'admin_email' ) : $this->get_recipient();

			$this->data = array(
				'mail_to'    => $mail_to,
				'user_id'    => $user_id,
				'product_id' => $product_id,
				'mail_data'  => $this->wkmp_get_common_mail_data(),
			);

			if ( $this->is_enabled() && $mail_to ) {
				$this->send( $mail_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
			return __( 'New Product Publish Request', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: Seller requested to publish a new product.', 'wk-marketplace' );
		}
	}
}
