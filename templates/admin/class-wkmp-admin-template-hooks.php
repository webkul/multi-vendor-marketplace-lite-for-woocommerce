<?php
/**
 * Admin template hooks.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Admin_Template_Hooks' ) ) {
	/**
	 * Admin template hooks
	 */
	class WKMP_Admin_Template_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			$template_functions = WKMP_Admin_Template_Functions::get_instance();
			add_action( 'admin_footer', array( $template_functions, 'wkmp_show_pro_upgrade_popup' ) );
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}
	}
}
