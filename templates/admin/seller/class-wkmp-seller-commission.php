<?php
/**
 * Admin Seller commission template class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

use WkMarketplace\Helper;
use WkMarketplace\Helper\Common;
use WkMarketplace\Templates\Admin as AdminTemplates;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Commission' ) ) {
	/**
	 * Admin Seller commission template class.
	 *
	 * Class WKMP_Seller_Commission
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Commission {
		/**
		 * Form field builder
		 *
		 * @var object
		 */
		protected $form_helper;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Commission constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id   = $seller_id;
			$this->form_helper = Helper\WKMP_Form_Field_Builder::get_instance();
			$this->wkmp_display_commission_templates();
		}

		/**
		 * Commission templates.
		 */
		public function wkmp_display_commission_templates() {
			$nonce = \WK_Caching::wk_get_request_data( 'wkmp-admin-seller-commission-nonce-value', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-admin-seller-commission-nonce-action' ) ) {
				$commission = empty( $_POST['wkmp_seller_commission'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_seller_commission'] ) );
				$commission = is_null( $commission ) ? '' : wc_format_decimal( trim( stripslashes( $commission ) ) );

				if ( empty( $commission ) || ( is_numeric( $commission ) && $commission >= 0 && $commission <= 100 ) ) {
					$commission_data = array( 'wkmp_seller_commission' => $commission );
					do_action( 'wkmp_save_seller_commission', $commission_data, $this->seller_id );
					?>
					<div class="notice notice-success wkmp-admin-notice is-dismissible">
						<p><?php esc_html_e( 'Commission saved successfully.', 'wk-marketplace' ); ?></p>
					</div>
					<?php
				} else {
					?>
					<div class="notice notice-error wkmp-admin-notice is-dismissible">
						<p><?php printf( /* translators: %s: Commission. */ esc_html__( 'Invalid default commission value %s. Must be between 0 & 100.', 'wk-marketplace' ), esc_attr( $commission ) ); ?></p>
					</div>
					<?php
				}
			}

			$commission_helper = Common\WKMP_Commission::get_instance();

			$cur_symbol         = get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) );
			$commission_info    = $commission_helper->wkmp_get_seller_commission_info( $this->seller_id, 'commision_on_seller, seller_total_ammount, admin_amount' );
			$default_commission = empty( $commission_info->commision_on_seller ) ? get_option( '_wkmp_default_commission', 0 ) : $commission_info->commision_on_seller;
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();

			$form_fields = array(
				'entry' => array(
					'fields' => array(
						'wkmp_seller_commission'      => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Commission Rate (in %)', 'wk-marketplace' ),
							'description' => '',
							'value'       => $commission_info->commision_on_seller,
							'placeholder' => esc_html__( 'Enter a positive number upto 100.', 'wk-marketplace' ) . '...',
							'readonly'    => 'readonly',
							'show_lock'   => true,
						),
						'wkmp_total_sale'             => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Total Sale', 'wk-marketplace' ),
							'description' => '',
							'value'       => ( $commission_info->seller_total_ammount + $commission_info->admin_amount ) . esc_attr( $cur_symbol ),
							'readonly'    => 'readonly',
							'placeholder' => esc_html__( 'Total Sale', 'wk-marketplace' ) . '...',
						),
						'wkmp_total_admin_commission' => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Total Admin Commission', 'wk-marketplace' ),
							'description' => '',
							'value'       => $commission_info->admin_amount . esc_attr( $cur_symbol ),
							'readonly'    => 'readonly',
							'placeholder' => esc_html__( 'Total Admin Commission', 'wk-marketplace' ) . '...',
						),
						'wkmp_existing_commission'    => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Existing Commission (in %)', 'wk-marketplace' ),
							'description' => '',
							'value'       => $default_commission,
							'placeholder' => '',
						),
					),
				),
			);

			$form_fields  = apply_filters( 'wkmp_seller_commission_settings', $form_fields );
			$button_attrs = apply_filters( 'wkmp_seller_save_button_attributes', array( 'disabled' => true ) );
			?>
			<div class="wrap">
				<h1> <?php esc_html_e( 'Set Seller Commission', 'wk-marketplace' ); ?> </h1>
				<p></p>
				<hr>
				<form action='' method='post' class="form-table" name='commision-form'>
					<?php
					$this->form_helper->wkmp_form_field_builder( $form_fields );
					wp_nonce_field( 'wkmp-admin-seller-commission-nonce-action', 'wkmp-admin-seller-commission-nonce-value' );

					submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary', 'submit', false, $button_attrs );
					empty( $button_attrs['disabled'] ) ? '' : $template_functions->wkmp_show_upgrade_lock_icon();
					?>
				</form>
			</div>
			<?php
		}
	}
}
