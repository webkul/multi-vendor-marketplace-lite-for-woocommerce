<?php
/**
 * WC Dependency checker class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC Dependency Checker
 *
 * Checks if WooCommerce is enabled.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
class WKMP_WC_Dependencies {
	/**
	 * Private static Active plugins.
	 *
	 * @var array $active_plugins
	 */
	private static $active_plugins;

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
	 * Woocommerce active check.
	 *
	 * @return bool
	 */
	public static function woocommerce_install_check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();

		return isset( $installed_plugins['woocommerce/woocommerce.php'] );
	}

	/**
	 * Woocommerce active check.
	 *
	 * @return bool
	 */
	public static function woocommerce_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}
}
