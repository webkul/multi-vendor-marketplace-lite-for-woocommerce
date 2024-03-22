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

if ( ! class_exists( 'WC_Email_WKMP_Seller_Order_Refunded' ) ) {
	/**
	 * Class WC_Email_WKMP_Seller_Order_Refunded
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_Order_Refunded extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Refunded amount.
		 *
		 * @var mixed
		 */
		public $refunded_amount;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Order_Refunded constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_seller_order_refunded';
			$this->title       = esc_html__( 'Seller Order Refunded', 'wk-marketplace' );
			$this->description = esc_html__( 'Order refunded emails are sent to sellers when their orders are refunded.', 'wk-marketplace' );

			$this->wkmp_default_email_place_holder();

			$this->template_html  = 'emails/wkmp-seller-order-refunded.php';
			$this->template_plain = 'emails/plain/wkmp-seller-order-refunded.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_order_refunded_notification', array( $this, 'trigger' ), 10, 3 );
			add_action( 'wkmp_seller_order_refunded_completely_notification', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param array  $items Items.
		 * @param string $seller_email Seller Email.
		 * @param array  $refund_args Refund arguments..
		 */
		public function trigger( $items, $seller_email, $refund_args = array() ) {
			$this->setup_locale();

			$order_id              = empty( $refund_args['order_id'] ) ? 0 : $refund_args['order_id'];
			$this->recipient       = empty( $seller_email ) ? $this->recipient : $seller_email;
			$mail_to               = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();
			$this->refunded_amount = empty( $refund_args['amount'] ) ? 0 : $refund_args['amount'];

			$this->data = array(
				'order_id'         => $order_id,
				'seller_email'     => $seller_email,
				'recipient'        => $this->get_recipient(),
				'refunded_amount'  => $this->refunded_amount,
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
			return __( 'Your order #{order_number} has been refunded.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( 'Your [{site_title}]: Order #{order_number} has been refunded.', 'wk-marketplace' );
		}
	}
}
