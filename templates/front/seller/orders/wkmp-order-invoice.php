<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$seller_info      = empty( $data['seller_info'] ) ? new stdClass() : $data['seller_info'];
$ordered_products = empty( $data['ordered_products'] ) ? array() : $data['ordered_products'];
$currency_symbol  = empty( $data['currency_symbol'] ) ? '' : $data['currency_symbol'];
$city_country     = empty( $seller_info->billing_city ) ? '' : $seller_info->billing_city;
$city_country    .= ( ! empty( $city_country ) && ! empty( $seller_info->billing_country ) ) ? ', ' : '';
$city_country    .= empty( $seller_info->billing_country ) ? '' : $seller_info->billing_country;

require_once WKMP_LITE_PLUGIN_FILE . '/templates/common/wkmp-order-invoice-header.php';
?>
	<table class="table table-bordered">
		<thead>
		<tr>
			<td colspan="2"><b><?php esc_html_e( 'Order Information', 'wk-marketplace' ); ?></b></td>
		</tr>
		</thead>

		<tbody>
		<tr>
			<td>
				<b><?php echo esc_html( $seller_info->shop_name ); ?></b><br>
				<?php echo esc_html( $seller_info->first_name . ' ' . $seller_info->last_name ); ?><br>
				<?php echo esc_html( $city_country ); ?><br>
				<b><?php esc_html_e( 'Email: ', 'wk-marketplace' ); ?></b><a href="mailto:<?php echo esc_attr( $seller_info->user_email ); ?>"><?php echo esc_html( $seller_info->user_email ); ?></a><br>
				<b><?php esc_html_e( 'Profile Link: ', 'wk-marketplace' ); ?></b>
				<a href="<?php echo esc_url( $data['store_url'] ); ?>" target="_blank"><?php echo esc_url( $data['store_url'] ); ?></a>
			</td>
			<td>
				<b><?php esc_html_e( 'Order Date: ', 'wk-marketplace' ); ?></b><?php echo esc_html( $data['date_created'] ); ?><br>
				<b><?php esc_html_e( 'Order ID: ', 'wk-marketplace' ); ?> </b><?php echo esc_html( $order_id ); ?><br>
				<b><?php esc_html_e( 'Payment Method: ', 'wk-marketplace' ); ?></b><?php echo esc_html( $data['payment_method'] ); ?><br>
				<?php
				if ( ! empty( $data['shipping_method'] ) ) {
					?>
					<b><?php esc_html_e( 'Shipping Method: ', 'wk-marketplace' ); ?></b><?php echo esc_html( $data['shipping_method'] ); ?><br>
					<?php
				}
				?>
			</td>
		</tr>
		</tbody>
	</table>

	<table class="table table-bordered">
		<tbody>
		<tr>
			<td colspan="2"><b><?php esc_html_e( 'Buyer Details', 'wk-marketplace' ); ?></b></td>
		</tr>
		<tr>
			<td><b><?php esc_html_e( 'Name', 'wk-marketplace' ); ?></b></td>
			<td data-title="<?php esc_attr_e( 'Name', 'wk-marketplace' ); ?>"><?php echo esc_html( $data['customer_details']['name'] ); ?></td>
		</tr>
		<tr>
			<td><b><?php esc_html_e( 'Email', 'wk-marketplace' ); ?></b></td>
			<td data-title="<?php esc_attr_e( 'Email', 'wk-marketplace' ); ?>"><a href="mailto:<?php echo esc_attr( $data['customer_details']['email'] ); ?>"><?php echo esc_html( $data['customer_details']['email'] ); ?></a></td>
		</tr>
		<tr class="alt-table-row">
			<td><b><?php esc_html_e( 'Telephone', 'wk-marketplace' ); ?></b></td>
			<td data-title="<?php esc_attr_e( 'Telephone', 'wk-marketplace' ); ?>"><a class="wkmp-seller-detail-phone" href="tel:<?php echo esc_attr( $data['customer_details']['telephone'] ); ?>"><?php echo esc_html( $data['customer_details']['telephone'] ); ?></a></td>
		</tr>
		</tbody>
	</table>

	<table class="table table-bordered">
		<thead>
		<tr>
			<td class="text-left"><b><?php esc_html_e( 'Product', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><b><?php esc_html_e( 'Quantity', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><b><?php esc_html_e( 'Unit Price', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></td>
		</tr>
		</thead>

		<tbody>
		<?php
		foreach ( $ordered_products as $key => $product ) {
			?>
			<tr>
				<td><?php echo esc_html( $product['product_name'] ); ?>
					<dl class="variation">
						<?php
						foreach ( $product['meta_data'] as $value ) {
							if ( '_reduced_stock' !== $value['key'] ) {
								?>
								<dt class="variation-size"><?php echo esc_html( $value['key'] ) . ' : ' . wp_kses_post( $value['value'] ); ?></dt>
								<?php
							}
						}
						?>
					</dl>
				</td>
				<td class="text-right"><?php echo esc_html( $product['quantity'] ); ?></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . $product['unit_price'] ); ?></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . $product['total_price'] ); ?></td>
			</tr>
		<?php } ?>
		<tr>
			<td class="text-right" colspan="3"><b><?php esc_html_e( 'SubTotal', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><?php echo esc_html( $currency_symbol . $data['sub_total'] ); ?></td>
		</tr>
		<?php
		if ( $data['total_discount'] > 0 ) {
			?>
		<tr>
			<td class="text-right" colspan="3"><b><?php esc_html_e( 'Discount', 'wk-marketplace' ); ?></b></td>
			<td class="text-right"><?php echo esc_html( $currency_symbol . $data['total_discount'] ); ?></td>
		</tr>
			<?php
		}
		foreach ( $seller_order->get_items( 'fee' ) as $item_id => $item_fee ) {
			$fee_name   = $item_fee->get_name();
			$fee_amount = $item_fee->get_total();
			?>
		<tr>
			<td class="text-right" colspan="3"><b><?php echo esc_html( apply_filters( 'wkmp_seller_order_fee_name', $fee_name ) ); ?></b></td>
			<td class="text-right"><?php echo esc_html( $currency_symbol . $fee_amount ); ?></td>
		</tr>
			<?php
		}
		if ( isset( $data['shipping_cost'] ) ) {
			?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Shipping', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . $data['shipping_cost'] ); ?></td>
			</tr>
			<?php
		}

		if ( ! empty( $data['total_commission'] ) ) {
			?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Commission', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo esc_html( '- ' . $currency_symbol . $data['total_commission'] ); ?></td>
			</tr>
			<?php
		}

		if ( ! empty( $seller_order_tax ) ) {
			?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Tax', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo wp_kses_data( wc_price( $seller_order_tax, $cur_symbol ) ); ?></td>
			</tr>
			<?php
		}
		if ( ! empty( $refund_data['refunded_amount'] ) ) {
			?>
			<tr>
				<td class="text-right" colspan="3"><b><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></b></td>
				<td class="text-right"><?php echo esc_html( $currency_symbol . wc_format_decimal( $refund_data['refunded_amount'], 2 ) ); ?></td>
			</tr>
		<?php } ?>

		<tr>
			<td class="text-right" colspan="3"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></td>
			<?php
			if ( ! empty( $refund_data['refunded_amount'] ) ) {
				?>
				<td class="text-right"><strong>
					<del><?php echo esc_html( $currency_symbol . $data['subtotal_refunded'] ); ?></del>
					</strong><?php echo esc_html( $currency_symbol . apply_filters( 'wkmp_add_order_fee_to_total', round( floatval( $data['total'] ), 2 ), $order_id ) ); ?></td>
			<?php } else { ?>
				<td class="text-right"><?php echo esc_html( $currency_symbol . apply_filters( 'wkmp_add_order_fee_to_total', $data['total'], $order_id ) ); ?></td>
			<?php } ?>
		</tr>

		</tbody>
	</table>

	<table class="table table-bordered">
		<thead>
		<tr>
			<td style="width: 50%;"><b><?php esc_html_e( 'Billing Address', 'wk-marketplace' ); ?></b></td>
			<td style="width: 50%;"><b><?php esc_html_e( 'Shipping Address', 'wk-marketplace' ); ?></b></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<address> <?php echo wp_kses_post( $data['billing_address'] ); ?> </address>
			</td>
			<td>
				<address> <?php echo wp_kses_post( $data['shipping_address'] ); ?> </address>
			</td>
		</tr>
		</tbody>
	</table>
<?php
require_once WKMP_LITE_PLUGIN_FILE . '/templates/common/wkmp-order-invoice-footer.php';
