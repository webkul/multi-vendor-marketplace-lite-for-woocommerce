<?php
/**
 * Admin template Functions
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @since 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Notification;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Notification_Templates_Handler' ) ) {
	/**
	 * Class WKMP_Notification_Templates_Handler.
	 *
	 * @package WkMarketplace\Templates\Admin\Notification
	 */
	class WKMP_Notification_Templates_Handler {
		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Notification
		 */
		private $db_obj;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Notification_Templates_Handler constructor.
		 */
		public function __construct() {
			$this->db_obj = Common\WKMP_Seller_Notification::get_instance();

			add_action( 'wkmp_notification_orders_content', array( $this, 'wkmp_notification_orders_content' ) );
			add_action( 'wkmp_notification_product_content', array( $this, 'wkmp_notification_product_content' ) );
			add_action( 'wkmp_notification_seller_content', array( $this, 'wkmp_notification_seller_content' ) );

			$this->wkmp_notification_templates();
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
		 * Display notifications tabs
		 */
		public function wkmp_notification_templates() {
			$config_tabs = array(
				'orders'  => esc_html__( 'Orders', 'wk-marketplace' ),
				'product' => esc_html__( 'Product', 'wk-marketplace' ),
				'seller'  => esc_html__( 'Seller', 'wk-marketplace' ),
			);

			$config_tabs = apply_filters( 'wkmp_admin_notification_tabs', $config_tabs );

			$wk_page     = \WK_Caching::wk_get_request_data( 'page' );
			$current_tab = \WK_Caching::wk_get_request_data( 'tab' );
			$current_tab = empty( $current_tab ) ? 'orders' : $current_tab;

			$url = admin_url( 'admin.php?page=' . $wk_page );
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Notifications', 'wk-marketplace' ); ?></h1>
				<nav class="nav-tab-wrapper wkmp-admin-seller-list-manage-nav">
					<?php
					foreach ( $config_tabs as $name => $label ) {
						$tab_class = ( $current_tab === $name ) ? 'nav-tab-active' : '';
						?>
						<a href="<?php echo esc_url( $url ) . '&tab=' . esc_attr( $name ); ?>" class="nav-tab <?php echo esc_attr( $tab_class ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php } ?>
				</nav>
				<?php do_action( 'wkmp_notification_' . esc_attr( $current_tab ) . '_content' ); ?>
			</div>
			<?php
		}

		/**
		 * Call back method for orders content
		 */
		public function wkmp_notification_orders_content() {
			new WKMP_Notification_Orders( $this->db_obj );
		}

		/**
		 *  Call back methods for product content
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_notification_product_content() {
			$product_notification = WKMP_Notification_Product::get_instance();
			$product_notification->display_notification_content( $this->db_obj );
		}

		/**
		 *  Call back methods for seller content
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_notification_seller_content() {
			$product_notification = WKMP_Notification_Seller::get_instance();
			$product_notification->display_notification_content( $this->db_obj );
		}
	}
}
