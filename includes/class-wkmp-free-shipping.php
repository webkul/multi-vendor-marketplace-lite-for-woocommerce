<?php
/**
 * File Handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Free_Shipping' ) ) {
	/**
	 * Class WKMP_Free_Shipping
	 *
	 * @package WkMarketplace\Includes
	 */
	class WKMP_Free_Shipping {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Free_Shipping constructor.
		 */
		public function __construct() {
			add_filter( 'woocommerce_shipping_methods', array( $this, 'wkmp_add_free_shipping_method' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'wkmp_free_shipping_method_init' ) );
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

		/**
		 * Adding free shipping method.
		 *
		 * @param array $methods Methods.
		 *
		 * @return mixed
		 */
		public function wkmp_add_free_shipping_method( $methods ) {
			$methods['mp_free_shipping'] = 'WKMP_Free_Shipping_Method';
			return $methods;
		}

		/**
		 * Shipping method Init.
		 */
		public function wkmp_free_shipping_method_init() {
			require_once __DIR__ . '/shipping/mp-free-shipping/class-wkmp-free-shipping-method.php';
		}
	}
}
