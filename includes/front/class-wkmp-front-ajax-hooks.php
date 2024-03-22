<?php
/**
 * Front ajax hooks.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Front_Ajax_Hooks' ) ) {
	/**
	 * Front ajax hooks
	 */
	class WKMP_Front_Ajax_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			$function_handler = WKMP_Front_Ajax_Functions::get_instance();

			add_action( 'wp_ajax_nopriv_wkmp_check_shop_url', array( $function_handler, 'wkmp_check_for_shop_url' ) );
			add_action( 'wp_ajax_wkmp_check_shop_url', array( $function_handler, 'wkmp_check_for_shop_url' ) );

			add_action( 'wp_ajax_wkmp_add_favourite_seller', array( $function_handler, 'wkmp_update_favourite_seller' ) );
			add_action( 'wp_ajax_wkmp_get_state_by_country_code', array( $function_handler, 'wkmp_get_seller_state_by_country_code' ) );

			add_action( 'wp_ajax_wkmp_marketplace_attributes_variation', array( $function_handler, 'wkmp_marketplace_add_variation_attribute' ) );
			add_action( 'wp_ajax_wkmp_attributes_variation_remove', array( $function_handler, 'wkmp_attributes_remove_variation' ) );
			add_action( 'wp_ajax_wkmp_productgallary_image_delete', array( $function_handler, 'wkmp_productgallary_image_delete' ) );
			add_action( 'wp_ajax_wkmp_downloadable_file_add', array( $function_handler, 'wkmp_downloadable_file_add' ) );
			add_action( 'wp_ajax_wkmp_product_sku_validation', array( $function_handler, 'wkmp_validate_seller_product_sku' ) );

			add_action( 'wp_ajax_wkmp_change_frontend_seller_dashboard', array( $function_handler, 'wkmp_change_dashboard_to_backend_seller' ) );
			add_action( 'wp_ajax_wkmp_delete_seller_product', array( $function_handler, 'wkmp_delete_seller_selected_product' ) );
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
