<?php
/**
 * Plugin Name: Multi-Vendor Marketplace Lite for WooCommerce
 * Plugin URI: https://store.webkul.com/woocommerce-plugins.html?limit=48
 * Description: This plugin converts the WooCommerce store into multi-vendor store. Using this plugin, the seller can manage the inventory, shipment, seller profile page, seller collection page and much more.
 * Version: 1.2.0
 * Author: Webkul
 * Author URI: https://webkul.com
 * Text Domain: wk-marketplace
 * Domain Path: /languages
 *
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Tested up to PHP: 8.3
 * WC requires at least: 8.0
 * WC tested up to: 9.2
 *
 * Blog URI: https://webkul.com/blog/marketplace-for-woocommerce-lite/
 *
 * Requires Plugins: woocommerce
 *
 * WPML Compatible: No
 * Multisite Compatible: yes
 *
 * Marketplace Lite for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Marketplace Lite for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Rollback. If not, see <http://www.gnu.org/licenses/>.
 *
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WkMarketplace\Includes;

// Define Constants.
defined( 'WKMP_LITE_FILE' ) || define( 'WKMP_LITE_FILE', __FILE__ );
defined( 'WKMP_LITE_PLUGIN_FILE' ) || define( 'WKMP_LITE_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
defined( 'WKMP_LITE_PLUGIN_URL' ) || define( 'WKMP_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'WKMP_LITE_SCRIPT_VERSION' ) || define( 'WKMP_LITE_SCRIPT_VERSION', '1.2.0' );
defined( 'WKMP_LITE_VERSION' ) || define( 'WKMP_LITE_VERSION', '1.2.0' );
defined( 'WKMP_LITE_DB_VERSION' ) || define( 'WKMP_LITE_DB_VERSION', '5.3.8' );
defined( 'WKMP_LITE_PLUGIN_BASENAME' ) || define( 'WKMP_LITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
defined( 'WKMP_LITE_WK_CACHING_VERSION' ) || define( 'WKMP_LITE_WK_CACHING_VERSION', '1.0.9' );
defined( 'WKMP_PRO_MIN_VERSION' ) || define( 'WKMP_PRO_MIN_VERSION', '5.6.0' );
defined( 'WKMP_DB_VERSION' ) || define( 'WKMP_DB_VERSION', '5.3.8' );
defined( 'WKMP_PRO_MODULE_URL' ) || define( 'WKMP_PRO_MODULE_URL', 'https://store.webkul.com/woocommerce-multivendor-marketplace.html' );
defined( 'WKMP_PRO_DEMO_URL' ) || define( 'WKMP_PRO_DEMO_URL', 'https://wpdemo.webkul.com/woocommerce-marketplace/' );

require_once __DIR__ . '/inc/class-wkmp-autoload.php';
require __DIR__ . '/vendor/autoload.php';

if ( ! function_exists( 'wkmp_wc_log' ) ) {
	/**
	 * Adding log for debugging.
	 *
	 * @param mixed  $message Message string or array.
	 * @param array  $context Additional parameter, like file name 'source'.
	 * @param string $level One of the following:
	 *     'emergency': System is unusable.
	 *     'alert': Action must be taken immediately.
	 *     'critical': Critical conditions.
	 *     'error': Error conditions.
	 *     'warning': Warning conditions.
	 *     'notice': Normal but significant condition.
	 *     'info': Informational messages.
	 *     'debug': Debug-level messages.
	 *
	 * @return void
	 */
	function wkmp_wc_log( $message, $context = array(), $level = 'info' ) {
		/** Allow to disable the log.
		 *
		 * @since 5.0.0
		*/
		$log_enabled = apply_filters( 'wkmp_is_log_enabled', true );

		if ( function_exists( 'wc_get_logger' ) && $log_enabled ) {
			$source            = ( is_array( $context ) && ! empty( $context['source'] ) ) ? $context['source'] : 'wk-mp';
			$context['source'] = $source;
			$logger            = wc_get_logger();
			$current_user_id   = get_current_user_id();

			$in_action = sprintf( ( /* translators: %s current user id */ esc_html__( 'User in action: %s: ', 'wk-marketplace' ) ), $current_user_id );
			$message   = $in_action . $message;

			$logger->log( $level, $message, $context );
		}
	}
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_multi_vendor_marketplace_lite_for_woocommerce() {
	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new Appsero\Client( '0bbe8076-4f61-40c2-b7bb-6cd17e2f7e5a', 'Multi-Vendor Marketplace Lite for WooCommerce &#8211; Create Your Amazing B2C Multi-Vendor Marketplace Intuitively', __FILE__ );

	// Active insights.
	$client->insights()->init();
}

appsero_init_tracker_multi_vendor_marketplace_lite_for_woocommerce();

add_filter(
	'appsero_custom_deactivation_reasons',
	function () {
		return array(
			array(
				'id'          => 'pro-plugin-is-costly',
				'text'        => esc_html__( 'Pro Plugin is Costly', 'wk-marketplace' ),
				'placeholder' => esc_html__( 'Kindly contact Webkul support (support@webkul.com) to check if there is a discount coupon available for you.', 'wk-marketplace' ),
				'icon'        => '<img width="32px" height="32px" src="https://store.webkul.com/media/catalog/product/cache/1/small_image/9df78eab33525d08d6e5fb8d27136e95/s/h/shopify-help-desk-app-uvdesk-thumbnail-1200x1200.png" />',
			),
		);
	}
);

// For global availability object.
$GLOBALS['wkmarketplace'] = Includes\WKMarketplace::instance();
