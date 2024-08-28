<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>
<div class="wk-mp-transaction-info-box">
	<div>
		<h3><?php printf( /* translators: %s transaction id */ esc_html__( 'Transaction Id - %s', 'wk-marketplace' ), esc_attr( $transaction_info->transaction_id ) ); ?></h3>
		<div class="box">
			<div class="box-title">
				<h3><?php esc_html_e( 'Information', 'wk-marketplace' ); ?></h3>
			</div>
			<fieldset>
				<div class="box-content">
					<div class="wk_row">
						<span class="label"><?php esc_html_e( 'Date :', 'wk-marketplace' ); ?></span>
						<span class="value"><?php echo esc_html( gmdate( 'F, j, Y', strtotime( $transaction_info->transaction_date ) ) ); ?></span>
					</div>
					<div class="wk_row">
						<span class="label"><?php esc_html_e( 'Amount :', 'wk-marketplace' ); ?></span>
						<span class="value"><span class="price"><?php echo wp_kses_data( wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $transaction_info->amount - $seller_order_info['refunded_amount'], $transaction_info->order_id ), array( 'currency' => $currency ) ) ); ?></span></span>
					</div>
					<?php if ( isset( $seller_order_info['refunded_amount'] ) && $seller_order_info['refunded_amount'] ) { ?>
						<div class="wk_row">
							<span class="label"><?php esc_html_e( 'Refunded Amount', 'wk-marketplace' ); ?> : </span>
							<span class="value"><span class="price"><?php echo wp_kses_data( wc_price( $seller_order_info['refunded_amount'], array( 'currency' => $currency ) ) ); ?></span></span>
						</div>
					<?php } ?>
					<div class="wk_row">
						<span class="label"><?php esc_html_e( 'Type :', 'wk-marketplace' ); ?></span>
						<span class="value"><?php echo esc_html( ucfirst( $transaction_info->type ) ); ?></span>
					</div>
					<div class="wk_row">
						<span class="label"><?php esc_html_e( 'Method :', 'wk-marketplace' ); ?></span>
						<span class="value"><?php echo esc_html( ucfirst( $transaction_info->method ) ); ?></span>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
</div>
<div class="transaction-details">
	<div class="table-wrapper">
		<h3 class="table-caption"><?php esc_html_e( 'Detail', 'wk-marketplace' ); ?></h3>
		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
			<tr>
				<?php foreach ( $columns as $key => $value ) { ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-id"><span class="nobr"><?php echo esc_html( $value ); ?></span></th>
				<?php } ?>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php echo '#' . esc_html( $transaction_info->order_id ); ?></td>
				<td><?php echo esc_html( $product_name ); ?></td>
				<td><?php echo esc_html( $seller_order_info['quantity'] ); ?></td>
				<td>
					<?php echo wp_kses_data( wc_price( ( $seller_order_info['product_total'] - $seller_order_info['refunded_amount'] ), array( 'currency' => $currency ) ) ); ?>
				</td>
				<td>
					<?php echo wp_kses_data( wc_price( $seller_order_info['total_commission'], array( 'currency' => $currency ) ) ); ?>
				</td>
				<?php
				do_action( 'wkmp_account_transactions_columns_data', $transaction_info->order_id );
				?>
				<td>
					<?php echo wp_kses_data( wc_price( ( $seller_order_info['total_seller_amount'] - $seller_order_info['refunded_amount'] ), array( 'currency' => $currency ) ) ); ?>
				</td>
			</tr>
			</tbody>

		</table>
	</div>
</div>
<?php
do_action( 'wkmp_after_seller_end_seller_transaction_details', $id );
