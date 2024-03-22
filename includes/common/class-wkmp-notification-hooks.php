<?php
/**
 * Front hooks template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Notification_Hooks' ) ) {
	/**
	 * Class WKMP_Notification_Hooks
	 *
	 * @package WkMarketplace\Includes\Common
	 */
	class WKMP_Notification_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Notification_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = WKMP_Notification_Functions::get_instance();
			add_action( 'woocommerce_checkout_order_processed', array( $function_handler, 'wkmp_send_email_on_order_processed' ), 999, 1 );
			add_action( 'transition_post_status', array( $function_handler, 'wkmp_save_on_product_update' ), 10, 3 );
			add_action( 'wkmp_save_seller_review_notification', array( $function_handler, 'wkmp_after_seller_review_saved' ), 10, 2 );
			add_action( 'woocommerce_low_stock_notification', array( $function_handler, 'wkmp_low_stock' ) );
			add_action( 'woocommerce_no_stock_notification', array( $function_handler, 'wkmp_no_stock' ) );
			add_action( 'woocommerce_order_status_processing', array( $function_handler, 'wkmp_order_processing_notification' ) );
			add_action( 'woocommerce_order_status_completed', array( $function_handler, 'wkmp_order_completed_notification' ) );

			// Block based hooks.
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $function_handler, 'wkmp_send_email_on_block_based_order_processed' ), 999, 1 );
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
