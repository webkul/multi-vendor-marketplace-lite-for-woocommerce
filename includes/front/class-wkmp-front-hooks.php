<?php
/**
 * Front hooks template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Templates\Front as FrontTemplates;
use WkMarketplace\Templates\Front\Seller;

if ( ! class_exists( 'WKMP_Front_Hooks' ) ) {
	/**
	 * Front hooks class.
	 *
	 * Class WKMP_Front_Hooks
	 *
	 * @package WkMarketplace\Includes\Front
	 */
	class WKMP_Front_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * WKMP_Front_Hooks Constructor.
		 */
		public function __construct() {
			$template_handler = FrontTemplates\WKMP_Front_Template_Functions::get_instance();
			$function_handler = new WKMP_Front_Functions( $template_handler );

			add_action( 'wp_enqueue_scripts', array( $function_handler, 'wkmp_front_scripts' ) );
			add_action( 'wp', array( $function_handler, 'wkmp_call_seller_pages' ) );

			add_action( 'init', array( $function_handler, 'wkmp_create_wc_seller_endpoints' ) );
			add_action( 'mp_get_wc_account_menu', array( $function_handler, 'wkmp_return_wc_account_menu' ) );
			add_action( 'wp_head', array( $function_handler, 'wkmp_add_dynamic_wc_endpoints_icons' ) );
			add_filter( 'woocommerce_account_menu_items', array( $function_handler, 'wkmp_seller_menu_items_my_account' ) );

			add_action( 'woocommerce_checkout_order_processed', array( $function_handler, 'wkmp_new_order_map_seller' ), 10, 3 );
			add_action( 'woocommerce_thankyou', array( $function_handler, 'wkmp_clear_shipping_session' ) );

			add_action( 'marketplace_after_shop_loop', array( $function_handler, 'wkmp_seller_collection_pagination' ) );
			add_action( 'marketplace_before_shop_loop', array( $function_handler, 'wkmp_seller_collection_pagination' ) );

			add_action( 'admin_init', array( $function_handler, 'wkmp_redirect_seller_tofront' ) );
			add_action( 'template_redirect', array( $function_handler, 'wkmp_redirect_seller_tofront' ) );
			add_filter( 'woocommerce_login_redirect', array( $function_handler, 'wkmp_seller_login_redirect' ), 10, 2 );
			add_action( 'woocommerce_account_navigation', array( $function_handler, 'wkmp_show_register_success_notice' ), 1 );

			// Adding sold item meta to order items.
			add_action( 'woocommerce_checkout_create_order_line_item', array( $function_handler, 'wkmp_add_sold_by_order_item_meta' ), 10, 4 );

			// Validating and showing notice on cart page when cart total is less than threshold amount.
			add_action( 'woocommerce_checkout_process', array( $function_handler, 'wkmp_validate_minimum_order_amount' ) );
			add_action( 'woocommerce_before_cart', array( $function_handler, 'wkmp_validate_minimum_order_amount' ) );

			// Showing the notice on checkout page when total volume less that threshold amount.
			add_action( 'woocommerce_checkout_update_order_review', array( $function_handler, 'wkmp_validate_minimum_order_amount_checkout' ) );

			// Handle seller registration.
			add_action( 'woocommerce_register_form', array( $function_handler, 'wkmp_show_seller_registration_fields' ) );
			add_filter( 'woocommerce_process_registration_errors', array( $function_handler, 'wkmp_seller_registration_errors' ) );
			add_filter( 'registration_errors', array( $function_handler, 'wkmp_seller_registration_errors' ) );
			add_filter( 'woocommerce_new_customer_data', array( $function_handler, 'wkmp_new_user_data' ) );
			add_action( 'woocommerce_created_customer', array( $function_handler, 'wkmp_process_registration' ), 10, 2 );

			// Replacing the Place order button when total volume less that threshold amount.
			add_filter( 'woocommerce_order_button_html', array( $function_handler, 'wkmp_remove_place_order_button' ), 10 );
			add_filter( 'body_class', array( $function_handler, 'wkmp_add_body_class' ) );
			add_filter( 'woocommerce_account_menu_item_classes', array( $function_handler, 'wkmp_wc_menu_active_class' ), 10, 2 );

			// All in one SEO compatibility.
			add_filter( 'aioseo_conflicting_shortcodes', array( $function_handler, 'wkmp_remove_mp_shortcode_from_aioseo_shortcode_lists' ) );

			$seller_id   = get_current_user_id();
			$seller_user = get_user_by( 'ID', $seller_id );

			if ( is_a( $seller_user, 'WP_User' ) && in_array( 'wk_marketplace_seller', $seller_user->roles, true ) ) {
				$seller_template = Seller\WKMP_Seller_Template_Functions::get_instance( $seller_id );
				$dashboard       = get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' );
				$product_list    = get_option( '_wkmp_product_list_endpoint', 'seller-products' );
				$add_product     = get_option( '_wkmp_add_product_endpoint', 'seller-add-product' );
				$edit_product    = get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' );
				$order_history   = get_option( '_wkmp_order_history_endpoint', 'sellers-orders' );
				$transactions    = get_option( '_wkmp_transaction_endpoint', 'seller-transactions' );
				$profile         = get_option( '_wkmp_profile_endpoint', 'seller-profile' );
				$notifications   = get_option( '_wkmp_notification_endpoint', 'seller-notifications' );
				$followers       = get_option( '_wkmp_shop_follower_endpoint', 'shop-followers' );
				$ask_admin       = get_option( '_wkmp_asktoadmin_endpoint', 'seller-ask-admin' );

				add_action( 'woocommerce_account_' . $dashboard . '_endpoint', array( $seller_template, 'wkmp_seller_dashboard_endpoint_content' ) );
				add_action( 'woocommerce_account_' . $product_list . '_endpoint', array( $seller_template, 'wkmp_product_list_endpoint_content' ) );
				add_action( 'woocommerce_account_' . $add_product . '_endpoint', array( $seller_template, 'wkmp_add_product_endpoint_content' ) );
				add_action( 'woocommerce_account_' . $edit_product . '_endpoint', array( $seller_template, 'wkmp_edit_product_endpoint_content' ) );
				add_action( 'woocommerce_account_' . $order_history . '_endpoint', array( $seller_template, 'wkmp_order_history_endpoint_content' ) );
				add_action( 'woocommerce_account_' . $transactions . '_endpoint', array( $seller_template, 'wkmp_transactions_content' ) );
				add_action( 'woocommerce_account_' . $profile . '_endpoint', array( $seller_template, 'wkmp_seller_profile_content' ) );
				add_action( 'woocommerce_account_' . $notifications . '_endpoint', array( $seller_template, 'wkmp_seller_notifications_content' ) );
				add_action( 'woocommerce_account_' . $followers . '_endpoint', array( $seller_template, 'wkmp_shop_followers_content' ) );
				add_action( 'woocommerce_account_' . $ask_admin . '_endpoint', array( $seller_template, 'wkmp_ask_admin_content' ) );
				add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' ); // Avoid updating customer data from other fields when seller placing order as a customer.
			}
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
