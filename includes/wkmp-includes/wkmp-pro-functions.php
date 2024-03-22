<?php
/**
 * MP Pro dependency checker function.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Functions used by plugins.
 */
if ( ! class_exists( 'WKMP_Pro_Dependencies' ) ) {
	require_once __DIR__ . '/class-wkmp-pro-dependencies.php';
}

/**
 * MP Pro install detection Detection.
 */
if ( ! function_exists( 'wkmp_is_pro_installed' ) ) {
	/**
	 * Checking if MP Lite is installed.
	 *
	 * @return bool
	 */
	function wkmp_is_pro_installed() {
		return WKMP_Pro_Dependencies::wkmp_pro_install_check();
	}
}

/**
 * MP Pro active Detection.
 */
if ( ! function_exists( 'wkmp_is_pro_active' ) ) {
	/**
	 * Checking if MP Pro is active.
	 *
	 * @return bool
	 */
	function wkmp_is_pro_active() {
		return WKMP_Pro_Dependencies::wkmp_pro_active_check();
	}
}

/**
 * MP Pro min required version detection.
 */
if ( ! function_exists( 'wkmp_is_min_pro_version_installed' ) ) {
	/**
	 * Checking if minimum required MP Pro version is installed.
	 *
	 * @return bool
	 */
	function wkmp_is_min_pro_version_installed() {
		return WKMP_Pro_Dependencies::wkmp_pro_min_version_check();
	}
}
