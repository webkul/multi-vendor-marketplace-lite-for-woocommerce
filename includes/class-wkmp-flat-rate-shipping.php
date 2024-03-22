<?php
/**
 * File Handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Flat_Rate_Shipping' ) ) {
	/**
	 * Class WKMP_Flat_Rate_Shipping
	 *
	 * @package WkMarketplace\Includes
	 */
	class WKMP_Flat_Rate_Shipping {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Flat_Rate_Shipping constructor.
		 */
		public function __construct() {
			add_filter( 'woocommerce_shipping_methods', array( $this, 'wkmp_add_flat_rate_shipping_method' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'wkmp_flat_rate_shipping_method_init' ) );
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
		 * Adding flat rate shipping method.
		 *
		 * @param array $methods Methods.
		 *
		 * @return mixed
		 */
		public function wkmp_add_flat_rate_shipping_method( $methods ) {
			// Disabling default woocommerce shipping methods.
			if ( isset( $methods['flat_rate'] ) ) {
				unset( $methods['flat_rate'] );
			}

			if ( isset( $methods['free_shipping'] ) ) {
				unset( $methods['free_shipping'] );
			}

			if ( isset( $methods['local_pickup'] ) ) {
				unset( $methods['local_pickup'] );
			}

			$methods['mp_flat_rate'] = 'WKMP_Flat_Rate_Shipping_Method';

			return $methods;
		}

		/**
		 * Shipping method Init.
		 */
		public function wkmp_flat_rate_shipping_method_init() {
			require_once __DIR__ . '/shipping/mp-flat-rate/class-wkmp-flat-rate-shipping-method.php';
		}
	}
}
