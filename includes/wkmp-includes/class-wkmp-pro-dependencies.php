<?php
/**
 * MP Pro Dependency checker class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * MP Pro Dependency Checker
 *
 * Checks if MP Pro is enabled.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
class WKMP_Pro_Dependencies {
	/**
	 * Private static Active plugins.
	 *
	 * @var array $active_plugins
	 */
	private static $active_plugins;

	/**
	 * Private static Active plugins.
	 *
	 * @var array $pro_basename
	 */
	private static $pro_basename = 'wk-woocommerce-marketplace/wk-woocommerce-marketplace.php';

	/**
	 * Init function.
	 *
	 * @return void
	 */
	public static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	/**
	 * MP Lite active check.
	 *
	 * @return bool
	 */
	public static function wkmp_pro_install_check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();

		return isset( $installed_plugins[ self::$pro_basename ] );
	}

	/**
	 * MP Lite active check.
	 *
	 * @return bool
	 */
	public static function wkmp_pro_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( ! self::wkmp_pro_install_check() ) {
			return false;
		}

		return in_array( self::$pro_basename, self::$active_plugins, true ) || array_key_exists( self::$pro_basename, self::$active_plugins );
	}

	/**
	 * MP Lite Minimum required version check.
	 *
	 * @return bool
	 */
	public static function wkmp_pro_min_version_check() {
		if ( ! defined( 'MARKETPLACE_VERSION' ) ) {
			return false;
		}

		if ( ! version_compare( MARKETPLACE_VERSION, WKMP_PRO_MIN_VERSION, '>=' ) ) {
			return false;
		}

		return true;
	}
}
