<?php
/**
 * WooCommerce dependency checker function.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Functions used by plugins.
 */
if ( ! class_exists( 'WKMP_WC_Dependencies' ) ) {
	require_once __DIR__ . '/class-wkmp-wc-dependencies.php';
}

/**
 * WC Detection.
 */
if ( ! function_exists( 'wkmp_is_woocommerce_installed' ) ) {
	/**
	 * Checking if woocommere is active.
	 *
	 * @return bool
	 */
	function wkmp_is_woocommerce_installed() {
		return WKMP_WC_Dependencies::woocommerce_install_check();
	}
}

/**
 * WC Detection.
 */
if ( ! function_exists( 'wkmp_is_woocommerce_active' ) ) {
	/**
	 * Checking if woocommere is active.
	 *
	 * @return bool
	 */
	function wkmp_is_woocommerce_active() {
		return WKMP_WC_Dependencies::woocommerce_active_check();
	}
}
