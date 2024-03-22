<?php
/**
 * Using trait for including common feature in all email files.
 *
 * In other words to experience multiple inheritance.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Emails;

use WkMarketplace\Helper\Common as HelperCommon;
use WkMarketplace\Includes\Common as IncludeCommon;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! trait_exists( 'WC_Email_WKMP_Settings' ) ) {
	/**
	 * WC_Email_WKMP_Settings
	 */
	trait WC_Email_WKMP_Settings {
		/**
		 * Admin Email class ids.
		 *
		 * @var array
		 */
		public $admin_email_ids = array(
			'wkmp_customer_become_seller_to_admin',
			'wkmp_new_seller_registration_to_admin',
			'wkmp_ask_to_admin',
			'wkmp_seller_published_product',
		);

		/**
		 * Headers.
		 *
		 * @var string
		 */
		public $headers;

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading'      => $this->get_heading(),
					'customer_email'     => $this->get_recipient(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
					'data'               => $this->data,
					'additional_content' => $this->get_additional_content(),
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get Common functions object.
		 *
		 * @return object
		 */
		public function get_common_functions_object() {
			return IncludeCommon\WKMP_Common_Functions::get_instance();
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'email_heading'      => $this->get_heading(),
					'customer_email'     => $this->get_recipient(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'data'               => $this->data,
					'email'              => $this,
					'additional_content' => $this->get_additional_content(),
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Default Additional content.
		 */
		public function get_default_additional_content() {
			$msg = __( 'Thanks for choosing {site_title} marketplace.', 'wk-marketplace' );
			if ( in_array( $this->id, $this->admin_email_ids, true ) ) {
				$msg = __( 'Congratulations on new seller on {site_title}.', 'wk-marketplace' );
				if ( in_array( $this->id, array( 'wkmp_seller_published_product', 'wkmp_ask_to_admin' ), true ) ) {
					$msg = __( 'Thanks for reading.', 'wk-marketplace' );
				}
			}

			if ( 'wkmp_seller_to_shop_followers' === $this->id ) {
				$msg = __( 'Thanks for following us.', 'wk-marketplace' );
			}

			return $msg;
		}

		/**
		 * Initialize settings form fields.
		 */
		public function init_form_fields() {
			/* translators: %s: list of placeholders */
			$placeholder_text = sprintf( __( 'Available placeholders: %s', 'wk-marketplace' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
			$default_to       = in_array( $this->id, $this->admin_email_ids, true ) ? __( 'Admin', 'wk-marketplace' ) : __( 'seller', 'wk-marketplace' );

			$this->form_fields = array();

			$this->form_fields['enabled'] = array(
				'title'   => __( 'Enable/Disable', 'wk-marketplace' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wk-marketplace' ),
				'default' => 'yes',
			);

			if ( 'wkmp_seller_to_shop_followers' !== $this->id ) {
				$this->form_fields['recipient'] = array(
					'title'       => __( 'Recipient(s)', 'wk-marketplace' ),
					'type'        => 'text',
					'description' => wp_sprintf( /* translators: %s: Default to. */ esc_html__( 'Enter recipients (comma separated) for this email. Defaults to %s email.', 'wk-marketplace' ), $default_to ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				);
			}

			$this->form_fields['subject']            = array(
				'title'       => __( 'Subject', 'wk-marketplace' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			);
			$this->form_fields['heading']            = array(
				'title'       => __( 'Email heading', 'wk-marketplace' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			);
			$this->form_fields['additional_content'] = array(
				'title'       => __( 'Additional content', 'wk-marketplace' ),
				'description' => __( 'Text to appear below the main email content.', 'wk-marketplace' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'wk-marketplace' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			);
			$this->form_fields['email_type']         = array(
				'title'       => __( 'Email type', 'wk-marketplace' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wk-marketplace' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			);
		}

		/**
		 * Default placeholder function.
		 *
		 * @return void
		 */
		public function wkmp_default_email_place_holder() {
			$this->placeholders = array_merge(
				array(
					'{order_date}'   => '',
					'{order_number}' => '',
				),
				$this->placeholders
			);
		}

		/**
		 * Setting default placeholder value.
		 *
		 * @param int|object $order Order id or WC Order object.
		 *
		 * @return void
		 */
		public function wkmp_set_placeholder_value( $order ) {
			if ( ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
				$this->placeholders['{order_number}'] = $order->get_order_number();
			}
		}

		/**
		 * Get date string for emails.
		 *
		 * @param int|object $order Order id or WC Order object.
		 *
		 * @return string
		 */
		public function wkmp_get_email_date_string( $order ) {
			if ( ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order );
			}

			if ( ! is_a( $order, 'WC_Order' ) ) {
				return gmdate( 'Y-m-d' );
			}

			$sell_date = $order->get_date_created();

			$arr_day = array(
				'Monday'    => html_entity_decode( esc_html__( 'Monday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'Tuesday'   => html_entity_decode( esc_html__( 'Tuesday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'Wednesday' => html_entity_decode( esc_html__( 'Wednesday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'Thursday'  => html_entity_decode( esc_html__( 'Thursday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'Friday'    => html_entity_decode( esc_html__( 'Friday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'Saturday'  => html_entity_decode( esc_html__( 'Saturday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'Sunday'    => html_entity_decode( esc_html__( 'Sunday', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
			);

			$arr_month = array(
				'January'   => html_entity_decode( esc_html__( 'January', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'February'  => html_entity_decode( esc_html__( 'February', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'March'     => html_entity_decode( esc_html__( 'March', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'April'     => html_entity_decode( esc_html__( 'April', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'May'       => html_entity_decode( esc_html__( 'May', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'June'      => html_entity_decode( esc_html__( 'June', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'July'      => html_entity_decode( esc_html__( 'July', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'August'    => html_entity_decode( esc_html__( 'August', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'September' => html_entity_decode( esc_html__( 'September', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'October'   => html_entity_decode( esc_html__( 'October', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'November'  => html_entity_decode( esc_html__( 'November', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'December'  => html_entity_decode( esc_html__( 'December', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
			);

			$order_day   = gmdate( 'l', strtotime( $sell_date ) );
			$order_month = gmdate( 'F', strtotime( $sell_date ) );

			return $arr_day[ $order_day ] . ', ' . $arr_month[ $order_month ] . ' ' . gmdate( 'j, Y', strtotime( $sell_date ) );
		}

		/**
		 * Get product details for email table.
		 *
		 * @param array $items Seller order items.
		 *
		 * @return array
		 */
		public function wkmp_get_email_product_details( $items ) {
			$items           = is_iterable( $items ) ? $items : array();
			$product_details = array();

			foreach ( $items as $product_detail ) {
				$product_id  = $product_detail->get_product_id();
				$variable_id = $product_detail->get_variation_id();
				$meta_data   = $product_detail->get_meta_data();
				$qty         = $product_detail->get_data()['quantity'];

				$product_total_price = $product_detail->get_data()['subtotal'];

				$item_data = array();

				if ( ! empty( $meta_data ) && is_iterable( $meta_data ) ) {
					foreach ( $meta_data as $meta ) {
						$item_data[] = $this->wkmp_validate_order_item_meta( $meta->get_data() );
					}
				}

				$product_details[ $product_id ][] = array(
					'product_name'        => empty( $product_detail['name'] ) ? __( 'N/A', 'wk-marketplace' ) : $product_detail['name'],
					'qty'                 => $qty,
					'variable_id'         => $variable_id,
					'product_total_price' => $product_total_price,
					'meta_data'           => $item_data,
					'tax'                 => $product_detail->get_total_tax(),
				);
			}

			return $product_details;
		}

		/**
		 * Get commission data.
		 *
		 * @param int $seller_email Seller Email.
		 * @param int $order_id Order id.
		 *
		 * @return array
		 */
		public function wkmp_get_email_commission_data( $seller_email, $order_id ) {
			require_once WKMP_LITE_PLUGIN_FILE . 'helper/common/class-wkmp-commission.php';
			$commission = HelperCommon\WKMP_Commission::get_instance();
			$seller_id  = 0;

			if ( ! empty( $seller_email ) ) {
				$seller_user = get_user_by( 'email', $seller_email );
				$seller_id   = ( is_a( $seller_user, 'WP_User' ) ) ? $seller_user->ID : $seller_id;
			}

			return $commission->wkmp_get_seller_final_order_info( $order_id, $seller_id );
		}

		/**
		 * Return common mail data.
		 *
		 * @return array
		 */
		public function wkmp_get_common_mail_data() {
			return array(
				'hi_msg'             => html_entity_decode( esc_html__( 'Hi ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'click_text'         => html_entity_decode( esc_html__( 'Click Here ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'profile_msg'        => html_entity_decode( esc_html__( ' to enter into your profile.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'approve_msg'        => html_entity_decode( esc_html__( ' to approve this account.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'reference_msg'      => html_entity_decode( esc_html__( 'If you have any problems, please contact me at ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . "\r\n\r\n",
				'email_label'        => html_entity_decode( esc_html__( 'Email: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'message_label'      => html_entity_decode( esc_html__( 'Message: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'subject_label'      => html_entity_decode( esc_html__( 'Subject: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'answer_label'       => html_entity_decode( esc_html__( 'Answer: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'vendor_label'       => html_entity_decode( esc_html__( 'Vendor ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'request_to_publish' => html_entity_decode( esc_html__( ' has requested to publish a product: ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ),
				'seller_url'         => esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/' . get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ) ),
				'mp_admin_url'       => esc_url( admin_url( 'admin.php?page=wk-marketplace' ) ),
				'auto_approve'       => get_option( '_wkmp_auto_approve_seller', true ),
				'admin_email'        => get_option( 'admin_email', false ),
			);
		}

		/**
		 * Validate metadata and replace seller id with seller shop link.
		 *
		 * @param array $meta_data Meta data.
		 *
		 * @return array
		 */
		public function wkmp_validate_order_item_meta( $meta_data ) {
			$common_functions = IncludeCommon\WKMP_Common_Functions::get_instance();
			$meta_value       = empty( $meta_data['value'] ) ? '' : $meta_data['value'];

			if ( ! empty( $meta_value ) ) {
				$meta_data['value'] = $common_functions->wkmp_validate_sold_by_order_item_meta( $meta_value, (object) $meta_data );
			}

			return $meta_data;
		}
	}
}
