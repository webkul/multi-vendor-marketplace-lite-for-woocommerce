<?php
/**
 * Admin template Functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Templates\Admin\Seller;
use WkMarketplace\Templates\Admin\Product;
use WkMarketplace\Templates\Admin\Notification;
use WkMarketplace\Templates\Admin\Feedback;
use WkMarketplace\Templates\Admin\Queries;
use WkMarketplace\Templates\Admin\Settings;
use WkMarketplace\Helper\Admin;

use WkMarketplace\Helper as Form;

if ( ! class_exists( 'WKMP_Admin_Template_Functions' ) ) {
	/**
	 * Admin template class
	 */
	class WKMP_Admin_Template_Functions {
		/**
		 * Form field builder
		 *
		 * @var object
		 */
		protected $form_helper;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Template_Functions constructor.
		 */
		public function __construct() {
			$this->form_helper = Form\WKMP_Form_Field_Builder::get_instance();
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
		 * Marketplace Sellers
		 *
		 * @return void
		 */
		public function wkmp_marketplace_sellers() {
			Seller\WKMP_Seller_Templates_Handler::get_instance();
		}

		/**
		 * Notification callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_products() {
			$obj_product = Product\WKMP_Admin_Product::get_instance();
			$wk_page     = \WK_Caching::wk_get_request_data( 'page' );
			$success     = \WK_Caching::wk_get_request_data( 'success', array( 'filter' => 'int' ) );
			?>
			<h1><?php esc_html_e( 'Product List', 'wk-marketplace' ); ?></h1>

			<?php
			if ( in_array( $success, array( 1, 2, 404 ), true ) ) {
				$message      = esc_html__( 'Please select atleast one product.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( 404 !== $success ) {
					$message      = ( $success > 1 ) ? esc_html__( 'Product assigned successfully.', 'wk-marketplace' ) : esc_html__( 'Product trashed successfully. You can restore them from woocommerce product page.', 'wk-marketplace' );
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice wkmp-admin-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $wk_page ); ?>"/>
				<?php
				$obj_product->prepare_items();
				$obj_product->search_box( esc_html__( 'Search Products', 'wk-marketplace' ), 'search-box-id' );
				$obj_product->display();
				?>
			</form>
			<?php
		}

		/**
		 * Notification callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_notifications() {
			Notification\WKMP_Notification_Templates_Handler::get_instance();
		}

		/**
		 * Reviews & rating callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_feedback() {
			$obj_feedback = Feedback\WKMP_Admin_Feedback::get_instance();

			$wk_page = \WK_Caching::wk_get_request_data( 'page' );
			$success = \WK_Caching::wk_get_request_data( 'success', array( 'filter' => 'int' ) );
			?>
			<h1><?php esc_html_e( 'Manage Feedback', 'wk-marketplace' ); ?></h1>
			<?php
			if ( in_array( $success, array( 1, 2, 3, 404 ), true ) ) {
				$message      = esc_html__( 'Please select atleast one feedback.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( 404 !== $success ) {
					$message      = ( $success > 1 ) ? esc_html__( 'Feedback has been disapproved successfully.', 'wk-marketplace' ) : esc_html__( 'Feedback has been approved successfully.', 'wk-marketplace' );
					$message      = ( $success > 2 ) ? esc_html__( 'Feedback has been deleted successfully.', 'wk-marketplace' ) : $message;
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice wkmp-admin-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
			}
			?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $wk_page ); ?>"/>
				<?php
				$obj_feedback->prepare_items();
				$obj_feedback->search_box( esc_html__( 'Search', 'wk-marketplace' ), 'search-box-id' );
				$obj_feedback->display();
				?>
			</form>
			<?php
		}

		/**
		 * Queries callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_queries() {
			$obj_queries = Queries\WKMP_Admin_Queries::get_instance();
			$wk_page     = \WK_Caching::wk_get_request_data( 'page' );

			$transient = get_transient( 'wkmp_deleted_queries_transient' );
			?>
			<h1><?php esc_html_e( 'Queries List', 'wk-marketplace' ); ?></h1>
			<?php
			if ( ! empty( $transient ) && in_array( intval( $transient['success'] ), array( 1, 404 ), true ) ) {
				$message      = esc_html__( 'Please select at least one query.', 'wk-marketplace' );
				$notice_class = 'notice-error';
				if ( 404 !== intval( $transient['success'] ) ) {
					$message      = esc_html__( 'Seller queries has been deleted successfully.', 'wk-marketplace' );
					$notice_class = 'notice-success';
				}
				?>
				<div class="notice wkmp-admin-notice is-dismissible <?php echo esc_attr( $notice_class ); ?>">
					<p><?php echo esc_html( $message ); ?></p>
				</div>
				<?php
				delete_transient( 'wkmp_deleted_queries_transient' );
			}
			?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $wk_page ); ?>"/>
				<?php
				$obj_queries->prepare_items();
				$obj_queries->search_box( esc_html__( 'Search', 'wk-marketplace' ), 'search-box-id' );
				$obj_queries->display();
				?>
			</form>
			<?php
		}

		/**
		 * Marketplace settings callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_settings() {
			new Settings\WKMP_Setting_Templates_Handler();
		}

		/**
		 * Marketplace extensions callback
		 *
		 * @return void
		 */
		public function wkmp_marketplace_extensions() {
			echo '<webkul-extensions></webkul-extensions>';
		}

		/**
		 * Marketplace support and services menu.
		 *
		 * @return void
		 */
		public function wkmp_marketplace_support_services() {
			echo '<wk-area></wk-area>';
		}

		/**
		 * Marketplace support and services menu.
		 *
		 * @return void
		 */
		public function wkmp_show_pro_upgrade_popup() {
			?>
			<div class="wkmp-popup-wrap">
				<div class="wkmp-hide wkmp-popup-overlay"></div>
				<div class="wkmp_show_pro_upgrade_poupup wkmp-hide">
					<div class="upgrade-popup-content wkmp_pro_upgrade_content">
						<span class="upgrade-close wkmp_pro_upgrade_popup_close"></span>
						<div class="upgrade-logo">
							<img src="<?php echo esc_url( WKMP_LITE_PLUGIN_URL . 'assets/images/wkmp-diamong.png' ); ?>" alt="<?php esc_attr_e( 'Marketplace Pro', 'wk-marketplace' ); ?>">
						</div>
						<h2 class="upgrade-title"><?php esc_html_e( 'Upgrade to Pro', 'wk-marketplace' ); ?></h2>
						<p class="upgrade-content"><?php esc_html_e( 'Please upgrade to pro to explore all these awesome features and enable to support our 50+ Marketplace addons.', 'wk-marketplace' ); ?></p>
						<div class="upgrade-btns">
							<a target="_blank" href="<?php echo esc_url( WKMP_PRO_MODULE_URL ); ?>" class="upgr-btn"><?php esc_html_e( 'Upgrade to Pro', 'wk-marketplace' ); ?></a>
							<a target="_blank" href="<?php echo esc_url( WKMP_PRO_DEMO_URL ); ?>" class="upgr-btn btn-light"><?php esc_html_e( 'Try Demo', 'wk-marketplace' ); ?></a>
						</div>
						<div class="upgrade-watermark">
							<img src="<?php echo esc_url( WKMP_LITE_PLUGIN_URL . 'assets/images/wk-logo.png' ); ?>" alt="">
						</div>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * Displays restricted search box to allow pro feature.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 * @param string $placeholder Search input placeholder.
		 */
		public function wkmp_show_restricted_search_box( $text, $input_id, $placeholder = '' ) {
			$input_id = $input_id . '-search-input';

			$orderby        = \WK_Caching::wk_get_request_data( 'orderby' );
			$sort_order     = \WK_Caching::wk_get_request_data( 'order' );
			$post_mime_type = \WK_Caching::wk_get_request_data( 'post_mime_type' );
			$detached       = \WK_Caching::wk_get_request_data( 'detached' );

			if ( ! empty( $orderby ) ) {
				echo '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '" />';
			}
			if ( ! empty( $sort_order ) ) {
				echo '<input type="hidden" name="order" value="' . esc_attr( $sort_order ) . '" />';
			}
			if ( ! empty( $post_mime_type ) ) {
				echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $post_mime_type ) . '" />';
			}
			if ( ! empty( $detached ) ) {
				echo '<input type="hidden" name="detached" value="' . esc_attr( $detached ) . '" />';
			}

			$button_attrs = apply_filters(
				'wkmp_seller_save_button_attributes',
				array(
					'disabled' => true,
					'id'       => 'search-submit',
				)
			);
			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
				<input placeholder="<?php echo esc_attr( $placeholder ); ?>" type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php
			submit_button( $text, '', '', false, $button_attrs );
			empty( $button_attrs['disabled'] ) ? '' : $this->wkmp_show_upgrade_lock_icon();
			?>
			</p>
			<?php
		}

		/**
		 * Show upgrade lock icon.
		 *
		 * @return void
		 */
		public function wkmp_show_upgrade_lock_icon() {
			echo '<i title="' . esc_attr__( 'Upgrade to Pro', 'wk-marketplace' ) . '" class="wkmp_pro_lock"></i>';
		}

		/**
		 * Disabling sellers that are not allowed in lite.
		 *
		 * @return void
		 */
		public function wkmp_maybe_disable_sellers() {
			global $wkmarketplace;
			$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();

			$log_data = array( 'pro_disabled' => $pro_disabled );

			if ( $pro_disabled ) {
				$last_check = get_option( 'wkmp_last_pro_check', 0 );

				$current_ts = strtotime( gmdate( 'Y-m-d H:i:s' ) );
				$next_check = intval( $last_check ) + intval( 12 * 60 * 60 ); // + 12 Hours

				$log_data['last_check'] = $last_check;
				$log_data['current_ts'] = $current_ts;
				$log_data['next_check'] = $next_check;

				if ( $current_ts > $next_check ) {
					$seller_db_obj = Admin\WKMP_Seller_Data::get_instance();
					$seller_db_obj = Admin\WKMP_Seller_Data::get_instance();
					$res           = $seller_db_obj->wkmp_disable_extra_sellers();
					update_option( 'wkmp_last_pro_check', $current_ts );
					$log_data['result'] = $res;
				}
			} else {
				delete_option( 'wkmp_last_pro_check' );
			}
		}
	}
}
