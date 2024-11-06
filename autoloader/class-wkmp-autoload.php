<?php
/**
 * Dynamically loads classes.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WKMP_Autoload' ) ) {
	/**
	 * WKMP_Autoload class
	 */
	class WKMP_Autoload {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Autoload constructor.
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload_entities' ) );
		}

		/**
		 * Autoload classes, traits and other entities.
		 *
		 * @param string $class_name The name of the class to load.
		 */
		public function autoload_entities( $class_name ) {
			if ( 0 !== strpos( $class_name, 'WkMarketplace' ) ) {
				return;
			}

			$file_parts = explode( '\\', $class_name );
			$namespace  = '';
			$filepath   = '';

			for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
				$current = strtolower( $file_parts[ $i ] );
				$current = str_ireplace( '_', '-', $current );

				if ( count( $file_parts ) - 1 === $i ) {
					if ( strpos( strtolower( $file_parts[ count( $file_parts ) - 1 ] ), 'interface' ) ) {
						$interface_name = explode( '_', $file_parts[ count( $file_parts ) - 1 ] );
						array_pop( $interface_name );
						$interface_name = strtolower( implode( '-', $interface_name ) );
						$file_name      = "interface-{$interface_name}.php";
					} else {
						$file_name = "class-{$current}.php";
					}
				} else {
					$namespace = '/' . esc_attr( $current ) . esc_attr( $namespace );
				}

				$filepath  = trailingslashit( dirname( __DIR__ ) . esc_attr( $namespace ) );
				$filepath .= $file_name;
			}

			// If the file exists in the specified path, then include it.
			if ( file_exists( $filepath ) ) {
				require_once $filepath;
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
	WKMP_Autoload::get_instance();
}
