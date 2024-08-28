<?php
/**
 * Main Class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

use stdClass;
use WkMarketplace\Includes;
use WkMarketplacePro\Includes as ProIncludes;
use WkMarketplace\Helper;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMarketplace' ) ) {
	if ( ! class_exists( 'WKMarketplace_Pro_Globals' ) && file_exists( dirname( WKMP_LITE_PLUGIN_FILE ) . '/wk-woocommerce-marketplace/includes/class-wkmarketplace-pro-globals.php' ) ) {
		require_once dirname( WKMP_LITE_PLUGIN_FILE ) . '/wk-woocommerce-marketplace/includes/class-wkmarketplace-pro-globals.php';
		/**
		 * WKMP_Pro_Global_Helper class.
		 */
		class WKMP_Pro_Global_Helper extends ProIncludes\WKMarketplace_Pro_Globals {
		}
	} else {
		/**
		 * WKMP_Pro_Global_Helper class.
		 */
		class WKMP_Pro_Global_Helper {
		}
	}

	/**
	 * Marketplace Main Class.
	 */
	final class WKMarketplace extends WKMP_Pro_Global_Helper {
		/**
		 * Marketplace version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Instance variable.
		 *
		 * @var object
		 */
		protected static $wk = null;

		/**
		 * Seller page slug
		 *
		 * @var string
		 */
		public $seller_page_slug;

		/**
		 * Seller url
		 *
		 * @var string
		 */
		public $seller_url;

		/**
		 * Seller Endpoints.
		 *
		 * @var array
		 */
		public $endpoints = array();

		/**
		 * WC Endpoints.
		 *
		 * @var array
		 */
		public $wc_endpoints = array();

		/**
		 * General query helper.
		 *
		 * @var object
		 */
		protected $general_query;

		/**
		 * Dependency check property.
		 *
		 * @var bool Dependency check property
		 */
		private $is_dependency_exists = true;

		/**
		 * Dependency min required pro module.
		 *
		 * @var bool Dependency check property
		 */
		private $is_min_pro_exists = true;

		/**
		 * The object is created from within the class itself only if the class has no instance.
		 *
		 * @return object|WKMarketplace|null
		 */
		public static function instance() {
			if ( is_null( self::$wk ) ) {
				self::$wk = new self();
			}

			return self::$wk;
		}

		/**
		 * Marketplace Constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'wkmp_lite_load_plugin_text_domain' ), 0 );
			/**
			 * Load dependency classes like woo-functions.php
			 */
			$this->load_dependencies_support();

			/**
			 * Run dependency check to check if dependency available
			 */
			$this->do_dependency_check();

			if ( ! $this->is_dependency_exists ) {
				add_action( 'admin_notices', array( $this, 'wkmp_show_wc_not_installed_notice' ) );
			} else {
				$this->wkmp_init_hooks();
				$this->general_query    = Helper\WKMP_General_Queries::get_instance();
				$this->seller_page_slug = $this->general_query->wkmp_get_seller_page_slug();
			}
		}

		/**
		 * Loading Marketplace text domain.
		 *
		 * @return void
		 */
		public function wkmp_lite_load_plugin_text_domain() {
			load_plugin_textdomain( 'wk-marketplace', false, plugin_basename( dirname( WKMP_LITE_FILE ) ) . '/languages' );
		}

		/**
		 * Load dependencies support.
		 *
		 * @return void
		 */
		public function load_dependencies_support() {
			/** Setting up WooCommerce Dependency Classes */
			require_once __DIR__ . '/woo-includes/woo-functions.php';
			require_once __DIR__ . '/wkmp-includes/wkmp-pro-functions.php';
		}

		/**
		 * Do dependency check.
		 *
		 * @return void
		 */
		public function do_dependency_check() {
			if ( ! wkmp_is_woocommerce_active() ) {
				$this->is_dependency_exists = false;
			}
		}

		/**
		 * Do minimum required pro dependency check.
		 *
		 * @return void
		 */
		public function do_min_pro_check() {
			if ( wkmp_is_pro_active() && ! wkmp_is_min_pro_version_installed() ) {
				$this->is_min_pro_exists = false;
			}
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @return void|bool
		 */
		public function wkmp_init_hooks() {
			$schema_handler = WKMP_Install::get_instance();
			register_activation_hook( WKMP_LITE_PLUGIN_FILE, array( $schema_handler, 'wkmp_create_schema' ) );

			add_action( 'plugins_loaded', array( $this, 'wkmp_load_plugin' ) );
			add_action( 'wp_login', array( $this, 'wkmp_seller_login' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'wkmp_maybe_create_missing_tables' ), 990 );
			add_action( 'admin_menu', array( $this, 'wkmp_remove_home_menu_wc_admin' ), 990 );

			require_once WKMP_LITE_PLUGIN_FILE . '/class-wk-caching-core-loader.php';

			add_action( 'plugins_loaded', array( 'WK_Caching_Core_Loader', 'include_core' ), - 1 );

			self::wkmp_declare_hpos_compatibility_status( WKMP_LITE_FILE, true );
			self::wkmp_declare_cart_checkout_block_compatibility_status( WKMP_LITE_FILE, true );
		}

		/**
		 * Load plugin.
		 *
		 * @return void|bool
		 */
		public function wkmp_load_plugin() {
			/**
			 * Run dependency check to check if pro installed but not minimum supported.
			 */
			$this->do_min_pro_check();

			if ( ! $this->is_min_pro_exists ) {
				add_action( 'admin_notices', array( $this, 'wkmp_show_min_pro_not_installed_notice' ) );
			}

			$this->endpoints = apply_filters(
				'wkmp_seller_wc_endpoints',
				array(
					'dashboard'        => get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ),
					'product-list'     => get_option( '_wkmp_product_list_endpoint', 'seller-products' ),
					'add-product'      => get_option( '_wkmp_add_product_endpoint', 'seller-add-product' ),
					'edit-product'     => get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' ),
					'order-history'    => get_option( '_wkmp_order_history_endpoint', 'sellers-orders' ),
					'transactions'     => get_option( '_wkmp_transaction_endpoint', 'seller-transactions' ),
					'profile'          => get_option( '_wkmp_profile_endpoint', 'seller-profile' ),
					'notifications'    => get_option( '_wkmp_notification_endpoint', 'seller-notifications' ),
					'shop-followers'   => get_option( '_wkmp_shop_follower_endpoint', 'seller-shop-followers' ),
					'asktoadmin'       => get_option( '_wkmp_asktoadmin_endpoint', 'seller-asktoadmin' ),
					'seller_product'   => get_option( '_wkmp_seller_product_endpoint', 'seller-products' ),
					'seller_store'     => get_option( '_wkmp_store_endpoint', 'seller-store' ),
					'seller_feedbacks' => get_option( '_wkmp_feedbacks_endpoint', 'seller-feedbacks' ),
					'add_feedback'     => get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' ),
					'favorite_seller'  => get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' ),
				)
			);

			Includes\WKMP_File_Handler::get_instance();
		}

		/**
		 * Show wc not installed notice.
		 *
		 * @return void
		 */
		public function wkmp_show_wc_not_installed_notice() {
			$plugin = defined( 'WC_PLUGIN_BASENAME' ) ? WC_PLUGIN_BASENAME : 'woocommerce/woocommerce.php';

			$activate_wc_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
			$message         = wp_sprintf( /* Translators: %1$s Marketplace Lite Plugin Name, %2$s: Woocommerce Activation link, %3$s: Closing anchor. */ esc_html__( '%1$s plugin depends on the latest version of WooCommerce. %2$s Click Here to Activate it Now %3$s', 'wk-marketplace' ), '<b>Marketplace Lite for WooCommerce</b>', '<a href=' . esc_url( $activate_wc_url ) . '>', '<a>' );

			if ( ! wkmp_is_woocommerce_installed() ) {
				$install_wc_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );

				$message = wp_sprintf( /* Translators: %1$s Marketplace Lite Plugin Name, %2$s: Woocommerce Install link, %3$s: Closing anchor. */ esc_html__( '%1$s plugin depends on the latest version of WooCommerce. %2$s Click Here to Install it Now %3$s', 'wk-marketplace' ), '<b>Marketplace Lite for WooCommerce</b>', '<a href=' . esc_url( $install_wc_url ) . '>', '<a>' );
			}

			\WK_Caching::wk_show_notice_on_admin( $message, 'error' );
		}

		/**
		 * Show Minimum required MP Pro version not installed notice.
		 *
		 * @return void
		 */
		public function wkmp_show_min_pro_not_installed_notice() {
			$message = wp_sprintf( /* Translators: %s Marketplace Pro module link. */ esc_html__( 'The %1$sMarketplace Lite for WooCommerce (v%2$s) %3$s plugin requires %4$s or later version of %5$s to work! Please consider upgrading it.', 'wk-marketplace' ), '<strong>', esc_html( WKMP_LITE_VERSION ), '</strong>', '<strong>v' . esc_html( WKMP_PRO_MIN_VERSION ) . '</strong>', '<a href="' . esc_url( WKMP_PRO_MODULE_URL ) . '" target="_blank">' . esc_html__( 'Marketplace for WooCommerce', 'wk-marketplace' ) . '</a>' );
			\WK_Caching::wk_show_notice_on_admin( $message, 'error' );
		}

		/**
		 * Seller login.
		 *
		 * @param string $user_login User login name.
		 * @param object $user WP_User object.
		 *
		 * @hooked 'wp_login' action hook.
		 */
		public function wkmp_seller_login( $user_login, $user ) {
			if ( in_array( 'wk_marketplace_seller', $user->roles, true ) ) {
				$current_dash = get_user_meta( $user->ID, 'wkmp_seller_backend_dashboard', true );
				if ( get_user_meta( $user->ID, 'show_admin_bar_front', true ) ) {
					update_user_meta( $user->ID, 'show_admin_bar_front', false );
				}

				if ( ! empty( $current_dash ) ) {
					$this->wkmp_add_role_cap( $user->ID );
					$this->seller_url = esc_url( admin_url( 'admin.php?page=seller' ) );
				} else {
					$this->wkmp_remove_role_cap( $user->ID );
					$this->seller_url = esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ) );
					update_user_meta( $user->ID, 'wkmp_seller_backend_dashboard', null );
				}
				add_filter( 'login_redirect', array( $this, 'wkmp_redirect_seller' ), 10 );
			}
		}

		/**
		 * Redirect seller.
		 *
		 * @return string
		 */
		public function wkmp_redirect_seller() {
			return $this->seller_url;
		}

		/**
		 * Add cap.
		 *
		 * @param int $user_id User id.
		 */
		public function wkmp_add_role_cap( $user_id ) {
			$user = get_user_by( 'ID', $user_id );

			if ( $user instanceof \WP_User ) {
				$user->add_cap( 'manage_woocommerce' );
				$user->add_cap( 'edit_others_shop_orders' );
				$user->add_cap( 'read_product' );
				$user->add_cap( 'edit_product' );
				$user->add_cap( 'delete_product' );
				$user->add_cap( 'edit_products' );
				$user->add_cap( 'publish_products' );
				$user->add_cap( 'read_private_products' );
				$user->add_cap( 'delete_products' );
				$user->add_cap( 'edit_published_products' );
				$user->add_cap( 'assign_product_terms' );
			}
		}

		/**
		 * Remove cap.
		 *
		 * @param int $user_id User id.
		 */
		public function wkmp_remove_role_cap( $user_id ) {
			$user = get_user_by( 'ID', $user_id );

			if ( $user instanceof \WP_User ) {
				$user->remove_cap( 'manage_woocommerce' );
				$user->remove_cap( 'edit_others_shop_orders' );
				$user->remove_cap( 'read_product' );
				$user->remove_cap( 'edit_product' );
				$user->remove_cap( 'delete_product' );
				$user->remove_cap( 'edit_products' );
				$user->remove_cap( 'publish_products' );
				$user->remove_cap( 'read_private_products' );
				$user->remove_cap( 'delete_products' );
				$user->remove_cap( 'edit_published_products' );
				$user->remove_cap( 'assign_product_terms' );
			}
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function wkmp_plugin_url() {
			return untrailingslashit( plugins_url( '/', WKMP_LITE_PLUGIN_FILE ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function wkmp_plugin_path() {
			return untrailingslashit( WKMP_LITE_PLUGIN_FILE );
		}

		/**
		 * Check User is Seller.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return bool
		 */
		public function wkmp_user_is_seller( $user_id ) {
			$seller_user_id = 0;

			if ( ! empty( get_user_by( 'ID', $user_id ) ) ) {
				$seller_user_id = $this->general_query->wkmp_check_if_seller( $user_id );
			}

			return ( $seller_user_id > 0 );
		}

		/**
		 * Check current page is seller.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return bool
		 */
		public function wkmp_is_seller_page( $query_vars = array() ) {
			global $wp;

			if ( empty( $query_vars ) ) {
				$query_vars = $wp->query_vars;
			}

			if ( is_page( $this->seller_page_slug ) ) {
				return true;
			}

			if ( ! empty( $query_vars ) && ( count( array_intersect( $query_vars, $this->endpoints ) ) > 0 || count( array_intersect( array_keys( $query_vars ), $this->endpoints ) ) > 0 ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Get seller id by shop address.
		 *
		 * @param String $shop_address Shop Address.
		 *
		 * @return int
		 */
		public function wkmp_get_seller_id_by_shop_address( $shop_address ) {
			return $this->general_query->wkmp_get_seller_id_by_shop_address( $shop_address );
		}

		/**
		 * Get seller id by shop name.
		 *
		 * @param string $shop_name Shop Name.
		 *
		 * @return int
		 */
		public function wkmp_get_seller_id_by_shop_name( $shop_name ) {
			return $this->general_query->wkmp_get_seller_id_by_shop_name( $shop_name );
		}

		/**
		 * Get Seller Information by ID.
		 *
		 * @param int $seller_id Seller ID.
		 *
		 * @return object
		 */
		public function wkmp_get_seller_info( $seller_id ) {
			$seller_info = array();

			if ( $this->wkmp_user_is_seller( $seller_id ) ) {
				$seller_info = new stdClass();
				$info        = get_user_by( 'ID', $seller_id );
				$meta        = get_user_meta( $seller_id, '', true );

				$seller_info        = $info->data;
				$seller_info->caps  = isset( $info->caps ) ? $info->caps : array();
				$seller_info->roles = isset( $info->roles ) ? $info->roles : array();

				foreach ( $meta as $key => $value ) {
					$seller_info->$key = $value[0];
				}

				$shop_banner  = WKMP_LITE_PLUGIN_URL . 'assets/images/mp-banner.png';
				$shop_logo    = WKMP_LITE_PLUGIN_URL . 'assets/images/shop-logo.png';
				$avatar_image = WKMP_LITE_PLUGIN_URL . 'assets/images/generic-male.png';

				if ( ! empty( $seller_info->_thumbnail_id_shop_banner ) ) {
					$shop_banner_src               = wp_get_attachment_image_src( $seller_info->_thumbnail_id_shop_banner, array( 750, 320 ) );
					$shop_banner_full              = wp_get_attachment_image_src( $seller_info->_thumbnail_id_shop_banner, 'full' );
					$shop_banner                   = ( ! empty( $shop_banner_src ) && is_array( $shop_banner_src ) && count( $shop_banner_src ) > 0 ) ? $shop_banner_src[0] : $shop_banner;
					$seller_info->shop_banner_full = ( ! empty( $shop_banner_full ) && is_array( $shop_banner_full ) && count( $shop_banner_full ) > 0 ) ? $shop_banner_full[0] : $shop_banner;
				}

				if ( ! empty( $seller_info->_thumbnail_id_company_logo ) ) {
					$shop_logo_src               = wp_get_attachment_image_src( $seller_info->_thumbnail_id_company_logo );
					$shop_logo_full              = wp_get_attachment_image_src( $seller_info->_thumbnail_id_company_logo, 'full' );
					$shop_logo                   = ( ! empty( $shop_logo_src ) && is_array( $shop_logo_src ) && count( $shop_logo_src ) > 0 ) ? $shop_logo_src[0] : $shop_logo;
					$seller_info->shop_logo_full = ( ! empty( $shop_logo_full ) && is_array( $shop_logo_full ) && count( $shop_logo_full ) > 0 ) ? $shop_logo_full[0] : $shop_logo;
				}

				if ( ! empty( $seller_info->_thumbnail_id_avatar ) ) {
					$avatar_image_src               = wp_get_attachment_image_src( $seller_info->_thumbnail_id_avatar );
					$avatar_image_full              = wp_get_attachment_image_src( $seller_info->_thumbnail_id_avatar, 'full' );
					$avatar_image                   = ( ! empty( $avatar_image_src ) && is_array( $avatar_image_src ) && count( $avatar_image_src ) > 0 ) ? $avatar_image_src[0] : $avatar_image;
					$seller_info->avatar_image_full = ( ! empty( $avatar_image_full ) && is_array( $avatar_image_full ) && count( $avatar_image_full ) > 0 ) ? $avatar_image_full[0] : $avatar_image;
				}

				$seller_info->shop_banner  = $shop_banner;
				$seller_info->shop_logo    = $shop_logo;
				$seller_info->avatar_image = $avatar_image;

				// Adding generic shop_name if it is empty.
				if ( empty( $seller_info->shop_name ) ) {
					$seller_info->shop_name = wp_sprintf( /* translators: %d: Seller id. */ esc_html__( 'Seller %d', 'wk-marketplace' ), $seller_id );
				}

				// Adding generic shop_url if it is empty.
				if ( empty( $seller_info->shop_address ) ) {
					$seller_info->shop_address = $info->user_login;
				}

				$seller_info = $this->wkmp_add_seller_missing_fields( $seller_info );
			}

			return $seller_info;
		}

		/**
		 * Get the Pagination
		 *
		 * @param int    $total Total items.
		 * @param int    $page Which page.
		 * @param int    $limit How many items display on single page.
		 * @param string $url Page URL.
		 *
		 * @return array $data Pagination info
		 */
		public function wkmp_get_pagination( $total, $page, $limit, $url ) {
			$data = array();
			$url .= '/page/{page}';

			$pagination        = WKMP_Pagination::get_instance();
			$pagination->total = $total;
			$pagination->page  = $page;
			$pagination->limit = $limit;
			$pagination->url   = $url;

			$data['pagination'] = $pagination->wkmp_render();

			$data['results'] = '<p class="woocommerce-result-count">' . sprintf( /* translators: %d total, %d limit, %d offset. */ esc_html__( 'Showing %1$d to %2$d of %3$d (%4$d Pages)', 'wk-marketplace' ), ( $total ) ? ( ( $page - 1 ) * $limit ) + 1 : 0, ( ( ( $page - 1 ) * $limit ) > ( $total - $limit ) ) ? $total : ( ( ( $page - 1 ) * $limit ) + $limit ), $total, ceil( $total / $limit ) ) . '</p>';

			return $data;
		}

		/**
		 * To get first admin user id. It will return smallest admin user id on the site.
		 *
		 * @return int
		 */
		public function wkmp_get_first_admin_user_id() {
			// Find and return first admin user id.
			$first_admin_user_id = 0;
			$admin_users         = get_users(
				array(
					'role'    => 'administrator',
					'orderby' => 'ID',
					'order'   => 'ASC',
					'number'  => 1,
				)
			);

			if ( count( $admin_users ) > 0 && $admin_users[0] instanceof \WP_User ) {
				$first_admin_user_id = $admin_users[0]->ID;
			}

			return $first_admin_user_id;
		}

		/**
		 * May be create missing MP tables if it was not created during activation due to any reason (like fatal error on site)
		 *
		 * @hooked 'admin_init' Action hook.
		 */
		public function wkmp_maybe_create_missing_tables() {
			$get_db_version = get_option( '_wkmp_db_version', '0.0.0' );

			if ( version_compare( WKMP_LITE_DB_VERSION, $get_db_version, '>' ) ) {
				$schema_handler = WKMP_Install::get_instance();
				$schema_handler->wkmp_create_schema();
			}
		}

		/**
		 * Check User is Customer.
		 *
		 * @param int $customer_id User ID.
		 *
		 * @return bool
		 */
		public function wkmp_user_is_customer( $customer_id ) {
			$response = false;

			if ( $customer_id > 0 ) {
				$customer_user = get_user_by( 'ID', $customer_id );
				$cust_roles    = ( $customer_user instanceof \WP_User && isset( $customer_user->roles ) ) ? $customer_user->roles : array();
				$allowed_roles = array( 'customer', 'subscriber' );

				if ( count( array_intersect( $allowed_roles, $cust_roles ) ) > 0 && ! in_array( 'wk_marketplace_seller', $cust_roles, true ) && ! $this->wkmp_user_is_pending_seller( $customer_id ) ) {
					$response = true;
				}
			}

			return $response;
		}

		/**
		 * To check if current page belongs to WooCommerce pages.
		 *
		 * @return false
		 */
		public function wkmp_is_woocommerce_page() {
			if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url() || is_product_tag() || is_checkout_pay_page() || is_view_order_page() || is_edit_account_page() || is_order_received_page() ) {
				return true;
			}

			return false;
		}

		/**
		 * Remove 'Home' menu from WooCommerce if seller backend dashboard.
		 *
		 * @hooked 'admin_menu' action hook.
		 *
		 * @return void
		 */
		public function wkmp_remove_home_menu_wc_admin() {
			$seller_id = get_current_user_id();
			if ( $this->wkmp_user_is_seller( $seller_id ) ) {
				remove_submenu_page( 'woocommerce', 'wc-admin' );

				if ( 'disabled' === get_option( 'woocommerce_ship_to_countries', false ) ) {
					remove_submenu_page( 'woocommerce', 'wc-settings' );
				}
			}
		}

		/**
		 * Check if User is pending seller to approve.
		 *
		 * @param int $customer_id User ID.
		 *
		 * @return bool
		 */
		public function wkmp_user_is_pending_seller( $customer_id = 0 ) {
			$response    = false;
			$customer_id = ( $customer_id > 0 ) ? $customer_id : get_current_user_id();

			if ( $customer_id > 0 ) {
				$seller_id = $this->general_query->wkmp_get_pending_seller_id( $customer_id );
				$response  = ( $seller_id > 0 );
			}

			return $response;
		}

		/**
		 * Removing translate capability.
		 *
		 * @param int $user_id User id.
		 *
		 * @return void
		 */
		public function wkmp_remove_seller_translate_capability( $user_id = 0 ) {
			$sellers = array();
			if ( $user_id > 0 ) {
				$sellers[] = get_user_by( 'ID', $user_id );
			} else {
				$sellers = get_users( array( 'role' => 'wk_marketplace_seller' ) );
			}

			foreach ( $sellers as $seller ) {
				$seller->remove_cap( 'translate' );
				$this->general_query->wkmp_delete_icl_user_meta( $seller->ID, 'language_pairs' );
			}
		}

		/**
		 * To get common table data for seller separate dashboard and and front order history.
		 *
		 * @param array $filter_data Filter data.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_order_table_data( $filter_data ) {
			$table_data = $this->general_query->wkmp_get_seller_order_data( $filter_data );

			return apply_filters( 'wkmp_seller_order_table_data', $table_data );
		}

		/**
		 * Get parsed seller info.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $posted_data Posted data.
		 *
		 * @return array
		 */
		public function wkmp_get_parsed_seller_info( $seller_id, $posted_data = array() ) {
			$seller_info = array();

			$field_keys = apply_filters(
				'wkmp_seller_default_meta_fields',
				array(
					'wkmp_username',
					'wkmp_seller_email',
					'wkmp_first_name',
					'wkmp_last_name',
					'wkmp_shop_name',
					'wkmp_shop_url',
					'wkmp_about_shop',
					'wkmp_shop_address_1',
					'wkmp_shop_address_2',
					'wkmp_shop_city',
					'wkmp_shop_postcode',
					'wkmp_shop_phone',
					'wkmp_shop_country',
					'wkmp_shop_state',
					'wkmp_payment_details',
					'wkmp_display_banner',
					'wkmp_avatar_id',
					'wkmp_logo_id',
					'wkmp_banner_id',
					'wkmp_avatar_file',
					'wkmp_logo_file',
					'wkmp_banner_file',
					'wkmp_generic_avatar',
					'wkmp_generic_logo',
					'wkmp_generic_banner',
					'wkmp_facebook',
					'wkmp_instagram',
					'wkmp_twitter',
					'wkmp_linkedin',
					'wkmp_youtube',
				)
			);

			foreach ( $field_keys as $field_key ) {
				$seller_info[ $field_key ] = '';
			}

			if ( $this->wkmp_user_is_seller( $seller_id ) ) {
				$seller_user_obj = get_user_by( 'ID', $seller_id );
				$user_meta       = get_user_meta( $seller_id );

				$shop_slug = $this->wkmp_get_seller_store_address( $seller_id );

				$seller_info['wkmp_seller_id']       = $seller_id;
				$seller_info['wkmp_first_name']      = empty( $posted_data['wkmp_first_name'] ) ? ( empty( $user_meta['first_name'][0] ) ? '' : $user_meta['first_name'][0] ) : $posted_data['wkmp_first_name'];
				$seller_info['wkmp_last_name']       = empty( $posted_data['wkmp_last_name'] ) ? ( empty( $user_meta['last_name'][0] ) ? '' : $user_meta['last_name'][0] ) : $posted_data['wkmp_last_name'];
				$seller_info['wkmp_shop_name']       = empty( $posted_data['wkmp_shop_name'] ) ? ( empty( $user_meta['shop_name'][0] ) ? '' : $user_meta['shop_name'][0] ) : $posted_data['wkmp_shop_name'];
				$seller_info['wkmp_shop_url']        = empty( $posted_data['wkmp_shop_url'] ) ? $shop_slug : $posted_data['wkmp_shop_url'];
				$seller_info['wkmp_about_shop']      = empty( $posted_data['wkmp_about_shop'] ) ? ( empty( $user_meta['about_shop'][0] ) ? '' : $user_meta['about_shop'][0] ) : $posted_data['wkmp_about_shop'];
				$seller_info['wkmp_shop_address_1']  = empty( $posted_data['wkmp_shop_address_1'] ) ? ( empty( $user_meta['billing_address_1'][0] ) ? '' : $user_meta['billing_address_1'][0] ) : $posted_data['wkmp_shop_address_1'];
				$seller_info['wkmp_shop_address_2']  = empty( $posted_data['wkmp_shop_address_2'] ) ? ( empty( $user_meta['billing_address_2'][0] ) ? '' : $user_meta['billing_address_2'][0] ) : $posted_data['wkmp_shop_address_2'];
				$seller_info['wkmp_shop_city']       = empty( $posted_data['wkmp_shop_city'] ) ? ( empty( $user_meta['billing_city'][0] ) ? '' : $user_meta['billing_city'][0] ) : $posted_data['wkmp_shop_city'];
				$seller_info['wkmp_shop_postcode']   = empty( $posted_data['wkmp_shop_postcode'] ) ? ( empty( $user_meta['billing_postcode'][0] ) ? '' : $user_meta['billing_postcode'][0] ) : $posted_data['wkmp_shop_postcode'];
				$seller_info['wkmp_shop_phone']      = empty( $posted_data['wkmp_shop_phone'] ) ? ( empty( $user_meta['billing_phone'][0] ) ? '' : $user_meta['billing_phone'][0] ) : $posted_data['wkmp_shop_phone'];
				$seller_info['wkmp_shop_country']    = empty( $posted_data['wkmp_shop_country'] ) ? ( empty( $user_meta['billing_country'][0] ) ? '' : $user_meta['billing_country'][0] ) : $posted_data['wkmp_shop_country'];
				$seller_info['wkmp_shop_state']      = empty( $posted_data['wkmp_shop_state'] ) ? ( empty( $user_meta['billing_state'][0] ) ? '' : $user_meta['billing_state'][0] ) : $posted_data['wkmp_shop_state'];
				$seller_info['wkmp_payment_details'] = empty( $posted_data['wkmp_payment_details'] ) ? ( empty( $user_meta['mp_seller_payment_details'][0] ) ? '' : $user_meta['mp_seller_payment_details'][0] ) : $posted_data['wkmp_payment_details'];
				$seller_info['wkmp_display_banner']  = empty( $posted_data['wkmp_display_banner'] ) ? ( empty( $user_meta['shop_banner_visibility'][0] ) ? '' : $user_meta['shop_banner_visibility'][0] ) : $posted_data['wkmp_display_banner'];

				$seller_info['wkmp_facebook']  = empty( $posted_data['wkmp_facebook'] ) ? ( empty( $user_meta['social_facebook'][0] ) ? '' : $user_meta['social_facebook'][0] ) : $posted_data['wkmp_facebook'];
				$seller_info['wkmp_instagram'] = empty( $posted_data['wkmp_instagram'] ) ? ( empty( $user_meta['social_instagram'][0] ) ? '' : $user_meta['social_instagram'][0] ) : $posted_data['wkmp_instagram'];
				$seller_info['wkmp_twitter']   = empty( $posted_data['wkmp_twitter'] ) ? ( empty( $user_meta['social_twitter'][0] ) ? '' : $user_meta['social_twitter'][0] ) : $posted_data['wkmp_twitter'];
				$seller_info['wkmp_linkedin']  = empty( $posted_data['wkmp_linkedin'] ) ? ( empty( $user_meta['social_linkedin'][0] ) ? '' : $user_meta['social_linkedin'][0] ) : $posted_data['wkmp_linkedin'];
				$seller_info['wkmp_youtube']   = empty( $posted_data['wkmp_youtube'] ) ? ( empty( $user_meta['social_youtube'][0] ) ? '' : $user_meta['social_youtube'][0] ) : $posted_data['wkmp_youtube'];

				$seller_info['wkmp_seller_email']   = empty( $posted_data['wkmp_seller_email'] ) ? ( empty( $seller_user_obj->user_email ) ? '' : $seller_user_obj->user_email ) : $posted_data['wkmp_seller_email'];
				$seller_info['wkmp_username']       = empty( $posted_data['wkmp_username'] ) ? ( empty( $seller_user_obj->user_login ) ? '' : $seller_user_obj->user_login ) : $posted_data['wkmp_username'];
				$seller_info['wkmp_generic_avatar'] = esc_url( WKMP_LITE_PLUGIN_URL ) . 'assets/images/generic-male.png';
				$seller_info['wkmp_generic_logo']   = esc_url( WKMP_LITE_PLUGIN_URL ) . 'assets/images/shop-logo.png';
				$seller_info['wkmp_generic_banner'] = esc_url( WKMP_LITE_PLUGIN_URL ) . 'assets/images/mp-banner.png';

				$seller_info['wkmp_avatar_id'] = empty( $user_meta['_thumbnail_id_avatar'][0] ) ? ( empty( $posted_data['wkmp_avatar_id'] ) ? '' : $posted_data['wkmp_avatar_id'] ) : $user_meta['_thumbnail_id_avatar'][0];
				$seller_info['wkmp_logo_id']   = empty( $user_meta['_thumbnail_id_company_logo'][0] ) ? ( empty( $posted_data['wkmp_logo_id'] ) ? '' : $posted_data['wkmp_logo_id'] ) : $user_meta['_thumbnail_id_company_logo'][0];
				$seller_info['wkmp_banner_id'] = empty( $user_meta['_thumbnail_id_shop_banner'][0] ) ? ( empty( $posted_data['wkmp_banner_id'] ) ? '' : $posted_data['wkmp_banner_id'] ) : $user_meta['_thumbnail_id_shop_banner'][0];

				$avatar_file = wp_get_attachment_image_src( $seller_info['wkmp_avatar_id'] );
				$logo_file   = wp_get_attachment_image_src( $seller_info['wkmp_logo_id'] );
				$banner_file = wp_get_attachment_image_src( $seller_info['wkmp_banner_id'], array( 750, 320 ) );

				if ( ! empty( $avatar_file ) && ! empty( $avatar_file[0] ) ) {
					$seller_info['wkmp_avatar_file'] = $avatar_file[0];
				}
				if ( ! empty( $logo_file ) && ! empty( $logo_file[0] ) ) {
					$seller_info['wkmp_logo_file'] = $logo_file[0];
				}
				if ( ! empty( $banner_file ) && ! empty( $banner_file[0] ) ) {
					$seller_info['wkmp_banner_file'] = $banner_file[0];
				}
			}

			return apply_filters( 'wkmp_return_parsed_seller_info', $seller_info, $seller_id, $posted_data );
		}

		/**
		 * Returning WC registered endpoints.
		 *
		 * @return array
		 */
		public function wkmp_get_wc_registered_endpoints() {
			$this->wc_endpoints = apply_filters(
				'wkmp_wc_registered_endpoints',
				array(
					'dashboard',
					get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' ),
					get_option( 'woocommerce_myaccount_downloads_endpoint', 'downloads' ),
					get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' ),
					get_option( 'woocommerce_myaccount_payment_methods_endpoint', 'payment-methods' ),
					get_option( 'woocommerce_myaccount_edit_account_endpoint', 'edit-account' ),
					get_option( 'woocommerce_logout_endpoint', 'customer-logout' ),
					get_option( 'woocommerce_checkout_pay_endpoint', 'order-pay' ),
					get_option( 'woocommerce_checkout_order_received_endpoint', 'order-received' ),
					get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' ),
					get_option( 'woocommerce_myaccount_lost_password_endpoint', 'lost-password' ),
					get_option( 'woocommerce_myaccount_add_payment_method_endpoint', 'add-payment-method' ),
					get_option( 'woocommerce_myaccount_delete_payment_method_endpoint', 'delete-payment-method' ),
					get_option( 'woocommerce_myaccount_set_default_payment_method_endpoint', 'set-default-payment-method' ),
				)
			);
			return $this->wc_endpoints;
		}


		/**
		 * Get seller store URL.
		 *
		 * @param  int $seller_id Seller id.
		 *
		 * @return string
		 */
		public function wkmp_get_seller_store_url( $seller_id ) {
			$shop_slug = $this->wkmp_get_seller_store_address( $seller_id );

			$url = site_url() . '/' . $this->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'seller-store' ) . '/' . $shop_slug;

			return apply_filters( 'wkmp_update_seller_profile_url', $url, $seller_id, $shop_slug );
		}

		/**
		 * Get seller store slug.
		 *
		 * @param  int $seller_id Seller id.
		 *
		 * @return string
		 */
		public function wkmp_get_seller_store_address( $seller_id ) {
			$shop_slug = get_user_meta( $seller_id, 'shop_address', true );
			if ( empty( $shop_slug ) ) {
				$seller_user = get_user_by( 'ID', $seller_id );
				$shop_slug   = empty( $seller_user->user_login ) ? $seller_id : $seller_user->user_login;
			}

			return apply_filters( 'wkmp_seller_shop_slug', $shop_slug, $seller_id );
		}

		/**
		 * Get display name of a user.
		 *
		 * @param int           $user_id User id.
		 * @param object|string $user User objct.
		 * @param string        $display_type Name display type e.g. 'full'|'nick,'store_name'.
		 * @param string        $default_name Default name.
		 *
		 * @return string
		 */
		public function wkmp_get_user_display_name( $user_id = 0, $user = '', $display_type = 'full', $default_name = '' ) {
			$display_name = empty( $default_name ) ? __( 'Anonymous User', 'wk-marketplace' ) : $default_name;

			$user_id = ( empty( $user_id ) && ! $user instanceof \WP_User ) ? get_current_user_id() : $user_id;

			if ( ! $user instanceof \WP_User && $user_id > 0 ) {
				$user = get_user_by( 'ID', $user_id );
			}

			if ( is_a( $user, 'WP_User' ) ) {
				if ( 'nick' === $display_type ) {
					$display_name = empty( $user->user_nicename ) ? $user->display_name : $user->user_nicename;
				} elseif ( 'shop_name' === $display_type ) {
					$display_name = get_user_meta( $user_id, 'shop_name', true );
				} else {
					$display_name  = empty( $user->first_name ) ? get_user_meta( $user_id, 'first_name', true ) : $user->first_name;
					$display_name .= empty( $display_name ) ? '' : ( empty( $user->last_name ) ? ' ' . get_user_meta( $user_id, 'last_name', true ) : ' ' . $user->last_name );
					$display_name  = empty( $display_name ) ? $user->display_name : $display_name;
					$display_name  = empty( $display_name ) ? $user->user_nicename : $display_name;
					$display_name  = empty( $display_name ) ? $user->user_login : $display_name;
				}
			}

			return apply_filters( 'wkmp_get_user_display_name', $display_name, $user_id );
		}

		/**
		 * Get seller default data.
		 *
		 * @param object $seller_info Seller info.
		 *
		 * @return object
		 */
		public function wkmp_add_seller_missing_fields( $seller_info ) {
			if ( empty( $seller_info ) ) {
				$seller_info = new stdClass();
			}

			$default_info_keys = array(
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_company',
				'billing_country',
				'billing_state',
				'billing_email',
				'billing_phone',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_company',
				'shipping_country',
				'shipping_state',
				'shop_name',
				'shop_banner_visibility',
				'shop_address',
				'about_shop',
				'social_facebook',
				'social_instagram',
				'social_linkedin',
				'social_twitter',
				'social_youtube',
			);

			foreach ( $default_info_keys as $key ) {
				if ( ! isset( $seller_info->{$key} ) ) {
					$seller_info->{$key} = '';
				}
			}

			return $seller_info;
		}

		/**
		 * Return true if Pro module is deactivated.
		 *
		 * @return bool
		 */
		public function wkmp_is_pro_module_disabled() {
			$pro_active = wkmp_is_pro_active();

			$disabled = apply_filters( 'wkmp_is_pro_module_disabled', ! $pro_active );

			return ( ! defined( 'WKMP_LITE_MIN_VERSION' ) || $disabled );
		}

		/**
		 * Log function for debugging.
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
		 */
		public function log( $message, $context = array(), $level = 'info' ) {
			if ( function_exists( 'wkmp_wc_log' ) ) {
				wkmp_wc_log( $message, $context, $level );
			}
		}

		/**
		 * Decide whether display seller registration fields are allowed or not.
		 *
		 * @return bool
		 */
		public function wkmp_allow_seller_registration_fields() {
			$display = false;

			$separate_reg_enabled = ( 1 === intval( get_option( '_wkmp_separate_seller_registration', false ) ) );

			if ( ( $separate_reg_enabled && ! is_account_page() && $this->wkmp_is_seller_page() ) || ( ! $separate_reg_enabled && ! is_user_logged_in() && ! $this->wkmp_is_seller_page() && is_account_page() ) ) {
				$display = true;
			}

			if ( $display ) {
				$general_query = Helper\WKMP_General_Queries::get_instance();
				$display       = $general_query->wkmp_validate_seller_registration();
			}

			$pro_disabled = $this->wkmp_is_pro_module_disabled();

			if ( ! $pro_disabled ) {
				$pro     = new parent();
				$display = method_exists( $pro, __FUNCTION__ ) ? $pro->{__FUNCTION__}() : false;
			}

			return $display;
		}

		/**
		 * Display All settings tabs for addons.
		 *
		 * @param array  $tabs Setting tabs.
		 * @param string $title Page title.
		 * @param string $icon Module icon.
		 *
		 * @since 5.3.0
		 */
		public function create_settings_tabs( $tabs = array(), $title = '', $icon = '' ) {
			$submenu_name = ( is_array( $tabs ) && count( $tabs ) > 0 ) ? array_keys( $tabs )[0] : '';
			$submenu_page = \WK_Caching::wk_get_request_data( 'page' );

			if ( ! empty( $submenu_name ) && ! empty( $submenu_page ) && $submenu_name === $submenu_page ) {
				$tab = \WK_Caching::wk_get_request_data( 'tab' );

				$current_tab = empty( $tab ) ? $submenu_name : $tab;
				if ( ! empty( $tab ) ) {
					$submenu_page .= '_' . $tab;
				}
				$title = empty( $title ) ? array_values( $tabs )[0] : $title;
				?>
			<div class="wkmp-addons-tabs-wrap">
				<nav class="nav-tab-wrapper wkmp-admin-addon-list-manage-nav">
					<div class="wkmp-addons-page-header">
						<div class="module-icon">
						<?php echo wp_kses_post( $icon ); ?>
						</div>
						<p class="page-title"><?php echo esc_html( $title ); ?></p>
					</div>
					<div class="wkmp-addons-nav-link">
				<?php
				foreach ( $tabs as $name => $label ) {
					$tab_url  = admin_url( 'admin.php?page=' . esc_attr( $submenu_name ) );
					$tab_url .= ( $name === $submenu_name ) ? '' : '&tab=' . $name;
					echo wp_sprintf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( $tab_url ), ( $current_tab === $name ? 'nav-tab-active' : '' ), esc_html( $label ) );
				}
				?>
					</div>
				</nav>
				<?php
				do_action( $submenu_page . '_content', $submenu_name );
				?>
			</div>
				<?php
			}
		}

		/**
		 * Declare plugin is compatible with HPOS.
		 *
		 * @param string $file Plugin main file path.
		 * @param bool   $status Compatibility status.
		 *
		 * @return void
		 */
		public static function wkmp_declare_hpos_compatibility_status( $file = '', $status = true ) {
			add_action(
				'before_woocommerce_init',
				function () use ( $file, $status ) {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $file, $status );
					}
				}
			);
		}

		/**
		 * Declare plugin is incompatible with WC Cart and Checkout blocks.
		 *
		 * @param string $file Plugin main file path.
		 * @param bool   $status Compatibility status.
		 *
		 * @return void
		 */
		public static function wkmp_declare_cart_checkout_block_compatibility_status( $file = '', $status = false ) {
			add_action(
				'before_woocommerce_init',
				function () use ( $file, $status ) {
					if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $file, $status );
					}
				}
			);
		}
	}
}
