<?php
/**
 * File handler class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Templates\Admin as AdminTemplates;
use WkMarketplace\Templates\Front as FrontTemplates;
use WkMarketplace\Includes\Front;
use WkMarketplace\Includes\Admin;
use WkMarketplace\Includes\Common;

if ( ! class_exists( 'WKMP_File_Handler' ) ) {

	/**
	 * File handler class
	 */
	class WKMP_File_Handler {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * File handler constructor
		 */
		public function __construct() {

			if ( is_admin() ) {
				Admin\WKMP_Admin_Hooks::get_instance();
				Admin\WKMP_Admin_Ajax_Hooks::get_instance();
				AdminTemplates\WKMP_Admin_Template_Hooks::get_instance();
			} else {
				Front\WKMP_Front_Hooks::get_instance();
				Front\WKMP_Front_Action_Hooks::get_instance();
				FrontTemplates\WKMP_Front_Template_Hooks::get_instance();

				$is_block_checkout = \WC_Blocks_Utils::has_block_in_page( get_option( 'woocommerce_checkout_page_id' ), 'woocommerce/checkout' );
				$is_block_cart     = \WC_Blocks_Utils::has_block_in_page( get_option( 'woocommerce_cart_page_id' ), 'woocommerce/cart' );

				// Block cart-checkout related hooks and functions.
				if ( ( $is_block_cart ) || ( $is_block_checkout ) ) {
					Front\WKMP_Front_Block_Hooks::get_instance();
				}
			}

			WKMP_Emails::get_instance();
			WKMP_Flat_Rate_Shipping::get_instance();
			WKMP_Free_Shipping::get_instance();
			WKMP_Local_Pickup_Shipping::get_instance();
			Front\WKMP_Front_Ajax_Hooks::get_instance();
			Common\WKMP_Common_Hooks::get_instance();
			Common\WKMP_Notification_Hooks::get_instance();
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
