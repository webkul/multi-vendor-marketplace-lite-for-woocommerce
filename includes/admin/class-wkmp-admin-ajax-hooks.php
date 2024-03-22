<?php
/**
 * Admin End Hooks.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Admin_Ajax_Hooks' ) ) {

	/**
	 * Admin hooks class.
	 *
	 * Class WKMP_Admin_Ajax_Hooks
	 *
	 * @package WkMarketplace\Includes\Admin
	 */
	class WKMP_Admin_Ajax_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Admin hooks constructor.
		 *
		 * WKMP_Admin_Ajax_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = WKMP_Admin_Ajax_Functions::get_instance();

			add_action( 'wp_ajax_wkmp_admin_replied_to_seller', array( $function_handler, 'wkmp_admin_replied_to_seller' ) );
			add_action( 'wp_ajax_wkmp_check_myshop', array( $function_handler, 'wkmp_check_slug_for_seller_shop' ) );
			add_action( 'wp_ajax_wkmp_change_seller_dashboard', array( $function_handler, 'wkmp_change_seller_to_frontend_dashboard' ) );
			add_action( 'wp_ajax_wkmp_update_seller_order_status', array( $function_handler, 'wkmp_seller_order_status_update' ) );
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
