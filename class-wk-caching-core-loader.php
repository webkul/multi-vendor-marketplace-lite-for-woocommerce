<?php
/**
 * WK Caching Core loader class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * This file is to initiate core and to run some common methods and decide which WK_Caching_Loader core should run.
 */
if ( ! class_exists( 'WK_Caching_Core_Loader' ) ) {
	/**
	 * WK_Caching_Core_Loader class.
	 */
	class WK_Caching_Core_Loader {
		/**
		 * Plugins array.
		 *
		 * @var @plugins
		 */
		public static $plugins = array();

		/**
		 * Loaded.
		 *
		 * @var $loaded.
		 */
		public static $loaded = false;

		/**
		 * Version
		 *
		 * @var $version.
		 */
		public static $version = null;

		/**
		 * Include core.
		 *
		 * @return void
		 */
		public static function include_core() {
			$get_configuration = self::get_the_latest();

			if ( false === self::$loaded && $get_configuration && is_array( $get_configuration ) && isset( $get_configuration['class'] ) ) {
				if ( is_callable( array( $get_configuration['class'], 'load_files' ) ) ) {
					self::$version = $get_configuration['version'];
					self::$loaded  = true;
					call_user_func( array( $get_configuration['class'], 'load_files' ) );
				}
			}
		}

		/**
		 * Register.
		 *
		 * @param array $configuration Configuration.
		 *
		 * @return void
		 */
		public static function register( $configuration ) {
			array_push( self::$plugins, $configuration );
		}

		/**
		 * Get the latest.
		 *
		 * @return array
		 */
		public static function get_the_latest() {
			$get_all = self::$plugins;
			uasort(
				$get_all,
				function ( $a, $b ) {
					if ( version_compare( $a['version'], $b['version'], '=' ) ) {
						return 0;
					}
					return ( version_compare( $a['version'], $b['version'], '<' ) ) ? - 1 : 1;
				}
			);

			$get_most_recent_configuration = end( $get_all );

			return $get_most_recent_configuration;
		}
	}
}


if ( ! class_exists( 'WKMP_WK_Caching_Core' ) ) {
	/**
	 * WKMP_WK_Caching_Core
	 */
	class WKMP_WK_Caching_Core {
		/**
		 * WK Caching Loading Version.
		 *
		 * @var $version
		 */
		public static $version = WKMP_LITE_WK_CACHING_VERSION;

		/**
		 * Register.
		 *
		 * @return void
		 */
		public static function register() {
			$configuration = array(
				'basename'    => WKMP_LITE_PLUGIN_BASENAME,
				'version'     => self::$version,
				'plugin_path' => WKMP_LITE_PLUGIN_FILE,
				'class'       => __CLASS__,
			);
			WK_Caching_Core_Loader::register( $configuration );
		}

		/**
		 * Load files.
		 *
		 * @return bool
		 */
		public static function load_files() {
			$get_global_path = WKMP_LITE_PLUGIN_FILE . 'wk_caching/';

			if ( false === file_exists( $get_global_path . '/includes/class-wk-caching.php' ) ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'WK Caching Core should be present in folder \'wk_caching\includes\' in order to run this properly.', 'wk-marketplace' ), esc_html( self::$version ) );
				if ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) {
					die( 0 );
				}
				return false;
			}

			/**
			 * Loading Core caching Files.
			 */
			require_once $get_global_path . 'includes/class-wk-caching.php';

			if ( WK_CACHING_VERSION === self::$version ) {
				do_action( 'wk_caching_loaded', $get_global_path );
			} else {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'WK Caching Core should be at the same version as declared in your "class-wk-caching-core-loader.php"', 'wk-marketplace' ), esc_html( self::$version ) );
				if ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) {
					die( 0 );
				}
			}
		}
	}
	WKMP_WK_Caching_Core::register();
}
