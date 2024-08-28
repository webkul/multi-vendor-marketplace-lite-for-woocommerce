<?php
/**
 * Seller template functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Templates\Front\Seller\Orders\WKMP_Orders;
use WkMarketplace\Templates\Front\Seller\Product\WKMP_Product_List;

if ( ! class_exists( 'WKMP_Seller_Template_Functions' ) ) {
	/**
	 * Seller template functions class.
	 */
	class WKMP_Seller_Template_Functions {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id = '';

		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * @param int $seller_id Seller Id.
		 */
		public function __construct( $seller_id ) {
			$this->seller_id = empty( $seller_id ) ? get_current_user_id() : $seller_id;
		}

		/**
		 * Set seller Id.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function set_seller_id( $seller_id ) {
			$this->seller_id = $seller_id;
		}

		/**
		 * Get seller Id.
		 *
		 * @return int
		 */
		public function get_seller_id() {
			return empty( $this->seller_id ) ? get_current_user_id() : intval( $this->seller_id );
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @param int $seller_id Seller Id.
		 *
		 * @return object
		 */
		public static function get_instance( $seller_id = 0 ) {
			if ( ! static::$instance ) {
				static::$instance = new self( $seller_id );
			}
			return static::$instance;
		}

		/**
		 * Callback method for seller Dashboard.
		 *
		 * @return void
		 */
		public function wkmp_seller_dashboard_endpoint_content() {
			$dashboard = Dashboard\WKMP_Dashboard::get_instance();
			$dashboard->wkmp_dashboard_page();
		}

		/**
		 * Callback method for seller Product List.
		 *
		 * @return void
		 */
		public function wkmp_product_list_endpoint_content() {
			global $wp;
			$query_vars       = $wp->query_vars;
			$product_endpoint = get_option( '_wkmp_product_list_endpoint', 'sellers-products' );
			$query_args       = empty( $query_vars[ $product_endpoint ] ) ? 0 : $query_vars[ $product_endpoint ];

			$show_product_list_table = apply_filters( 'wkmp_is_show_product_list_table', true, $query_args );
			do_action( 'wkmp_before_product_list_table', $query_args, $this->seller_id );

			if ( $show_product_list_table ) {
				$page_no = 1;
				$filter  = '';

				if ( ! empty( $query_args ) ) {
					$args_array = explode( '/', $query_args );

					if ( is_array( $args_array ) && count( $args_array ) >= 2 ) {
						$filter  = ( 'filter' === $args_array[0] ) ? $args_array[1] : $filter;
						$page_no = ( 'page' === $args_array[0] ) ? $args_array[1] : $page_no;

						$page_no = ( ! empty( $filter ) && 4 === count( $args_array ) && 'page' === $args_array[2] ) ? $args_array[3] : $page_no;
					}
				}

				$product_list = WKMP_Product_List::get_instance();
				$product_list->wkmp_product_list( $this->seller_id, $page_no, $filter );
			}
		}

		/**
		 * Callback method for seller Product Form.
		 *
		 * @return void
		 */
		public function wkmp_add_product_endpoint_content() {
			$product_form = Product\WKMP_Product_Form::get_instance();
			$product_form->wkmp_add_product_form( $this->seller_id );
		}

		/**
		 * Callback method for seller Product Form.
		 *
		 * @return void
		 */
		public function wkmp_edit_product_endpoint_content() {
			$product_form = Product\WKMP_Product_Form::get_instance();
			$product_form->wkmp_edit_product_form( $this->seller_id );
		}

		/**
		 * Callback method for seller Order History.
		 *
		 * @return void
		 */
		public function wkmp_order_history_endpoint_content() {
			global $wp;
			$wkmp_orders = WKMP_Orders::get_instance();

			$query_vars     = $wp->query_vars;
			$order_endpoint = get_option( '_wkmp_order_history_endpoint', 'sellers-orders' );
			$query_args     = empty( $query_vars[ $order_endpoint ] ) ? 0 : $query_vars[ $order_endpoint ];
			$order_id       = is_numeric( $query_args ) ? intval( $query_args ) : 0;

			if ( empty( $order_id ) ) {
				$args_array = explode( '/', $query_args );
				$page_no    = ( is_array( $args_array ) && 2 === count( $args_array ) && 'page' === $args_array[0] ) ? $args_array[1] : 1;
				$wkmp_orders->wkmp_order_list( $this->seller_id, $page_no );
			} else {
				$wkmp_orders->wkmp_order_views( $this->seller_id, $order_id );
			}
		}

		/**
		 * Callback method for seller order invoice.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_seller_order_invoice( $seller_id ) {
			$wkmp_orders = WKMP_Orders::get_instance();
			$wkmp_orders->wkmp_order_invoice( $seller_id );
		}

		/**
		 * Callback method for seller Transactions.
		 *
		 * @return void
		 */
		public function wkmp_transactions_content() {
			global $wp;
			$query_vars           = $wp->query_vars;
			$transaction_endpoint = get_option( '_wkmp_transaction_endpoint', 'sellers-transactions' );
			$query_args           = empty( $query_vars[ $transaction_endpoint ] ) ? 0 : $query_vars[ $transaction_endpoint ];
			$page_no              = 1;

			$transaction_id = is_numeric( $query_args ) ? intval( $query_args ) : 0;

			if ( empty( $transaction_id ) && ! empty( $query_args ) ) {
				$args_array = explode( '/', $query_args );
				$page_no    = ( is_array( $args_array ) && 2 === count( $args_array ) && 'page' === $args_array[0] ) ? $args_array[1] : 1;
			}

			$transaction = Transaction\WKMP_Transactions::get_instance();
			$transaction->wkmp_transaction_list( $this->seller_id, $transaction_id, $page_no );
		}

		/**
		 * Callback method for seller Profile.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_content() {
			global $wp;
			$profile = get_option( '_wkmp_profile_endpoint', 'seller-profile' );
			$action  = empty( $wp->query_vars[ $profile ] ) ? '' : $wp->query_vars[ $profile ];

			if ( 'view' === $action ) {
				$store = Store\WKMP_Seller_Store_Info::get_instance();
				$store->wkmp_display_seller_store( $this->seller_id );
			} else {
				$profile = Profile\WKMP_Profile_Edit::get_instance();
				$profile->wkmp_seller_profile_form( $this->seller_id );
			}
		}

		/**
		 * Callback method for seller Notification.
		 *
		 * @return void
		 */
		public function wkmp_seller_notifications_content() {
			global $wp;
			$query_vars = $wp->query_vars;
			$endpoint   = get_option( '_wkmp_notification_endpoint', 'seller-notifications' );
			$query_args = empty( $query_vars[ $endpoint ] ) ? 0 : $query_vars[ $endpoint ];
			$page_no    = 1;
			$tab        = 'orders';

			if ( ! empty( $query_args ) ) {
				$args_array = explode( '/', $query_args );
				$tab        = ( is_array( $args_array ) && 3 === count( $args_array ) ) ? $args_array[0] : $tab;
				$page_no    = ( is_array( $args_array ) && 3 === count( $args_array ) && 'page' === $args_array[1] ) ? $args_array[2] : $page_no;
			}

			$notification = WKMP_Notification::get_instance();
			$notification->wkmp_display_notifications( $this->seller_id, $tab, $page_no );
		}

		/**
		 * Callback method for seller Shop Follower
		 *
		 * @return void
		 */
		public function wkmp_shop_followers_content() {
			$followers = WKMP_Shop_Follower::get_instance();
			$followers->wkmp_display_shop_follower( $this->seller_id );
		}

		/**
		 * Callback method for seller Ask To admin
		 *
		 * @return void
		 */
		public function wkmp_ask_admin_content() {
			$ask_admin = WKMP_Ask_To_Admin::get_instance();
			$ask_admin->wkmp_seller_queries_list( $this->seller_id );
		}

		/**
		 * Callback method for seller profile info
		 * This method is call by default
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_info() {
			$profile = Profile\WKMP_Profile_Info::get_instance();
			$profile->wkmp_seller_profile_section( $this->seller_id );
		}

		/**
		 * Show seller store info.
		 *
		 * @return void
		 */
		public function wkmp_seller_store_info() {
			$store = Store\WKMP_Seller_Store_Info::get_instance();
			$store->wkmp_display_seller_store( $this->seller_id );
		}

		/**
		 * Show seller product collections.
		 *
		 * @return void
		 */
		public function wkmp_seller_products_info() {
			$store = Store\WKMP_Seller_Store_Info::get_instance();
			$store->wkmp_seller_store_collection( $this->seller_id );
		}

		/**
		 * Show seller add review page.
		 *
		 * @return void
		 */
		public function wkmp_seller_add_feedback() {
			$store = Store\WKMP_Seller_Store_Info::get_instance();
			$store->wkmp_seller_add_feedback_template( $this->seller_id );
		}

		/**
		 * Show seller all reviews page.
		 *
		 * @return void
		 */
		public function wkmp_seller_all_feedback() {
			$store = Store\WKMP_Seller_Store_Info::get_instance();
			$store->wkmp_seller_all_feedback( $this->seller_id );
		}
	}
}
