<?php
/**
 * Seller Transaction view In Admin Dashboard.
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Admin as AdminHelper;
use WkMarketplace\Helper\Common;

if ( ! class_exists( 'WKMP_Seller_Transaction_View' ) ) {
	/**
	 * Seller Transaction View Class.
	 *
	 * Class WKMP_Seller_Transaction_View
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Transaction_View {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Seller id.
		 *
		 * @var int|mixed $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Transaction_View constructor.
		 */
		public function __construct() {
			$seller_id       = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );
			$this->seller_id = empty( $seller_id ) ? 0 : $seller_id;
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
		 * Transaction view.
		 *
		 * @param int $id Transaction id.
		 */
		public function wkmp_display_seller_transaction_view( $id ) {
			$transaction_db_obj = Common\WKMP_Transaction::get_instance();
			$commission_db_obj  = Common\WKMP_Commission::get_instance();
			$transaction_info   = $transaction_db_obj->wkmp_get_transaction_details_by_id( $id );
			$this->seller_id    = empty( $this->seller_id ) ? $transaction_info->seller_id : $this->seller_id;
			$ord_id             = $transaction_info->order_id;
			$order              = wc_get_order( $ord_id );
			$sel_info           = $commission_db_obj->wkmp_get_seller_final_order_info( $ord_id, $this->seller_id );

			$columns = apply_filters(
				'wkmp_account_transactions_columns',
				array(
					'order_id'     => esc_html__( 'Order Id', 'wk-marketplace' ),
					'product_name' => esc_html__( 'Product Name', 'wk-marketplace' ),
					'quantity'     => esc_html__( 'Quantity', 'wk-marketplace' ),
					'price'        => esc_html__( 'Total Price', 'wk-marketplace' ),
					'commission'   => esc_html__( 'Commission', 'wk-marketplace' ),
					'subtotal'     => esc_html__( 'Subtotal', 'wk-marketplace' ),
				)
			);

			$currency = ( $order->get_currency() ) ? $order->get_currency() : get_woocommerce_currency();
			?>
			<div class="woocommerce-account woocommerce">
				<?php do_action( 'mp_get_wc_account_menu', 'wk-marketplace' ); ?>
				<div class="wk-transaction-view woocommerce-MyAccount-content">
					<div class="wk-mp-transaction-info-box">
						<div>
							<h3>
								<?php esc_html_e( 'Transaction Id', 'wk-marketplace' ); ?> - <?php echo esc_html( $transaction_info->transaction_id ); ?>
							</h3>
							<div class="box">
								<div class="box-title">
									<h3><?php esc_html_e( 'Information', 'wk-marketplace' ); ?></h3>
								</div>
								<fieldset>
									<div class="box-content">
										<div class="wk_row">
											<span class="label"><?php esc_html_e( 'Date', 'wk-marketplace' ); ?> : </span>
											<span class="value"><?php echo esc_html( gmdate( 'F, j, Y', strtotime( $transaction_info->transaction_date ) ) ); ?></span>
										</div>
										<div class="wk_row">
											<span class="label"><?php esc_html_e( 'Amount', 'wk-marketplace' ); ?> : </span>
											<span class="value"><span class="price"><?php echo wp_kses_data( wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $transaction_info->amount - $sel_info['refunded_amount'], $ord_id ), array( 'currency' => $currency ) ) ); ?></span></span>
										</div>

										<?php if ( isset( $sel_info['refunded_amount'] ) && $sel_info['refunded_amount'] ) { ?>
											<div class="wk_row">
												<span class="label"><?php esc_html_e( 'Refunded Amount', 'wk-marketplace' ); ?> : </span>
												<span class="value"><span class="price"><?php echo wp_kses_data( wc_price( $sel_info['refunded_amount'], array( 'currency' => $currency ) ) ); ?></span></span>
											</div>
										<?php } ?>

										<div class="wk_row">
											<span class="label"><?php esc_html_e( 'Type', 'wk-marketplace' ); ?> : </span>
											<span class="value"><?php echo esc_html( $transaction_info->type ); ?></span>
										</div>
										<div class="wk_row">
											<span class="label"><?php esc_html_e( 'Method', 'wk-marketplace' ); ?> : </span>
											<span class="value"><?php echo esc_html( $transaction_info->method ); ?></span>
										</div>
									</div>
								</fieldset>
							</div>
						</div>
					</div>
					<div class="transaction-details">
						<div class="table-wrapper">
							<h3 class="table-caption">
								<?php esc_html_e( 'Detail', 'wk-marketplace' ); ?>
							</h3>
							<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
								<thead>
								<tr>
									<?php foreach ( $columns as $column_id => $column_name ) : ?>
										<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>">
											<span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
									<?php endforeach; ?>
								</tr>
								</thead>

								<tbody>
								<?php
								$current_user = wp_get_current_user();
								$role_name    = $current_user->roles;
								$seller_id    = get_current_user_id();
								if ( ! in_array( 'wk_marketplace_seller', $role_name, true ) ) {
									$seller_id = $this->seller_id;
								}
								$item_count   = $order->get_items();
								$product_name = '';

								if ( isset( $sel_info['product'] ) && is_iterable( $sel_info['product'] ) ) {
									foreach ( $sel_info['product'] as $pro_nme ) {
										if ( ! empty( $product_name ) ) {
											$product_name = $product_name . ' + ';
										}
										$product_name = $product_name . $pro_nme['title'];
									}
								}

								$sel_rwd_note = '';
								if ( ! empty( $sel_info['reward_data'] ) ) {
									if ( ! empty( $sel_info['reward_data']['seller'] ) ) {
										$sel_rwd_note = ' ' . wc_price( $sel_info['reward_data']['seller'], array( 'currency' => $currency ) ) . '( ' . __( 'Reward', 'wk-marketplace' ) . ' )';
									}
								}

								$admin_rwd_note = '';
								if ( ! empty( $sel_info['reward_data'] ) ) {
									if ( ! empty( $sel_info['reward_data']['admin'] ) ) {
										$admin_rwd_note = ' + ' . wc_price( $sel_info['reward_data']['admin'], array( 'currency' => $currency ) ) . '( ' . __( 'Reward', 'wk-marketplace' ) . ' )';
									}
								}

								$sel_walt_note = '';
								if ( ! empty( $sel_info['wallet_data'] ) ) {
									if ( ! empty( $sel_info['wallet_data']['seller'] ) ) {
										$sel_walt_note = ' ' . wc_price( $sel_info['wallet_data']['seller'], array( 'currency' => $currency ) ) . '( ' . __( 'Wallet', 'wk-marketplace' ) . ' )';
									}
								}

								$quantity          = $sel_info['quantity'];
								$line_total        = ( $sel_info['product_total'] + $sel_info['shipping'] );
								$line_total        = $line_total - $sel_info['refunded_amount'];
								$commission_amount = $sel_info['total_commission'];
								$subtotal          = apply_filters( 'wkmp_add_order_fee_to_total', $sel_info['total_seller_amount'] - $sel_info['refunded_amount'], $order->get_id() );
								?>
								<tr>
									<td>
										<?php echo esc_html_x( '#', 'hash before order number', 'wk-marketplace' ) . intval( $order->get_order_number() ); ?>
									</td>
									<td>
										<?php echo esc_html( $product_name ); ?>
									</td>
									<td>
										<?php echo esc_html( $quantity ); ?>
									</td>
									<td>
										<?php echo wp_kses_data( wc_price( $line_total, array( 'currency' => $currency ) ) ); ?>
									</td>
									<td>
										<?php echo wp_kses_data( wc_price( $commission_amount, array( 'currency' => $currency ) ) . ' ' . esc_html( $admin_rwd_note ) ); ?>
									</td>
									<?php
										do_action( 'wkmp_account_transactions_columns_data', $ord_id );
									?>
									<td>
										<?php
										echo wp_kses_data( wc_price( $subtotal, array( 'currency' => $currency ) ) );
										if ( $subtotal !== $line_total ) {
											$tip  = '<p>';
											$tip .= wc_price( $subtotal, array( 'currency' => $currency ) );
											$tip .= ' = ';
											$tip .= wc_price( $line_total, array( 'currency' => $currency ) );
											if ( ! empty( $commission_amount ) ) {
												$tip .= ' - ';
												$tip .= wc_price( $commission_amount, array( 'currency' => $currency ) ) . ' ( ' . __( 'Commission', 'wk-marketplace' ) . ' ) ';
											}
											if ( ! empty( $sel_rwd_note ) ) {
												$tip .= ' - ';
												$tip .= $sel_rwd_note;
											}
											if ( ! empty( $sel_walt_note ) ) {
												$tip .= ' - ';
												$tip .= $sel_walt_note;
											}
											$tip .= ' ';
											$tip .= '</p>';
											echo wc_help_tip( $tip, true );
										}
										?>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
					<?php do_action( 'wkmp_after_admin_end_seller_transaction_details', $id ); ?>
				</div>
			</div>
			<?php
		}
	}
}
