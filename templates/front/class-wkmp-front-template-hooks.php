<?php
/**
 * Front template hooks.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Front_Template_Hooks' ) ) {
	/**
	 * Admin template hooks.
	 */
	class WKMP_Front_Template_Hooks {
		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			$function_handler = WKMP_Front_Template_Functions::get_instance();

			// Product by Feature on product page.
			add_action( 'woocommerce_single_product_summary', array( $function_handler, 'wkmp_product_by' ), 11 );

			// Customer Account menu.
			add_filter( 'query_vars', array( $function_handler, 'wkmp_add_query_vars' ) );
			add_filter( 'rewrite_rules_array', array( $function_handler, 'wkmp_insert_custom_rules' ) );
			add_filter( 'woocommerce_account_menu_items', array( $function_handler, 'wkmp_new_menu_items' ) );
			add_filter( 'the_title', array( $function_handler, 'wkmp_endpoint_title' ) );

			add_action( 'wp_footer', array( $function_handler, 'wkmp_front_footer_templates' ) );
			add_filter( 'woocommerce_get_item_data', array( $function_handler, 'wkmp_add_sold_by_cart_data' ), 10, 2 );
			add_action( 'woocommerce_product_meta_start', array( $function_handler, 'wkmp_add_seller_prefix_to_sku' ) );
			add_action( 'woocommerce_product_meta_end', array( $function_handler, 'wkmp_remove_seller_prefix_to_sku' ) );

			$favorite_seller = get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' );

			add_action( 'woocommerce_account_' . $favorite_seller . '_endpoint', array( $function_handler, 'wkmp_favorite_endpoint_content' ) );
			add_action( 'woocommerce_loop_add_to_cart_args', array( $function_handler, 'wkmp_add_soldby_on_archive_add_to_cart_button' ), 10, 3 );
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
}
