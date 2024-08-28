<?php
/**
 * Front hooks template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Common_Hooks' ) ) {
	/**
	 * Front hooks clasṣ
	 *
	 * Class WKMP_Common_Hooks
	 *
	 * @package WkMarketplace\Includes\Common
	 */
	class WKMP_Common_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Common_Hooks constructor.
		 */
		public function __construct() {
			$function_handler = WKMP_Common_Functions::get_instance();

			add_action( 'woocommerce_shipping_zone_method_added', array( $function_handler, 'wkmp_after_add_admin_shipping_zone' ), 10, 3 );
			add_action( 'woocommerce_delete_shipping_zone', array( $function_handler, 'wkmp_action_woocommerce_delete_shipping_zone' ) );
			add_action( 'woocommerce_shipping_classes_save_class', array( $function_handler, 'wkmp_after_add_admin_shipping_class' ), 10, 2 );

			add_action( 'woocommerce_order_status_cancelled', array( $function_handler, 'wkmp_action_on_order_cancel' ) );
			add_action( 'woocommerce_order_status_failed', array( $function_handler, 'wkmp_action_on_order_changed_mails' ) );
			add_action( 'woocommerce_order_status_on-hold', array( $function_handler, 'wkmp_action_on_order_changed_mails' ) );
			add_action( 'woocommerce_order_status_processing', array( $function_handler, 'wkmp_action_on_order_changed_mails' ) );
			add_action( 'woocommerce_order_status_completed', array( $function_handler, 'wkmp_action_on_order_changed_mails' ) );
			add_action( 'woocommerce_order_status_refunded', array( $function_handler, 'wkmp_action_on_order_changed_mails' ) );
			add_action( 'woocommerce_order_status_refunded', array( $function_handler, 'wkmp_add_seller_refund_data_on_order_fully_refunded' ) );
			add_action( 'woocommerce_refund_created', array( $function_handler, 'wkmp_add_seller_refund_data_on_order_refund' ), 10, 2 );

			add_action( 'template_redirect', array( $function_handler, 'wkmp_reset_previous_chosen_shipping_method' ), 1 );

			add_action( 'draft_to_publish', array( $function_handler, 'wkmp_action_on_product_approve' ) );
			add_action( 'wp_trash_post', array( $function_handler, 'wkmp_action_on_product_disapprove' ) );
			add_action( 'save_post', array( $function_handler, 'wkmp_save_product_seller_and_qty' ) );

			add_action( 'personal_options_update', array( $function_handler, 'wkmp_save_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $function_handler, 'wkmp_save_extra_user_profile_fields' ) );
			add_action( 'user_profile_update_errors', array( $function_handler, 'wkmp_validate_extra_profile_fields' ), 10, 3 );
			add_action( 'admin_bar_menu', array( $function_handler, 'wkmp_add_toolbar_items' ), 100 );
			add_action( 'ajax_query_attachments_args', array( $function_handler, 'wkmp_restrict_media_library' ) );
			add_action( 'woocommerce_init', array( $function_handler, 'wkmp_add_manage_shipping' ) );
			add_action( 'wkmp_validate_update_seller_profile', array( $function_handler, 'wkmp_process_seller_profile_data' ), 10, 2 );
			add_action( 'woocommerce_order_status_completed', array( $function_handler, 'wkmp_reset_seller_order_count_cache' ) );
			add_action( 'admin_footer', array( $function_handler, 'wkmp_add_seller_dashboard_dynamic_style' ) );
			add_action( 'wp_footer', array( $function_handler, 'wkmp_add_seller_dashboard_dynamic_style' ) );

			add_filter( 'woocommerce_order_item_display_meta_value', array( $function_handler, 'wkmp_validate_sold_by_order_item_meta' ), 10, 2 );
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
