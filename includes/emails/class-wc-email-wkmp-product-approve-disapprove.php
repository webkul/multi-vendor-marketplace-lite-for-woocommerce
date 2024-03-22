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

if ( ! class_exists( 'WC_Email_WKMP_Product_Approve_Disapprove' ) ) {
	/**
	 * New Product Added by Seller Email.
	 *
	 * Class WC_Email_WKMP_Product_Approve_Disapprove
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Product_Approve_Disapprove extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Product status.
		 *
		 * @var string
		 */
		private $status = '';

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Product_Approve_Disapprove constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_product_approve_disapprove';
			$this->title       = esc_html__( 'Product Approve or Disapprove', 'wk-marketplace' );
			$this->description = esc_html__( 'Product approve/disapprove emails are sent to chosen recipient(s) ', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-product-approve-disapprove.php';
			$this->template_plain = 'emails/plain/wkmp-product-approve-disapprove.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';
			$this->status         = 'approve';

			add_action( 'wkmp_product_approve_disapprove_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param int    $seller_id Seller id.
		 * @param int    $product_id Product id.
		 * @param string $status Status.
		 */
		public function trigger( $seller_id, $product_id, $status = 'approve' ) {
			$this->setup_locale();

			$this->status = ( 'approve' === $status ) ? $status : 'disapprove';
			$seller_email = '';

			if ( ! empty( $seller_id ) ) {
				$seller_user  = get_userdata( $seller_id );
				$seller_email = ( is_a( $seller_user, 'WP_User' ) ) ? $seller_user->user_email : '';
			}

			$this->recipient = empty( $seller_email ) ? $this->recipient : $seller_email;
			$mail_to         = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();

			$this->data = array(
				'mail_to'           => $mail_to,
				'product_id'        => $product_id,
				'seller_id'         => $seller_id,
				'status'            => $this->status,
				'seller_first_name' => get_user_meta( $seller_id, 'first_name', true ),
				'mail_data'         => $this->wkmp_get_common_mail_data(),
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
			$heading = __( 'Your product(s) are approved.', 'wk-marketplace' );
			if ( 'approve' !== $this->status ) {
				$heading = __( 'Your product(s) are disapproved.', 'wk-marketplace' );
			}

			return $heading;
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			$subject = __( '[{site_title}]: Admin has approved your product(s).', 'wk-marketplace' );
			if ( 'approve' !== $this->status ) {
				$subject = __( '[{site_title}]: Admin has disapproved your product(s).', 'wk-marketplace' );
			}

			return $subject;
		}
	}
}
