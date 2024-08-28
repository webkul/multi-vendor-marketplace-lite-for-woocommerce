<?php
/**
 * Admin template Functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Settings;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper as Form;
use WkMarketplace\Templates\Admin as AdminTemplates;

if ( ! class_exists( 'WKMP_Setting_Templates_Handler' ) ) {

	/**
	 * Admin Settings Handler class
	 */
	class WKMP_Setting_Templates_Handler {
		/**
		 * Form field builder
		 *
		 * @var object
		 */
		protected $form_helper;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			$this->form_helper = Form\WKMP_Form_Field_Builder::get_instance();

			add_action( 'wkmp_general_settings_content', array( $this, 'wkmp_general_settings_content' ) );
			add_action( 'wkmp_product_settings_content', array( $this, 'wkmp_product_settings_content' ) );
			add_action( 'wkmp_assets_settings_content', array( $this, 'wkmp_assets_settings_content' ) );
			add_action( 'wkmp_endpoint_settings_content', array( $this, 'wkmp_endpoint_settings_content' ) );
			add_action( 'wkmp_google_analytics_settings_content', array( $this, 'wkmp_google_analytics_tab_content' ) );

			$this->wkmp_display_settings_tab();
		}

		/**
		 * Display All settings tabs
		 */
		public function wkmp_display_settings_tab() {
			$tabs = apply_filters(
				'wkmp_admin_setting_tabs',
				array(
					'general'          => esc_html__( 'General', 'wk-marketplace' ),
					'product'          => esc_html__( 'Product Options', 'wk-marketplace' ),
					'assets'           => esc_html__( 'Assets Visibility', 'wk-marketplace' ),
					'endpoint'         => esc_html__( 'Endpoints', 'wk-marketplace' ),
					'google_analytics' => esc_html__( 'Google Analytics & Map', 'wk-marketplace' ),
				)
			);

			$current_tab = \WK_Caching::wk_get_request_data( 'tab' );
			$current_tab = empty( $current_tab ) ? 'general' : $current_tab;
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
				<nav class="nav-tab-wrapper wkmp-admin-seller-list-manage-nav">
					<?php
					foreach ( $tabs as $name => $label ) {
						$tab_class = ( $current_tab === $name ) ? 'nav-tab-active' : '';
						echo wp_sprintf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=wk-marketplace-settings&tab=' . esc_attr( $name ) ) ), esc_attr( $tab_class ), esc_html( $label ) );
					}
					?>
				</nav>
				<?php do_action( 'wkmp_' . esc_attr( $current_tab ) . '_settings_content' ); ?>
			</div>
			<?php
		}

		/**
		 * General Settings
		 *
		 * @return void
		 */
		public function wkmp_general_settings_content() {
			global $wkmarketplace;
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			require __DIR__ . '/wkmp-general-settings-content.php';
		}

		/**
		 * Product Options
		 *
		 * @return void
		 */
		public function wkmp_product_settings_content() {
			require __DIR__ . '/wkmp-product-settings-content.php';
		}

		/**
		 * Assets Options
		 *
		 * @return void
		 */
		public function wkmp_assets_settings_content() {
			require __DIR__ . '/wkmp-assets-settings-content.php';
		}

		/**
		 * Endpoints Options
		 *
		 * @return void
		 */
		public function wkmp_endpoint_settings_content() {
			global $wkmarketplace;
			$wc_endpoints       = $wkmarketplace->wkmp_get_wc_registered_endpoints();
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			require __DIR__ . '/wkmp-endpoint-settings-content.php';
		}

		/**
		 * Google Analytics settings.
		 *
		 * @return void
		 */
		public function wkmp_google_analytics_tab_content() {
			global $wkmarketplace;
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			require __DIR__ . '/wkmp-google-analytics-settings-content.php';
		}
	}
}
