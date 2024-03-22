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

if ( ! class_exists( 'WC_Email_WKMP_Seller_Order_Paid' ) ) {
	/**
	 * Class WC_Email_WKMP_Seller_Order_Paid
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_Order_Paid extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Order_Paid constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_seller_order_paid';
			$this->title       = esc_html__( 'Seller Order Paid', 'wk-marketplace' );
			$this->description = esc_html__( 'This notification is sent to sellers when their order is paid by admin.', 'wk-marketplace' );

			$this->wkmp_default_email_place_holder();

			$this->template_html  = 'emails/wkmp-seller-order-paid.php';
			$this->template_plain = 'emails/plain/wkmp-seller-order-paid.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_order_paid_notification', array( $this, 'trigger' ), 10, 1 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param array $order_info Order info.
		 */
		public function trigger( $order_info ) {
			$this->setup_locale();

			$action   = ! empty( $order_info['action'] ) ? $order_info['action'] : '';
			$order_id = empty( $order_info['order_id'] ) ? 0 : $order_info['order_id'];

			if ( ! in_array( $action, array( 'approve', 'pay' ), true ) || $order_id < 1 ) {
				return;
			}

			$seller_id    = ! empty( $order_info['seller_id'] ) ? $order_info['seller_id'] : 0;
			$seller_email = '';

			if ( $seller_id > 0 ) {
				$seller_user = get_user_by( 'ID', $seller_id );
				if ( is_a( $seller_user, 'WP_User' ) ) {
					$seller_email = $seller_user->user_email;
				}
			}

			$this->wkmp_set_placeholder_value( $order_id );

			$this->recipient = empty( $seller_email ) ? $this->recipient : $seller_email;
			$mail_to         = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();

			$items        = array();
			$seller_order = wc_get_order( $order_id );
			$order_items  = ( is_a( $seller_order, 'WC_Order' ) ) ? $seller_order->get_items() : array();

			foreach ( $order_items as $order_item ) {
				$item_id   = empty( $order_item['product_id'] ) ? 0 : $order_item['product_id'];
				$author_id = get_post_field( 'post_author', $item_id );
				if ( intval( $author_id ) === intval( $seller_id ) ) {
					$items [] = $order_item;
				}
			}

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
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
			return __( 'Your order #{order_number} has been paid.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( 'Payment for your [{site_title}]: Order #{order_number} has been made.', 'wk-marketplace' );
		}
	}
}
