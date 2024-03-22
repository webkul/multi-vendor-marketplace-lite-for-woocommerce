<?php
/**
 * Admin Template Functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Seller_Templates_Handler' ) ) {
	/**
	 * Admin Seller Handler class.
	 *
	 * Class WKMP_Seller_Templates_Handler
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Templates_Handler {
		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Templates_Handler constructor.
		 */
		public function __construct() {
			add_action( 'wkmp_seller_details_content', array( $this, 'wkmp_seller_details_content' ) );
			add_action( 'wkmp_seller_orders_content', array( $this, 'wkmp_seller_orders_content' ) );
			add_action( 'wkmp_seller_transactions_content', array( $this, 'wkmp_seller_transactions_content' ) );
			add_action( 'wkmp_seller_commission_content', array( $this, 'wkmp_seller_commission_content' ) );
			add_action( 'wkmp_seller_assign_category_content', array( $this, 'wkmp_seller_assign_category_content' ) );

			$this->wkmp_manage_sellers();
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
		 * Manage seller tabs and seller list.
		 *
		 * @return void
		 */
		public function wkmp_manage_sellers() {
			$tab_action = \WK_Caching::wk_get_request_data( 'tab-action' );
			$seller_id  = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );

			if ( 'manage' === $tab_action && ! empty( $seller_id ) ) {
				$this->wkmp_display_seller_tabs();
			} elseif ( 'delete' === $tab_action && ! empty( $seller_id ) && $seller_id > 1 ) {
				$obj = Admin\WKMP_Seller_Data::get_instance();
				$obj->wkmp_delete_seller( $seller_id );

				$page_name = \WK_Caching::wk_get_request_data( 'page' );
				$url       = 'admin.php?page=' . $page_name . '&success=1';

				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			} else {
				$this->wkmp_display_seller_list();
			}
		}

		/**
		 * Display seller manage tabs.
		 *
		 * @return void
		 */
		private function wkmp_display_seller_tabs() {
			global $wkmarketplace;
			$seller_id   = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			$seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );

			$config_tabs = array(
				'details'         => esc_html__( 'Details', 'wk-marketplace' ),
				'orders'          => esc_html__( 'Orders', 'wk-marketplace' ),
				'transactions'    => esc_html__( 'Transactions', 'wk-marketplace' ),
				'commission'      => esc_html__( 'Commission', 'wk-marketplace' ),
				'assign_category' => esc_html__( 'Misc. Settings', 'wk-marketplace' ),
			);

			$config_tabs = apply_filters( 'wkmp_admin_seller_tabs', $config_tabs );

			$page_name   = \WK_Caching::wk_get_request_data( 'page' );
			$current_tab = \WK_Caching::wk_get_request_data( 'tab' );
			$tab_action  = \WK_Caching::wk_get_request_data( 'tab-action' );

			$current_tab = empty( $current_tab ) ? 'details' : $current_tab;
			$url         = admin_url( 'admin.php?page=' . $page_name . '&tab-action=' . $tab_action . '&seller-id=' . $seller_id );
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php echo wp_sprintf( /* translators: %s: Seller display name. */ esc_html__( 'Seller - %s', 'wk-marketplace' ), esc_html( $wkmarketplace->wkmp_get_user_display_name( $seller_id ) ) ); ?></h1>
				<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=wk-marketplace' ) ); ?>"><?php esc_html_e( 'Back', 'wk-marketplace' ); ?></a>
				<nav class="nav-tab-wrapper wkmp-admin-seller-list-manage-nav">
					<?php
					foreach ( $config_tabs as $name => $label ) {
						$tab_class = ( $current_tab === $name ) ? 'nav-tab-active' : '';
						?>
						<a href="<?php echo esc_url( $url ) . '&tab=' . esc_attr( $name ); ?>" class="nav-tab <?php echo esc_attr( $tab_class ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php } ?>
				</nav>
				<?php do_action( 'wkmp_seller_' . esc_attr( $current_tab ) . '_content' ); ?>
			</div>
			<?php
		}

		/**
		 * Display Seller list.
		 *
		 * @return void
		 */
		private function wkmp_display_seller_list() {
			$obj     = WKMP_Admin_Seller_List::get_instance();
			$wk_page = \WK_Caching::wk_get_request_data( 'page' );
			$success = \WK_Caching::wk_get_request_data( 'success', array( 'filter' => 'int' ) );
			?>
			<div class="wrap wkmp-admin-seller-list-wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Sellers', 'wk-marketplace' ); ?></h1>
				<?php do_action( 'wkmp_after_seller_list_page_title' ); ?>
				<p class="description"><?php esc_html_e( 'List of Shop Vendors associated with this Marketplace.', 'wk-marketplace' ); ?></p>

				<?php
				if ( in_array( $success, array( 1, 2, 404 ), true ) ) {
					$message      = ( 2 === $success ) ? esc_html__( 'Kindly approve the seller first.', 'wk-marketplace' ) : esc_html__( 'Please select atleast one Seller.', 'wk-marketplace' );
					$notice_class = 'notice-error';
					if ( 1 === intval( $success ) ) {
						$message      = esc_html__( 'Seller deleted successfully.', 'wk-marketplace' );
						$notice_class = 'notice-success';
					}
					?>
					<div class="notice wkmp-admin-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
						<p><?php echo esc_html( $message ); ?></p>
					</div>
					<?php
				}
				?>
				<form method="GET">
					<input type="hidden" name="page" value="<?php echo esc_attr( $wk_page ); ?>"/>
					<?php
					$obj->prepare_items();
					?>
					<div class="wkmp-seller-search-wrap">
					<?php $obj->search_box( esc_html__( 'Search Seller', 'wk-marketplace' ), 'search-id' ); ?>
					</div>
					<?php
					$obj->display();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Display seller details in manage section.
		 *
		 * @return void
		 */
		public function wkmp_seller_details_content() {
			$seller_id = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			new WKMP_Seller_Profile( $seller_id );
		}

		/**
		 * Display seller orders in manage section.
		 *
		 * @return void
		 */
		public function wkmp_seller_orders_content() {
			$obj_orders = WKMP_Seller_Order_List::get_instance();

			$page_name   = \WK_Caching::wk_get_request_data( 'page' );
			$current_tab = \WK_Caching::wk_get_request_data( 'tab' );
			$current_tab = empty( $current_tab ) ? 'details' : $current_tab;

			$tab_action = \WK_Caching::wk_get_request_data( 'tab-action' );
			$seller_id  = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			$success    = \WK_Caching::wk_get_request_data( 'success', array( 'filter' => 'int' ) );

			if ( ! is_null( $success ) ) {
				$message      = esc_html__( 'Please select atleast one order.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( $success ) {
					$message      = esc_html__( 'Order status for selected orders has been successfully updated.', 'wk-marketplace' );
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice wkmp-admin-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<div class="notice wkmp-admin-notice is-dismissible notice-success wkmp-hide"></div>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
				<input type="hidden" name="tab-action" value="<?php echo esc_attr( $tab_action ); ?>"/>
				<input type="hidden" name="seller-id" value="<?php echo esc_attr( $seller_id ); ?>"/>
				<input type="hidden" name="tab" value="<?php echo esc_attr( $current_tab ); ?>"/>
				<?php
				$obj_orders->prepare_items();
				$obj_orders->display();
				?>
			</form>
			<?php
		}

		/**
		 * Display seller transactions in manage section.
		 *
		 * @return void
		 */
		public function wkmp_seller_transactions_content() {
			$page_name   = \WK_Caching::wk_get_request_data( 'page' );
			$current_tab = \WK_Caching::wk_get_request_data( 'tab' );
			$current_tab = empty( $current_tab ) ? 'details' : $current_tab;

			$tab_action = \WK_Caching::wk_get_request_data( 'tab-action' );
			$seller_id  = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			$id         = \WK_Caching::wk_get_request_data( 'id', array( 'filter' => 'int' ) );

			if ( 'transactions' === $current_tab && $id ) {
				$obj = WKMP_Seller_Transaction_View::get_instance();
				$obj->wkmp_display_seller_transaction_view( $id );
			} else {
				$obj_transaction = WKMP_Seller_Transaction_List::get_instance();
				?>
				<form method="get">

					<input type="hidden" name="page" value="<?php echo esc_attr( $page_name ); ?>"/>
					<input type="hidden" name="tab-action" value="<?php echo esc_attr( $tab_action ); ?>"/>
					<input type="hidden" name="seller-id" value="<?php echo esc_attr( $seller_id ); ?>"/>
					<input type="hidden" name="tab" value="<?php echo esc_attr( $current_tab ); ?>"/>
					<?php
					$obj_transaction->prepare_items();
					$obj_transaction->display();
					?>
				</form>
				<?php
			}
		}

		/**
		 * Display seller commissions in manage section.
		 *
		 * @return void
		 */
		public function wkmp_seller_commission_content() {
			$seller_id = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			new WKMP_Seller_Commission( $seller_id );
		}

		/**
		 * Display seller assigned category in manage section.
		 *
		 * @return void
		 */
		public function wkmp_seller_assign_category_content() {
			$seller_id = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			new WKMP_Seller_Assign_Category( $seller_id );
		}


		/**
		 * Displays restricted search box to allow pro feature.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			$search = \WK_Caching::wk_get_request_data( 's' );

			if ( empty( $search ) && ! $this->has_items() ) {
				return;
			}

			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$template_functions->wkmp_show_restricted_search_box( $text, $input_id );
		}
	}
}
