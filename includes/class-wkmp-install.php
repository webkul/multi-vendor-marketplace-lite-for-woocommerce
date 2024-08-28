<?php
/**
 * Schema template.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Install' ) ) {

	/**
	 * Class to create schema on plugin activation
	 */
	class WKMP_Install {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

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

		/**
		 * Create required tables
		 *
		 * @return void
		 */
		public function wkmp_create_schema() {
			$get_db_version = get_option( '_wkmp_db_version', '0.0.0' ); // This option key is updated in DB in MP-5.1.0 released on 2021-10-20.

			if ( version_compare( WKMP_LITE_DB_VERSION, $get_db_version, '>' ) ) {
				$this->wkmp_create_seller_page();
				$this->wkmp_create_seller_role();
				$this->wkmp_create_db_tables();
				$this->wkmp_migrate_old_option_keys_values_501( $get_db_version );
				$this->wkmp_migrate_email_settings_512( $get_db_version ); // Given WCML compatibility.
				$this->wkmp_migrate_endpoint_520_settings( $get_db_version ); // Changed seller page endpoints to My-Account endpoints.
			}
		}

		/**
		 * Create Seller Page on activation
		 *
		 * @return void
		 */
		public function wkmp_create_seller_page() {
			$seller_page_slug = apply_filters( 'wkmp_seller_page_slug', get_option( 'wkmp_seller_page_slug', 'seller' ) );
			$pages            = apply_filters(
				'wkmp_marketplace_pages_data',
				array(
					$seller_page_slug => array(
						'title'   => esc_html__( 'Seller', 'wk-marketplace' ),
						'content' => '[marketplace]',
					),
				)
			);

			foreach ( $pages as $key => $value ) {
				$page    = get_page_by_path( $key );
				$page_id = isset( $page->ID ) ? $page->ID : 0;

				if ( empty( $page_id ) ) {
					$page_data = array(
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'post_author'    => get_current_user_id(),
						'post_name'      => $key,
						'post_title'     => $value['title'],
						'post_content'   => $value['content'],
						'comment_status' => 'closed',
					);

					$page_id = wp_insert_post( $page_data );

					if ( $page_id > 0 && $seller_page_slug === $key ) {
						update_option( 'wkmp_seller_page_id', $page_id, 'no' );
					}
				}
			}
		}

		/**
		 * Create new seller role on activation
		 *
		 * @return void
		 */
		public function wkmp_create_seller_role() {
			global $wp_roles;

			$role = 'wk_marketplace_seller';
			if ( get_role( $role ) ) {
				wkmp_wc_log( "Role: $role is already created." );
				if ( ! get_role( $role )->has_cap( 'delete_published_products' ) ) {
					$new_caps = array(
						'delete_published_products',
						'delete_post',
						'delete_product',
						'delete_products',
					);
					foreach ( $new_caps as $cap ) {
						$wp_roles->add_cap( 'wk_marketplace_seller', $cap );
					}
				}

				return;
			}

			add_role(
				$role,
				esc_html__( 'Marketplace Seller', 'wk-marketplace' ),
				array(
					'read'                      => true, // True allows that capability.
					'edit_posts'                => true,
					'delete_post'               => true, // Use false to explicitly deny.
					'delete_posts'              => true, // Use false to explicitly deny.
					'publish_posts'             => true,
					'edit_published_posts'      => false,
					'upload_files'              => true,
					'delete_published_posts'    => true,
					'delete_product'            => true,
					'delete_products'           => true,
					'delete_published_products' => true,
				)
			);

			$capabilities = array(
				'manage_marketplace',
				'edit_products',
			);

			foreach ( $capabilities as $capability ) {
				$wp_roles->add_cap( 'administrator', $capability );
			}
		}

		/**
		 * Create Tables
		 *
		 * @return void
		 */
		public function wkmp_create_db_tables() {
			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$table_name1 = $wpdb->prefix . 'mpsellerinfo';

			$query1 = "CREATE TABLE IF NOT EXISTS $table_name1 (
				seller_id bigint(20) NOT NULL AUTO_INCREMENT,
				user_id bigint(20) NOT NULL,
				seller_key varchar(30) NULL,
				seller_value varchar(30) NULL,
				PRIMARY KEY  (seller_id)
			) $charset_collate;";

			dbDelta( $query1 );

			$table_name2 = $wpdb->prefix . 'mpcommision';

			$query2 = "CREATE TABLE IF NOT EXISTS $table_name2 (
				ID bigint(20) NOT NULL AUTO_INCREMENT,
				seller_id bigint(20),
				commision_on_seller double,
				admin_amount double,
				seller_total_ammount double,
				paid_amount double,
				last_paid_ammount double,
				last_com_on_total double,
				total_refunded_amount double,
				seller_payment_method varchar(255),
				payment_id_desc text,
				PRIMARY KEY  (ID)
			) $charset_collate;";

			dbDelta( $query2 );

			$table_name3 = $wpdb->prefix . 'mpseller_meta';

			$query3 = "CREATE TABLE IF NOT EXISTS $table_name3 (
				seller_meta_id bigint(20) NOT NULL AUTO_INCREMENT,
				seller_id bigint(20),
				zone_id bigint(20),
				PRIMARY KEY  (seller_meta_id)
			) $charset_collate;";

			dbDelta( $query3 );

			$table_name4 = $wpdb->prefix . 'mpfeedback';

			$query4 = "CREATE TABLE IF NOT EXISTS $table_name4 (
				ID bigint(20) NOT NULL AUTO_INCREMENT,
				seller_id bigint(20),
				user_id bigint(20),
				price_r int(1),
				value_r int(1),
				quality_r int(1),
				nickname varchar(255),
				review_summary text,
				review_desc text,
				review_time datetime,
				status int(1) COMMENT '0 - Pending | 1 - Approved | 2 - Disapproved',
				PRIMARY KEY  (ID)
			) $charset_collate;";

			dbDelta( $query4 );

			$table_name5 = $wpdb->prefix . 'mp_notifications';

			$query5 = "CREATE TABLE $table_name5 (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				type varchar(30) NOT NULL,
				author_id bigint(20) NOT NULL,
				context bigint(20) DEFAULT '0',
				content text NOT NULL,
				read_flag int(1) NOT NULL DEFAULT '0',
				timestamp datetime DEFAULT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $query5 );

			$table_name6 = $wpdb->prefix . 'mpseller_orders';

			$query6 = "CREATE TABLE IF NOT EXISTS $table_name6 (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				order_id bigint(20) NOT NULL,
				seller_id bigint(20) NOT NULL,
				order_status varchar(30) NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $query6 );

			$table_name7 = $wpdb->prefix . 'mpseller_asktoadmin';

			$query7 = "CREATE TABLE IF NOT EXISTS $table_name7 (
				id bigint(20) NOT NULL AUTO_INCREMENT,
		 	  seller_id bigint(20) NOT NULL,
				subject varchar(100) NOT NULL,
		 	  message varchar(500) NOT NULL,
				create_date datetime NOT NULL,
		 	  PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $query7 );

			$table_name8 = $wpdb->prefix . 'mporders';

			$query8 = "CREATE TABLE IF NOT EXISTS $table_name8 (
				id bigint(20) NOT NULL auto_increment,
				order_id bigint(20) NOT NULL,
				product_id bigint(20) NOT NULL,
				seller_id bigint(20) NOT NULL,
				amount float NOT NULL,
				admin_amount float NOT NULL,
				seller_amount float NOT NULL,
				quantity bigint(20) NOT NULL,
				commission_applied float NOT NULL,
				discount_applied float NOT NULL,
				commission_type varchar(200) NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $query8 );

			$table_name9 = $wpdb->prefix . 'mporders_meta';

			$query9 = "CREATE TABLE IF NOT EXISTS $table_name9 (
				mmid bigint(20) NOT NULL auto_increment,
				seller_id bigint(20),
				order_id bigint(20),
				meta_key varchar(255),
				meta_value varchar(255),
				PRIMARY KEY  (mmid)
			) $charset_collate;";

			dbDelta( $query9 );

			$table_name10 = $wpdb->prefix . 'seller_transaction';

			$query10 = "CREATE TABLE IF NOT EXISTS $table_name10 (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				transaction_id varchar(100) NOT NULL,
				order_id bigint(20) NOT NULL,
				order_item_id bigint(20) NOT NULL,
				seller_id bigint(20) NOT NULL,
				amount float NOT NULL,
				type varchar(100) NOT NULL,
		 	   	method varchar(100) NOT NULL,
				transaction_date datetime NOT NULL,
		 	   	PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $query10 );

			$table_name11 = $wpdb->prefix . 'mpseller_asktoadmin_meta';

			$query11 = "CREATE TABLE IF NOT EXISTS $table_name11 (
				id bigint(20) NOT NULL auto_increment,
				meta_key varchar(255),
				meta_value varchar(255),
				PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $query11 );

			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}mpfeedback'" ) === $wpdb->prefix . 'mpfeedback' ) {
				$s = $wpdb->get_results( "SHOW COLUMNS FROM `{$wpdb->prefix}mpfeedback`  WHERE field = 'status'" );
				if ( isset( $s ) && ! $s ) {
					$wpdb->query( "ALTER TABLE {$wpdb->prefix}mpfeedback ADD `status` INT(1) NOT NULL DEFAULT 0" );
				}
			}

			wkmp_wc_log( 'All tables are successfully created.' );
		}

		/**
		 * Migrate settings if updating from 4.9.3
		 *
		 * @since 5.0.1
		 *
		 * @param string $wkmp_db_version Marketplace version saved in DB.
		 */
		public function wkmp_migrate_old_option_keys_values_501( $wkmp_db_version ) {
			if ( version_compare( '1.0.4', $wkmp_db_version, '<' ) ) { // If anyone migrates to 5.2.0 or higher version from 5.0.0 or any earlier then we migrate old option keys otherwise it had been already migrated.
				if ( false !== get_option( 'wkmpcom_minimum_com_onseller', false ) && false === get_option( '_wkmp_default_commission', false ) ) {
					$option_keys = array(
						'wkmpcom_minimum_com_onseller'   => '_wkmp_default_commission',
						'wkmp_auto_approve_seller'       => '_wkmp_auto_approve_seller',
						'wkmp_enable_seller_seperate_dashboard' => '_wkmp_separate_seller_dashboard',
						'wkmp_show_seller_seperate_form' => '_wkmp_separate_seller_registration',
						'wkmp_seller_allow_publish'      => '_wkmp_allow_seller_to_publish',
						'wkmp_seller_allowed_product_types' => '_wkmp_seller_allowed_product_types',
						'wkmp_seller_allowed_categories' => '_wkmp_seller_allowed_categories',
						'wkmp_show_seller_email'         => '_wkmp_is_seller_email_visible',
						'wkmp_show_seller_contact'       => '_wkmp_is_seller_contact_visible',
						'wkmp_show_seller_address'       => '_wkmp_is_seller_address_visible',
						'wkmp_show_seller_social_links'  => '_wkmp_is_seller_social_links_visible',
						'mp_dashboard'                   => '_wkmp_dashboard_endpoint',
						'mp_dashboard_name'              => '_wkmp_dashboard_endpoint_name',
						'mp_product_list'                => '_wkmp_product_list_endpoint',
						'mp_product_list_name'           => '_wkmp_product_list_endpoint_name',
						'mp_add_product'                 => '_wkmp_add_product_endpoint',
						'mp_add_product_name'            => '_wkmp_add_product_endpoint_name',
						'mp_order_history'               => '_wkmp_order_history_endpoint',
						'mp_order_history_name'          => '_wkmp_order_history_endpoint_name',
						'mp_transaction'                 => '_wkmp_transaction_endpoint',
						'mp_transaction_name'            => '_wkmp_transaction_endpoint_name',
						'mp_shipping'                    => '_wkmp_shipping_endpoint',
						'mp_shipping_name'               => '_wkmp_shipping_endpoint_name',
						'mp_profile'                     => '_wkmp_profile_endpoint',
						'mp_profile_name'                => '_wkmp_profile_endpoint_name',
						'mp_notification'                => '_wkmp_notification_endpoint',
						'mp_notification_name'           => '_wkmp_notification_endpoint_name',
						'mp_shop_follower'               => '_wkmp_shop_follower_endpoint',
						'mp_shop_follower_name'          => '_wkmp_shop_follower_endpoint_name',
						'mp_to'                          => '_wkmp_asktoadmin_endpoint',
						'mp_to_name'                     => '_wkmp_asktoadmin_endpoint_name',
						'mp_seller_product'              => '_wkmp_seller_product_endpoint',
						'mp_seller_product_name'         => '_wkmp_seller_product_endpoint_name',
						'mp_store'                       => '_wkmp_store_endpoint',
						'mp_store_name'                  => '_wkmp_store_endpoint_name',
					);

					foreach ( $option_keys as $key_4 => $key_5 ) {
						$value_4 = get_option( $key_4, false );
						$value_5 = get_option( $key_5, false );

						if ( false === $value_5 && false !== $value_4 ) {
							update_option( $key_5, $value_4 );
						}
					}
				}
			}
		}

		/**
		 * Migrate Email settings if updating from 5.1.0 to support WCML compatibility.
		 *
		 * @param string $wkmp_db_version Marketplace version saved in DB.
		 *
		 * @since 5.1.2
		 */
		public function wkmp_migrate_email_settings_512( $wkmp_db_version ) {
			if ( version_compare( '1.0.6', $wkmp_db_version, '<' ) ) { // If anyone migrates to 5.2.0 or higher version from 5.0.2 or any earlier then we migrate old Email otherwise it had been already migrated.
				$option_keys = array(
					'woocommerce_new_query_settings'       => 'woocommerce_wkmp_ask_to_admin_settings',
					'woocommerce_customer_become_seller_to_admin_settings' => 'woocommerce_wkmp_customer_become_seller_to_admin_settings',
					'woocommerce_customer_become_seller_settings' => 'woocommerce_wkmp_customer_become_seller_settings',
					'woocommerce_seller_register_to_admin_settings' => 'woocommerce_wkmp_new_seller_registration_to_admin_settings',
					'woocommerce_product_approve_disapporve_settings' => 'woocommerce_wkmp_product_approve_disapprove_settings',
					'woocommerce_new_seller_settings'      => 'woocommerce_wkmp_registration_details_to_seller_settings',
					'woocommerce_seller_approval_settings' => 'woocommerce_wkmp_seller_account_approved_settings',
					'woocommerce_seller_disapproval_settings' => 'woocommerce_wkmp_seller_account_disapproved_settings',
					'woocommerce_seller_order_cancelled_settings' => 'woocommerce_wkmp_seller_order_cancelled_settings',
					'woocommerce_seller_order_completed_settings' => 'woocommerce_wkmp_seller_order_completed_settings',
					'woocommerce_seller_order_failed_settings' => 'woocommerce_wkmp_seller_order_failed_settings',
					'woocommerce_seller_order_onhold_settings' => 'woocommerce_wkmp_seller_order_on_hold_settings',
					'woocommerce_seller_order_approved_settings' => 'woocommerce_wkmp_seller_order_paid_settings',
					'woocommerce_seller_order_processing_settings' => 'woocommerce_wkmp_seller_order_processing_settings',
					'woocommerce_seller_order_refunded_settings' => 'woocommerce_wkmp_seller_order_refunded_settings',
					'woocommerce_seller_order_placed_settings' => 'woocommerce_wkmp_seller_product_ordered_settings',
					'woocommerce_product_approve_settings' => 'woocommerce_wkmp_seller_published_product_settings',
					'woocommerce_query_reply_settings'     => 'woocommerce_wkmp_seller_query_replied_settings',
					'woocommerce_shop_follower_settings'   => 'woocommerce_wkmp_seller_to_shop_followers_settings',
				);

				foreach ( $option_keys as $key_old => $key_new ) {
					$value_old = get_option( $key_old, false );
					$value_new = get_option( $key_new, false );

					if ( false === $value_new && false !== $value_old ) {
						update_option( $key_new, $value_old );
					}
				}
			}
		}

		/**
		 * Migrate new endpoints for My-Account in 5.2.0.
		 *
		 * @param string $wkmp_db_version Marketplace version saved in DB.
		 *
		 * @return void
		 */
		public function wkmp_migrate_endpoint_520_settings( $wkmp_db_version ) {
			if ( version_compare( '5.2.0', $wkmp_db_version, '>' ) ) { // If anyone migrates to 5.2.0 or higher version from 5.1.2 or any earlier then we migrate old endpoints settings otherwise it had been already migrated.
				global $wkmarketplace;

				$wc_endpoints = $wkmarketplace->wkmp_get_wc_registered_endpoints();

				$transactions        = get_option( '_wkmp_transaction_endpoint_name', esc_html__( 'Transaction', 'wk-marketplace' ) );
				$notifications       = get_option( '_wkmp_notification_endpoint_name', esc_html__( 'Notification', 'wk-marketplace' ) );
				$shop_followers      = get_option( '_wkmp_shop_follower_endpoint_name', esc_html__( 'Shop Follower', 'wk-marketplace' ) );
				$seller_all_products = get_option( '_wkmp_seller_product_endpoint_name', esc_html__( 'All Products', 'wk-marketplace' ) );
				$recent_products     = get_option( '_wkmp_store_endpoint_name', esc_html__( 'Recent Products', 'wk-marketplace' ) );

				$endpoints = array(
					'_wkmp_dashboard_endpoint'           => in_array( 'seller-dashboard', $wc_endpoints, true ) ? esc_attr( 'wkmp-dashboard' ) : esc_attr( 'seller-dashboard' ),
					'_wkmp_product_list_endpoint'        => in_array( 'seller-products', $wc_endpoints, true ) ? esc_attr( 'wkmp-products' ) : esc_attr( 'seller-products' ),
					'_wkmp_add_product_endpoint'         => in_array( 'seller-add-product', $wc_endpoints, true ) ? esc_attr( 'wkmp-add-product' ) : esc_attr( 'seller-add-product' ),
					'_wkmp_order_history_endpoint'       => in_array( 'seller-orders', $wc_endpoints, true ) ? esc_attr( 'wkmp-orders' ) : esc_attr( 'seller-orders' ),
					'_wkmp_transaction_endpoint'         => in_array( 'seller-transactions', $wc_endpoints, true ) ? esc_attr( 'wkmp-transactions' ) : esc_attr( 'seller-transactions' ),
					'_wkmp_shipping_endpoint'            => in_array( 'seller-shippings', $wc_endpoints, true ) ? esc_attr( 'wkmp-shippings' ) : esc_attr( 'seller-shippings' ),
					'_wkmp_profile_endpoint'             => in_array( 'seller-profile', $wc_endpoints, true ) ? esc_attr( 'wkmp-profile' ) : esc_attr( 'seller-profile' ),
					'_wkmp_notification_endpoint'        => in_array( 'seller-notifications', $wc_endpoints, true ) ? esc_attr( 'wkmp-notifications' ) : esc_attr( 'seller-notifications' ),
					'_wkmp_shop_follower_endpoint'       => in_array( 'seller-shop-followers', $wc_endpoints, true ) ? esc_attr( 'wkmp-shop-followers' ) : esc_attr( 'seller-shop-followers' ),
					'_wkmp_asktoadmin_endpoint'          => in_array( 'seller-asktoadmin', $wc_endpoints, true ) ? esc_attr( 'wkmp-asktoadmin' ) : esc_attr( 'seller-asktoadmin' ),
					'_wkmp_seller_product_endpoint'      => in_array( 'seller-all-products', $wc_endpoints, true ) ? esc_attr( 'wkmp-all-products' ) : esc_attr( 'seller-all-products' ),
					'_wkmp_store_endpoint'               => in_array( 'seller-recent-products', $wc_endpoints, true ) ? esc_attr( 'wkmp-recent-products' ) : esc_attr( 'seller-recent-products' ),
					'_wkmp_transaction_endpoint_name'    => ( __( 'Transaction', 'wk-marketplace' ) === $transactions ) ? esc_html__( 'Transactions', 'wk-marketplace' ) : $transactions,
					'_wkmp_notification_endpoint_name'   => ( __( 'Notification', 'wk-marketplace' ) === $notifications ) ? esc_html__( 'Notifications', 'wk-marketplace' ) : $notifications,
					'_wkmp_shop_follower_endpoint_name'  => ( __( 'Shop Follower', 'wk-marketplace' ) === $shop_followers ) ? esc_html__( 'Shop Followers', 'wk-marketplace' ) : $shop_followers,
					'_wkmp_seller_product_endpoint_name' => ( __( 'All Products', 'wk-marketplace' ) === $seller_all_products ) ? esc_html__( 'Products from Seller', 'wk-marketplace' ) : $seller_all_products,
					'_wkmp_store_endpoint_name'          => ( __( 'Recent Products', 'wk-marketplace' ) === $recent_products ) ? esc_html__( 'Sellers Recent Product', 'wk-marketplace' ) : $recent_products,
				);

				foreach ( $endpoints as $option_key => $option_value ) {
					update_option( $option_key, $option_value );
				}
			}
			update_option( '_wkmp_db_version', WKMP_LITE_DB_VERSION, true );
		}
	}
}
