<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

use WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.


if ( ! class_exists( 'WKMP_Dashboard' ) ) {

	/**
	 * Seller products class
	 */
	class WKMP_Dashboard {
		/**
		 * Dashboard DB Object.
		 *
		 * @var Front\WKMP_Dashboard_Queries $dashboard_db_obj Dashboard DB Object.
		 */
		private $dashboard_db_obj;

		/**
		 * Marketplace class object.
		 *
		 * @var object $marketplace Marketplace class object.
		 */
		private $marketplace;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->dashboard_db_obj = Front\WKMP_Dashboard_Queries::get_instance();
			$this->marketplace      = $wkmarketplace;
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

		/**
		 * Dashboard Page.
		 */
		public function wkmp_dashboard_page() {
			$user_id     = get_current_user_id();
			$wkmp_notice = get_user_meta( $user_id, 'wkmp_show_register_notice', true );
			?>
			<div class="mp-dashboard-wrapper">
				<?php
				if ( ! empty( $wkmp_notice ) ) {
					wc_print_notice( esc_html( $wkmp_notice ) );
					delete_user_meta( $user_id, 'wkmp_show_register_notice' );
				}
				$sales_data  = $this->dashboard_db_obj->wkmp_get_sale_stats( $user_id );
				$filter_data = array(
					'user_id'  => $user_id,
					'per_page' => '-1',
				);

				$seller_orders = $this->dashboard_db_obj->wkmp_get_order_item_id( $filter_data );

				$this->wkmp_dashboard_summary_section( $sales_data, $user_id );
				$this->wkmp_dashboard_sale_order_history( $seller_orders, $user_id );
				$this->wkmp_dashboard_order_product_section( $seller_orders, $user_id );
				$this->wkmp_top_billing_country( $seller_orders, $user_id );
				$this->wkmp_dashboard_recent_order_section( $seller_orders, $user_id );
				?>
			</div>
			<?php
		}

		/**
		 * Store summary section function.
		 *
		 * @param array $sales_data Sales data.
		 * @param int   $user_id User id.
		 *
		 * @return void
		 */
		public function wkmp_dashboard_summary_section( $sales_data, $user_id ) {
			new WKMP_Dashboard_Summary( $this->dashboard_db_obj, $this->marketplace, $sales_data, $user_id );
		}

		/**
		 * Sale Order History.
		 *
		 * @param array $seller_orders Seller Orders.
		 * @param int   $user_id User id.
		 *
		 * @return void
		 */
		private function wkmp_dashboard_sale_order_history( $seller_orders, $user_id ) {
			new WKMP_Dashboard_Sale_Order( $this->dashboard_db_obj, $this->marketplace, $seller_orders, $user_id );
		}

		/**
		 * Order status and product selling status.
		 *
		 * @param array $seller_orders Seller orders.
		 * @param int   $seller_id Seller id.
		 *
		 * @return void
		 */
		private function wkmp_dashboard_order_product_section( $seller_orders, $seller_id ) {
			new WKMP_Dashboard_Order_Product( $this->dashboard_db_obj, $this->marketplace, $seller_orders, $seller_id );
		}

		/**
		 * Top billing country.
		 *
		 * @param array $seller_orders Seller orders.
		 * @param int   $seller_id Seller id.
		 *
		 * @return void
		 */
		private function wkmp_top_billing_country( $seller_orders, $seller_id ) {
			new WKMP_Dashboard_Top_Billing_Country( $this->dashboard_db_obj, $this->marketplace, $seller_orders, $seller_id );
		}

		/**
		 * Store recent orders section.
		 *
		 * @param array $seller_orders Seller orders.
		 * @param int   $seller_id Seller id.
		 */
		public function wkmp_dashboard_recent_order_section( $seller_orders, $seller_id ) {
			new WKMP_Dashboard_Recent_Order( $this->dashboard_db_obj, $this->marketplace, $seller_orders, $seller_id );
		}
	}
}
