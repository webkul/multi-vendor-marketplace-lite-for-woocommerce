<?php
/**
 * Front Block functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @since 1.0.3
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Front_Block_Functions' ) ) {
	/**
	 * Front hooks class.
	 *
	 * Class WKMP_Front_Block_Functions
	 *
	 * @package WkMarketplace\Includes\Front
	 */
	class WKMP_Front_Block_Functions extends WKMP_Front_Functions {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * WKMP_Front_Block_Functions Constructor.
		 */
		public function __construct() {
		}

		/**
		 * Front block scripts.
		 *
		 * @return void
		 */
		public function wkmp_front_block_scripts() {
			if ( is_cart() || is_checkout() ) {
				$suffix     = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? '' : '.min';
				$asset_path = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? 'build' : 'dist';
				wp_enqueue_script( 'wkmp-front-block-script', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/front/js/front-block' . $suffix . '.js', array( 'wp-util' ), WKMP_LITE_SCRIPT_VERSION, true );
			}
		}

		/**
		 * Map seller for new order on block based checkout (Store API).
		 *
		 * @param object $order WC_Order Object.
		 *
		 * @hooked 'woocommerce_store_api_checkout_order_processed' action hook.
		 *
		 * @return void
		 */
		public function wkmp_new_order_map_seller_block_checkout( $order ) {
			$order_id = 0;

			if ( $order instanceof \WC_Order ) {
				$order_id = $order->get_id();
				$this->wkmp_new_order_map_seller( $order_id, array(), $order );
			}
			wkmp_wc_log( "Block based Checkout order processed for order id: $order_id" );
		}

		/**
		 * Showing notices on cart validation for cart total and qty limitations.
		 *
		 * @param object $errors WP_Error class object.
		 * @param object $cart Store Cart object.
		 *
		 * @return void
		 */
		public function wkmp_show_cart_notices( $errors, $cart ) {
			$err_msgs = $this->wkmp_get_cart_validation_error_messages( $cart );

			foreach ( $err_msgs as $msg_code => $msg ) {
				$errors->add( 'wkmp_error_' . $msg_code, $msg );
			}
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
