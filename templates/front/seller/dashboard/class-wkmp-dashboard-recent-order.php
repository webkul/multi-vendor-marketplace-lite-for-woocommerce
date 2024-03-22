<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WKMP_Dashboard_Recent_Order' ) ) {
	/**
	 * Dashboard recent orders.
	 *
	 * Class WKMP_Dashboard_Recent_Order
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Dashboard
	 */
	class WKMP_Dashboard_Recent_Order {
		/**
		 * Seller orders.
		 *
		 * @var array $seller_orders Seller orders.
		 */
		private $seller_orders;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Dashboard_Recent_Order constructor.
		 *
		 * @param object $db_obj DB Object.
		 * @param object $marketplace Marketplace object.
		 * @param array  $seller_orders Seller orders.
		 * @param int    $seller_id Seller id.
		 */
		public function __construct( $db_obj, $marketplace, $seller_orders, $seller_id ) {
			$this->seller_orders = $seller_orders;
			$this->wkmp_show_recent_orders( $seller_id );
		}

		/**
		 * Show seller recent orders..
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_show_recent_orders( $seller_id ) {
			$commission = Common\WKMP_Commission::get_instance();
			$per_page   = apply_filters( 'wkmp_recent_orders_limit', 10 );

			$seller_orders = $this->seller_orders;

			$order_ids = ! empty( $seller_orders['order_id'] ) ? explode( ',', $seller_orders['order_id'] ) : array();
			rsort( $order_ids );
			$order_items = array();

			foreach ( $order_ids as $order_id ) {
				$seller_order = wc_get_order( $order_id );
				if ( ! $seller_order instanceof \WC_Order ) {
					continue;
				}

				if ( count( $order_items ) === intval( $per_page ) ) {
					break;
				}

				$seller_order_data = $commission->wkmp_get_seller_final_order_info( $order_id, $seller_id );
				$order_items[]     = array(
					'OrderID'       => $order_id,
					'ItemCount'     => $seller_order_data['quantity'],
					'OrderCurrency' => $seller_order->get_currency(),
					'OrderTotal'    => $seller_order_data['total_seller_amount'],
					'OrderDate'     => gmdate( 'Y-m-d H:i:s', strtotime( $seller_order->get_date_created() ) ),
					'BillingEmail'  => $seller_order->get_billing_email(),
					'FirstName'     => $seller_order->get_billing_first_name(),
				);
			}

			$order_url = admin_url( 'admin.php?page=order-history' );
			if ( ! is_admin() ) {
				$order_url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_order_history_endpoint', 'sellers-orders' );
			}
			?>
			<div class="mp-store-recent-orders">
				<h4><?php esc_html_e( 'Recent Orders', 'wk-marketplace' ); ?>
					<a href="<?php echo esc_url( $order_url ); ?>"><?php esc_html_e( 'View All', 'wk-marketplace' ); ?></a>
				</h4>

				<table class="recentOrders">
					<thead style="background : #e9e9e9;">
					<tr>
						<th><?php esc_html_e( 'Order ID', 'wk-marketplace' ); ?></th>
						<th><?php esc_html_e( 'Order Date', 'wk-marketplace' ); ?></th>
						<th><?php esc_html_e( 'Billing Email', 'wk-marketplace' ); ?></th>
						<th><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?></th>
						<th><?php esc_html_e( 'Item Count', 'wk-marketplace' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'wk-marketplace' ); ?></th>
					</tr>
					</thead>

					<tbody>
					<?php
					if ( count( $order_items ) > 0 ) {
						foreach ( $order_items as $key => $order_item ) {
							$alternate = '';
							if ( 1 === $key % 2 ) {
								$alternate = 'alternate ';
							}
							?>
							<tr class="<?php echo esc_attr( $alternate . 'row_' . $key ); ?>">
								<td><?php echo esc_html( $order_item['OrderID'] ); ?></td>
								<td><?php echo esc_html( $order_item['OrderDate'] ); ?></td>
								<td><?php echo esc_html( $order_item['BillingEmail'] ); ?></td>
								<td><?php echo esc_html( $order_item['FirstName'] ); ?></td>
								<td><?php echo esc_html( $order_item['ItemCount'] ); ?></td>
								<td><?php echo wp_kses_data( wc_price( $order_item['OrderTotal'], array( 'currency' => $order_item['OrderCurrency'] ) ) ); ?></td>
							</tr>
							<?php
						}
					} else {
						echo '<tr><td colspan=6><p>';
						esc_html_e( 'No orders found.', 'wk-marketplace' );
						echo '</p></td></tr>';
					}
					?>
					<tbody>
				</table>
			</div>
			<?php
		}
	}
}
