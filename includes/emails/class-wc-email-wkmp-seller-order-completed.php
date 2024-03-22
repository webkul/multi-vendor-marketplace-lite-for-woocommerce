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

if ( ! class_exists( 'WC_Email_WKMP_Seller_Order_Completed' ) ) {
	/**
	 * Class WC_Email_WKMP_Seller_Order_Completed
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_Order_Completed extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Order_Completed constructor.
		 */
		public function __construct() {
			$this->id    = 'wkmp_seller_order_completed';
			$this->title = esc_html__( 'Seller Order Completed', 'wk-marketplace' );

			$this->wkmp_default_email_place_holder();

			$this->description   = esc_html__( 'Order complete emails are sent to sellers when their orders are marked completed and usually indicate that their orders have been shipped.', 'wk-marketplace' );
			$this->template_html = 'emails/wkmp-seller-order-completed.php';
			$this->template_base = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_order_completed_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param int    $order_id Order id.
		 * @param array  $items Items.
		 * @param string $seller_email Seller email.
		 */
		public function trigger( $order_id, $items, $seller_email ) {
			$this->setup_locale();
			$this->wkmp_set_placeholder_value( $order_id );

			$this->recipient = $seller_email;

			$mail_to = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();

			$this->data = array(
				'order_id'         => $order_id,
				'seller_email'     => $seller_email,
				'recipient'        => $this->get_recipient(),
				'mail_to'          => $mail_to,
				'product_details'  => $this->wkmp_get_email_product_details( $items ),
				'date_string'      => $this->wkmp_get_email_date_string( $order_id ),
				'commission_data'  => $this->wkmp_get_email_commission_data( $seller_email, $order_id ),
				'common_functions' => $this->get_common_functions_object(),
			);

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
			return __( 'Your {site_title} order #{order_number} is now complete.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: Order #{order_number} is now complete.', 'wk-marketplace' );
		}
	}
}
