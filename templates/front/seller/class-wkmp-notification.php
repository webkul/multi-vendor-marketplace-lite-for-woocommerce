<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Notification' ) ) {
	/**
	 * Seller notifications.
	 *
	 * Class WKMP_Notification
	 *
	 * @package WkMarketplace\Templates\Front\Seller
	 */
	class WKMP_Notification {
		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Notification $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Notification constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id = $seller_id;
			$this->db_obj    = Common\WKMP_Seller_Notification::get_instance();
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
		 * Display seller notification.
		 *
		 * @param int    $seller_id Seller Id.
		 * @param string $tab Notification tab.
		 * @param int    $page_no Page number.
		 *
		 * @return void
		 */
		public function wkmp_display_notifications( $seller_id, $tab = 'orders', $page_no = 1 ) {
			$this->seller_id = empty( $this->seller_id ) ? $seller_id : $this->seller_id;
			?>
			<ul class="wkmp_nav_tabs">
				<li><a data-id="#wkmp-orders-tab" data-current_tab="<?php echo ( 'orders' === $tab ) ? 'yes' : ''; ?>"><?php esc_html_e( 'Orders', 'wk-marketplace' ); ?></a></li>
				<li><a data-id="#wkmp-product-tab" data-current_tab="<?php echo ( 'products' === $tab ) ? 'yes' : ''; ?>"><?php esc_html_e( 'Product', 'wk-marketplace' ); ?></a></li>
				<li><a data-id="#wkmp-seller-tab" data-current_tab="<?php echo ( 'seller' === $tab ) ? 'yes' : ''; ?>"><?php esc_html_e( 'Seller', 'wk-marketplace' ); ?></a></li>
			</ul>

			<div class="wkmp_tab_content">
				<div id="wkmp-orders-tab" class="wkmp_tab_pane">
					<?php $this->wkmp_display_orders_notification( $tab, $page_no ); ?>
				</div>

				<div id="wkmp-product-tab" class="wkmp_tab_pane">
					<?php $this->wkmp_display_product_notification( $tab, $page_no ); ?>
				</div>

				<div id="wkmp-seller-tab" class="wkmp_tab_pane">
					<?php $this->wkmp_display_seller_notification( $tab, $page_no ); ?>
				</div>
			</div><!-- Tab content end here -->
			<?php
		}

		/**
		 * Display seller order notification.
		 *
		 * @param string $tab Notification tab.
		 * @param int    $page_no Page number.
		 *
		 * @return void
		 */
		private function wkmp_display_orders_notification( $tab, $page_no ) {
			$paged    = ( 'orders' === $tab ) ? $page_no : 1;
			$page_num = empty( $paged ) ? 1 : absint( $paged );
			$limit    = apply_filters( 'wkmp_front_per_page_order_notifications', 5 );
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;
			$orders   = array();

			$total_count    = $this->db_obj->wkmp_get_seller_notification_count( 'order' );
			$orders['data'] = $this->db_obj->wkmp_get_seller_notification_data( 'order', $offset, $limit );

			$this->wkmp_display_notification_html( $orders, 'order' );

			$pagination = array(
				'total_count' => $total_count,
				'page'        => $paged,
				'previous'    => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) . '/orders/page', $paged - 1 ),
				'next'        => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) . '/orders/page', $paged + 1 ),
			);

			$this->wkmp_display_pagination( $pagination );
		}

		/**
		 * Display seller product notification.
		 *
		 * @param string $tab Notification tab.
		 * @param int    $page_no Page number.
		 *
		 * @return void
		 */
		private function wkmp_display_product_notification( $tab, $page_no ) {
			$paged    = ( 'product' === $tab ) ? $page_no : 1;
			$page_num = empty( $paged ) ? 1 : absint( $paged );
			$limit    = apply_filters( 'wkmp_front_per_page_product_notifications', 5 );
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;
			$products = array();

			$total_count      = $this->db_obj->wkmp_get_seller_notification_count( 'product' );
			$products['data'] = $this->db_obj->wkmp_get_seller_notification_data( 'product', $offset, $limit );

			$this->wkmp_display_notification_html( $products, 'product' );

			$pagination = array(
				'total_count' => $total_count,
				'page'        => $paged,
				'previous'    => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) . '/products/page', $paged - 1 ),
				'next'        => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) . '/products/page', $paged + 1 ),
			);

			$this->wkmp_display_pagination( $pagination );
		}

		/**
		 * Display seller notification data.
		 *
		 * @param string $tab Notification tab.
		 * @param int    $page_no Page number.
		 *
		 * @return void
		 */
		private function wkmp_display_seller_notification( $tab, $page_no ) {
			global $wkmarketplace;

			$paged    = ( 'seller' === $tab ) ? $page_no : 1;
			$page_num = empty( $paged ) ? 1 : absint( $paged );
			$limit    = apply_filters( 'wkmp_front_per_page_seller_notifications', 5 );
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;
			$sellers  = array();

			$page_num = isset( $paged ) ? absint( $paged ) : 1;
			$limit    = 10;
			$offset   = ( 1 === $page_num ) ? 0 : ( $page_num - 1 ) * $limit;

			$total_count     = $this->db_obj->wkmp_get_seller_notification_count( 'seller' );
			$sellers['data'] = $this->db_obj->wkmp_get_seller_notification_data( 'seller', $offset, $limit );

			$this->wkmp_display_notification_html( $sellers, 'seller' );

			$pagination = array(
				'total_count' => $total_count,
				'page'        => $paged,
				'previous'    => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) . '/' . $wkmarketplace->seller_page_slug . '/page', $paged - 1 ),
				'next'        => wc_get_endpoint_url( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) . '/' . $wkmarketplace->seller_page_slug . '/page', $paged + 1 ),
			);

			$this->wkmp_display_pagination( $pagination );
		}

		/**
		 * Display seller notification HTML.
		 *
		 * @param array  $notifications Notifications.
		 * @param string $action Actions.
		 *
		 * @throws \Exception Throwing exception.
		 */
		private function wkmp_display_notification_html( $notifications, $action ) {
			global $wkmarketplace;
			$display = array();

			$db_obj = Common\WKMP_Seller_Notification::get_instance();

			foreach ( $notifications['data'] as $value ) {
				$context_id = isset( $value['context'] ) ? $value['context'] : 0;

				if ( 'product' === $action && empty( get_the_title( $context_id ) ) ) {
					continue;
				}

				if ( 'order' === $action && ( ! wc_get_order( $context_id ) instanceof \WC_Order ) ) {
					continue;
				}

				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );
				$interval  = $datetime1->diff( $datetime2 );

				$link = '#';

				$interval_days = '<strong>' . esc_html__( ' today', 'wk-marketplace' ) . '</strong>';

				if ( $interval->days > 0 ) {
					$interval_days = wp_sprintf( /* translators: %d: Interval days. */ _n( '%d <strong> day ago.</strong>', '%d <strong> days ago.</strong>', $interval->days, 'wk-marketplace' ), $interval->days );
				}

				if ( 'order' === $action ) {
					$url  = get_permalink() . get_option( '_wkmp_order_history_endpoint', 'sellers-orders' ) . '/' . $context_id;
					$link = '<a href="' . esc_url( $url ) . '" target="_blank"> #' . esc_html( $context_id ) . ' </a>';
				}

				if ( 'product' === $action ) {
					$url  = get_permalink() . get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' ) . '/' . $context_id;
					$link = '<a href="' . esc_url( $url ) . '" target="_blank"> #' . esc_html( get_the_title( $context_id ) ) . ' </a>';
				}

				$db_content        = empty( $value['content'] ) ? '' : $value['content'];
				$formatted_content = $db_obj->wkmp_get_formatted_notification_content( $db_content );

				$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s %2$s %3$s', 'wk-marketplace' ), $link, $formatted_content, $interval_days );

				if ( 'seller' === $action ) {
					$user         = get_user_by( 'ID', $context_id );
					$display_name = $wkmarketplace->wkmp_get_user_display_name( $context_id, $user );

					$content = sprintf( /* translators: %1$s: Reviewer name, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s from <strong> %2$s </strong> %3$s', 'wk-marketplace' ), $formatted_content, $display_name, $interval_days );
				}

				$display[] = array(
					'content' => $content,
				);

				$this->db_obj->wkmp_update_notification_read_status( $value );
			}
			?>
			<ul class="mp-notification-list">
				<?php if ( $display ) { ?>
					<?php foreach ( $display as $value ) { ?>
						<li class="notification-link">
						<?php
						echo wp_kses(
							html_entity_decode( $value['content'], ENT_QUOTES, 'UTF-8' ),
							array(
								'li'     => array(
									'class' => array(),
								),
								'strong' => array(),
								'a'      => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
						</li>
						<?php
					}
				} else {
					esc_html_e( 'No data Found!', 'wk-marketplace' );
				}
				?>
			</ul>
			<?php
		}

		/**
		 * Display notification pagination
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		private function wkmp_display_pagination( $data ) {
			if ( 1 < $data['total_count'] ) {
				?>
				<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination wallet-pagination" style="margin-top:10px;">

					<?php if ( 1 !== $data['page'] && $data['page'] > 1 ) { ?>
						<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( $data['previous'] ); ?>"><?php esc_html_e( 'Previous', 'wk-marketplace' ); ?></a>
					<?php } ?>

					<?php if ( ceil( $data['total_count'] / 10 ) > $data['page'] ) { ?>
						<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $data['next'] ); ?>"><?php esc_html_e( 'Next', 'wk-marketplace' ); ?></a>
					<?php } ?>
				</div>
				<?php
			}
		}
	}
}
