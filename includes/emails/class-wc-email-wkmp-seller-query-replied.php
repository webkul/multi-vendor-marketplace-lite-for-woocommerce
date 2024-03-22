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

if ( ! class_exists( 'WC_Email_WKMP_Seller_Query_Replied' ) ) {
	/**
	 * Reply to seller regarding query Email.
	 *
	 * Class WC_Email_WKMP_Seller_Query_Replied
	 *
	 * @package WkMarketplace\Includes\Emails
	 */
	class WC_Email_WKMP_Seller_Query_Replied extends \WC_Email {
		use WC_Email_WKMP_Settings;

		/**
		 * Constructor of the class.
		 *
		 * WC_Email_WKMP_Seller_Query_Replied constructor.
		 */
		public function __construct() {
			$this->id          = 'wkmp_seller_query_replied';
			$this->title       = esc_html__( 'Reply to Seller Query', 'wk-marketplace' );
			$this->description = esc_html__( 'Query reply emails are sent to chosen recipient(s) ', 'wk-marketplace' );

			$this->template_html  = 'emails/wkmp-seller-query-replied.php';
			$this->template_plain = 'emails/plain/wkmp-seller-query-replied.php';
			$this->template_base  = WKMP_LITE_PLUGIN_FILE . 'woocommerce/templates/';

			add_action( 'wkmp_seller_query_replied_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', false );
		}

		/**
		 * Trigger.
		 *
		 * @param string $seller_email Seller email.
		 * @param int    $query_id Query Id.
		 * @param string $reply_data Reply Data.
		 */
		public function trigger( $seller_email, $query_id, $reply_data ) {
			$this->setup_locale();

			$this->recipient = empty( $seller_email ) ? $this->recipient : $seller_email;
			$mail_to         = empty( $this->get_recipient() ) ? $seller_email : $this->get_recipient();

			$this->data = array(
				'mail_to'    => $mail_to,
				'reply_data' => $reply_data,
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
			return __( '{site_title} Admin replies to your queries.', 'wk-marketplace' );
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_subject() {
			return __( '[{site_title}]: Admin have replied to your queries.', 'wk-marketplace' );
		}
	}
}
