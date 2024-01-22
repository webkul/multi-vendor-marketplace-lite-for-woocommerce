<?php
/**
 * File Handler.
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Emails;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! trait_exists( 'WC_Email_WKMP_Settings' ) ) {
	require_once __DIR__ . '/trait-wc-email-wkmp-settings.php';
}

if ( ! class_exists( 'WC_Email_WKMP_Seller_Account_Disapproved' ) ) {
	/**
	 * Seller Disapprove Email.
	 *
	 * Class WC_Email_WKMP_Seller_Account_Disapproved
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_Account_Disapproved extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Account_Disapproved constructor.
		 */
		public function __construct() {
			$this->id             = 'wkmp_seller_account_disapproved';
			$this->title          = esc_html__( 'Seller Account Disapproved', 'wk-marketplace' );
			$this->customer_email = true;

			$this->heading = esc_html__( 'Seller Account Disapproved', 'wk-marketplace' );
			$this->subject = '[' . get_option( 'blogname' ) . ']' . esc_html__( ' Account Disapproved', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-seller-account-disapproved.php';
			$this->template_plain = 'emails/plain/wkmp-seller-account-disapproved.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_account_disapproved_notification', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param int $user_id User id.
		 */
		public function trigger( $user_id ) {
			$this->setup_locale();

			$seller_email = '';
			$first_name   = '';

			if ( ! empty( $user_id ) ) {
				$user_data    = get_userdata( $user_id );
				$seller_email = is_a( $user_data, 'WP_User' ) ? $user_data->user_email : '';
				$first_name   = get_user_meta( $user_id, 'first_name', true );
			}

			$this->recipient = empty( $seller_email ) ? $this->recipient : $seller_email;
			$mail_to         = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();

			$this->data = array(
				'mail_to'    => $mail_to,
				'first_name' => $first_name,
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
			return __( 'Seller Account Disapproved.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( 'Your seller account on {site_title} has been disapproved/deactivated.', 'wk-marketplace' );
		}
	}
}
