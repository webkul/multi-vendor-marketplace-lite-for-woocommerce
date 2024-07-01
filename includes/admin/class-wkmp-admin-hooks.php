<?php
/**
 * Admin End Hooks.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WkMarketplace\Templates\Admin as AdminTemplates;

if ( ! class_exists( 'WKMP_Admin_Hooks' ) ) {

	/**
	 * Admin hooks class
	 */
	class WKMP_Admin_Hooks {
		/**
		 * Seller DB variable
		 *
		 * @var object
		 */
		protected $seller_obj;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Admin hooks constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			$template_handler = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$handler          = new WKMP_Admin_Functions( $template_handler );

			add_action( 'admin_init', array( $handler, 'wkmp_register_marketplace_options' ) );
			add_action( 'admin_init', array( $handler, 'wkmp_prevent_seller_admin_access' ) );
			add_action( 'admin_notices', array( $handler, 'wkmp_show_notice_on_seller_paid_by_admin' ) );
			add_action( 'admin_menu', array( $handler, 'wkmp_create_dashboard_menu' ), 9 );
			add_action( 'admin_menu', array( $handler, 'wkmp_create_submenu_menu' ), 999 );

			add_action( 'admin_enqueue_scripts', array( $handler, 'wkmp_admin_scripts' ) );
			add_action( 'admin_menu', array( $handler, 'wkmp_virtual_menu_invoice_page' ) );
			add_action( 'woocommerce_admin_order_actions_end', array( $handler, 'wkmp_order_invoice_button' ) );

			add_action( 'show_user_profile', array( $handler, 'wkmp_extra_user_profile_fields' ) );
			add_action( 'edit_user_profile', array( $handler, 'wkmp_extra_user_profile_fields' ) );
			add_action( 'user_new_form', array( $handler, 'wkmp_extra_user_profile_fields' ) );

			add_action( 'add_meta_boxes', array( $handler, 'wkmp_add_seller_metabox' ) );
			add_action( 'woocommerce_order_status_changed', array( $handler, 'wkmp_order_status_changed_action' ), 10, 3 );
			add_action( 'deleted_user', array( $handler, 'wkmp_delete_seller_on_user_delete' ), 10, 3 );
			add_action( 'woocommerce_product_options_inventory_product_data', array( $handler, 'wkmp_add_max_qty_field' ) );
			add_action( 'woocommerce_new_order', array( $handler, 'wkmp_update_seller_order_mapping' ) );
			add_action( 'woocommerce_new_order_item', array( $handler, 'wkmp_update_soldby_to_admin_order' ), 10, 3 );
			add_action( 'admin_notices', array( $handler, 'wkmp_maybe_show_notices_on_admin' ) );

			add_filter( 'set-screen-option', array( $handler, 'wkmp_set_screen' ), 10, 3 );
			add_filter( 'woocommerce_screen_ids', array( $handler, 'wkmp_set_wc_screen_ids' ) );
			add_filter( 'admin_footer_text', array( $handler, 'wkmp_admin_footer_text' ), 99 );
			add_filter( 'get_terms_args', array( $handler, 'wkmp_remove_sellers_shipping_classes' ), 10, 2 );
			add_filter( 'woocommerce_products_admin_list_table_filters', array( $handler, 'wkmp_remove_restricted_cats' ) );
			add_filter( 'plugin_action_links_' . WKMP_LITE_PLUGIN_BASENAME, array( $handler, 'wkmp_add_plugin_setting_links' ) );
			add_filter( 'plugin_row_meta', array( $handler, 'wkmp_plugin_show_row_meta' ), 10, 2 );
			add_filter( 'comments_list_table_query_args', array( $handler, 'wkmp_hide_other_comments_on_seller_dashboard' ) );
			add_filter( 'editable_roles', array( $handler, 'wkmp_remove_seller_from_change_role_to' ) );
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
