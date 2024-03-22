<?php
/**
 * Front hooks template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @since 1.0.3
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Front_Block_Hooks' ) ) {
	/**
	 * Front hooks class.
	 *
	 * Class WKMP_Front_Block_Hooks
	 *
	 * @package WkMarketplace\Includes\Front
	 */
	class WKMP_Front_Block_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * WKMP_Front_Block_Hooks Constructor.
		 */
		public function __construct() {
			$function_handler = WKMP_Front_Block_Functions::get_instance();

			add_action( 'wp_enqueue_scripts', array( $function_handler, 'wkmp_front_block_scripts' ) );

			add_action( 'woocommerce_store_api_checkout_order_processed', array( $function_handler, 'wkmp_new_order_map_seller_block_checkout' ) );
			add_action( 'woocommerce_store_api_cart_errors', array( $function_handler, 'wkmp_show_cart_notices' ), 10, 2 );
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
