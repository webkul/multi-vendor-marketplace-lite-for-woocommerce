<?php
/**
 * File Handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Local_Pickup_Shipping' ) ) {
	/**
	 * Class WKMP_Local_Pickup_Shipping
	 *
	 * @package WkMarketplace\Includes
	 */
	class WKMP_Local_Pickup_Shipping {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Local_Pickup_Shipping constructor.
		 */
		public function __construct() {
			add_filter( 'woocommerce_shipping_methods', array( $this, 'wkmp_add_local_pickup_shipping_method' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'wkmp_local_pickup_shipping_method_init' ) );
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
		 * Adding Local pickup shipping method.
		 *
		 * @param array $methods Methods.
		 *
		 * @return mixed
		 */
		public function wkmp_add_local_pickup_shipping_method( $methods ) {
			$methods['mp_local_pickup'] = 'WKMP_Local_Pickup_Shipping_Method';
			return $methods;
		}

		/**
		 * Shipping method Init.
		 */
		public function wkmp_local_pickup_shipping_method_init() {
			require_once __DIR__ . '/shipping/mp-local-pickup/class-wkmp-local-pickup-shipping-method.php';
		}
	}
}
