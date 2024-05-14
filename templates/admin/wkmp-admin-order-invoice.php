<?php
/**
 * Admin template hooks
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$invoice_nonce = \WK_Caching::wk_get_request_data( 'invoice_nonce' );

if ( empty( $invoice_nonce ) || ( ! empty( $invoice_nonce ) && ! wp_verify_nonce( $invoice_nonce, 'generate_invoice' ) ) ) {
	wp_die( '<h1>' . esc_html__( 'Cheating’ uh?', 'wk-marketplace' ) . '</h1><p>' . esc_html__( 'Sorry, you are not allowed to access invoice.', 'wk-marketplace' ) . '</p>' );
}

$order_detail_by_order_id = array();
$get_items                = $admin_order->get_items();
$cur_symbol               = get_woocommerce_currency_symbol( $admin_order->get_currency() );

foreach ( $get_items as $key => $value ) {
	$value_data          = $value->get_data();
	$product_id          = $value->get_product_id();
	$variable_id         = $value->get_variation_id();
	$product_total_price = $value_data['total'];
	$qty                 = $value_data['quantity'];

	$order_detail_by_order_id[ $product_id ][] = array(
		'product_name'        => $value['name'],
		'qty'                 => $qty,
		'variable_id'         => $variable_id,
		'product_total_price' => $product_total_price,
	);
}

	$shipping_method = ( $admin_order->get_shipping_method() ) ? $admin_order->get_shipping_method() : 'N.A';
	$payment_method  = $admin_order->get_data()['payment_method_title'];
	$total_payment   = 0;

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
		<td style="width: 50%;">
			<table>
				<tbody>
				<tr>
					<td colspan="2"><b><?php esc_html_e( 'Buyer Details', 'wk-marketplace' ); ?></b></td>
				</tr>

				<tr>
					<td><b><?php esc_html_e( 'Name: ', 'wk-marketplace' ); ?></b></td>
					<td data-title="Name">
						<?php echo esc_html( $admin_order->get_billing_first_name() . ' ' . $admin_order->get_billing_last_name() ); ?>
					</td>
				</tr>

				<tr>
					<td><b><?php esc_html_e( 'Email: ', 'wk-marketplace' ); ?></b></td>
					<td data-title="Email"><a href="mailto:<?php echo esc_attr( $admin_order->get_billing_email() ); ?>"><?php echo esc_html( $admin_order->get_billing_email() ); ?></a></td>
				</tr>

				<tr class="alt-table-row">
					<td><b><?php esc_html_e( 'Telephone: ', 'wk-marketplace' ); ?></b></td>
					<td data-title="Telephone"><a href="tel:<?php echo esc_attr( $admin_order->get_billing_phone() ); ?>"><?php echo esc_html( $admin_order->get_billing_phone() ); ?></a></td>
				</tr>
				</tbody>
			</table>
		</td>
		<td style="width: 50%;">
			<table>
				<tbody>
				<tr>
					<td><b><?php esc_html_e( 'Order Date: ', 'wk-marketplace' ); ?> </b></td>
					<td data-title="Order date"><?php echo esc_html( gmdate( 'F j, Y', strtotime( $admin_order->get_date_created() ) ) ); ?></td>
				</tr>

				<tr>
					<td><b><?php esc_html_e( 'Order ID: ', 'wk-marketplace' ); ?></b></td>
					<td data-title="Order ID"><?php echo esc_html( $order_id ); ?></td>
				</tr>

				<tr class="alt-table-row">
					<td><b><?php esc_html_e( 'Payment Method: ', 'wk-marketplace' ); ?> </b></td>
					<td data-title="Payment Method"><?php echo esc_html( $payment_method ); ?></td>
				</tr>

				<tr class="alt-table-row">
					<td><b><?php esc_html_e( 'Shipping Method: ', 'wk-marketplace' ); ?></b></td>
					<td data-title="Shipping Method"><?php echo esc_html( $shipping_method ); ?></td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>

<table class="table table-bordered">
	<thead>
	<tr>
		<td colspan="2"><b><?php esc_html_e( 'Address Information', 'wk-marketplace' ); ?></b></td>
	</tr>
	<tr>
		<td style="width: 50%;"><b><?php esc_html_e( 'Billing Address', 'wk-marketplace' ); ?></b></td>
		<td style="width: 50%;"><b><?php esc_html_e( 'Shipping Address', 'wk-marketplace' ); ?></b></td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>
			<address>
				<?php
				echo esc_html( $admin_order->get_billing_first_name() ) . ' ' . esc_html( $admin_order->get_billing_last_name() ) . '<br>' . esc_html( $admin_order->get_billing_address_1() ) . '<br>';
				if ( ! empty( $admin_order->get_billing_address_2() ) ) {
					echo esc_html( $admin_order->get_billing_address_2() ) . '<br>';
				}
				if ( $admin_order->get_billing_country() ) {
					echo esc_html( $admin_order->get_billing_city() . ' - ' . $admin_order->get_billing_postcode() ) . '<br>' . esc_html( $admin_order->get_billing_state() ) . ', ' . esc_html( WC()->countries->countries[ $admin_order->get_billing_country() ] );
				}
				?>
			</address>
		</td>
		<td>
			<address>
				<?php
				echo esc_html( $admin_order->get_shipping_first_name() . ' ' . $admin_order->get_shipping_last_name() ) . '<br>' . esc_html( $admin_order->get_shipping_address_1() ) . '<br>';
				if ( ! empty( $admin_order->get_shipping_address_2() ) ) {
					echo esc_html( $admin_order->get_shipping_address_2() ) . '<br>';
				}
				if ( $admin_order->get_shipping_country() ) {
					echo esc_html( $admin_order->get_shipping_city() . ' - ' . $admin_order->get_shipping_postcode() ) . '<br>' . esc_html( $admin_order->get_shipping_state() ) . ', ' . esc_html( WC()->countries->countries[ $admin_order->get_shipping_country() ] );
				}
				?>
			</address>
		</td>
	</tr>
	</tbody>
</table>

<table class="table table-bordered">
	<thead>
	<tr>
		<td colspan="4"><b><?php esc_html_e( 'Items to Invoice', 'wk-marketplace' ); ?></b></td>
	</tr>
	<tr>
		<td><b><?php esc_html_e( 'Product', 'wk-marketplace' ); ?></b></td>
		<td class="text-right"><b><?php esc_html_e( 'Quantity', 'wk-marketplace' ); ?></b></td>
		<td class="text-right"><b><?php esc_html_e( 'Unit Price', 'wk-marketplace' ); ?></b></td>
		<td class="text-right"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></td>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $order_detail_by_order_id as $product_id => $details ) {
		$detail_count = count( $details );
		for ( $i = 0; $i < $detail_count; $i++ ) {
			$total_payment = $total_payment + intval( $details[ $i ]['product_total_price'] );
			if ( $details[ $i ]['variable_id'] < 1 ) {
				?>

				<tr>
					<td><?php echo esc_html( $details[ $i ]['product_name'] ); ?></td>
					<td class="text-right"><?php echo esc_html( $details[ $i ]['qty'] ); ?></td>
					<td class="text-right"><?php echo esc_html( $cur_symbol ) . esc_html( $details[ $i ]['product_total_price'] / $details[ $i ]['qty'] ); ?></td>
					<td class="text-right"><?php echo esc_html( $cur_symbol . $details[ $i ]['product_total_price'] ); ?></td>
				</tr>

				<?php
			} else {
				$product        = new WC_Product( $product_id );
				$attribute      = $product->get_attributes();
				$attribute_name = '';
				foreach ( $attribute as $key => $value ) {
					$attribute_name = $value['name'];
				}
				$variation      = new WC_Product_Variation( $details[ $i ]['variable_id'] );
				$aaa            = $variation->get_variation_attributes();
				$attribute_prop = strtoupper( $aaa[ 'attribute_' . strtolower( $attribute_name ) ] );
				?>
				<tr>
					<td>
						<?php echo esc_html( $details[ $i ]['product_name'] ); ?><br>
						<b><?php echo esc_html( $attribute_name ) . ': '; ?></b>
						<?php echo esc_html( $attribute_prop ); ?>
					</td>
					<td class="text-right"><?php echo esc_html( $details[ $i ]['qty'] ); ?></td>
					<td class="text-right"><?php echo esc_html( $cur_symbol ) . esc_html( $details[ $i ]['product_total_price'] / $details[ $i ]['qty'] ); ?></td>
					<td class="text-right"><?php echo esc_html( $cur_symbol . $details[ $i ]['product_total_price'] ); ?></td>
				</tr>
			<?php } ?>
		<?php } ?>
	<?php } ?>
	<tr>
		<td class="text-right" colspan="3"><b><?php esc_html_e( 'SubTotal', 'wk-marketplace' ); ?></b></td>
		<td class="text-right"><?php echo esc_html( $cur_symbol . $total_payment ); ?></td>
	</tr>

	<tr>
		<td class="text-right" colspan="3"><b><?php esc_html_e( 'Shipping', 'wk-marketplace' ); ?></b></td>
		<td class="text-right"><?php echo esc_html( $cur_symbol ) . esc_html( $admin_order->get_total_shipping() ); ?></td>
	</tr>

	<tr>
		<td class="text-right" colspan="3"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></td>
		<td class="text-right"><?php echo esc_html( $cur_symbol ) . esc_html( $admin_order->get_total_shipping() + $total_payment ); ?></td>
	</tr>
	</tbody>
</table>
<?php
require_once WKMP_LITE_PLUGIN_FILE . '/templates/common/wkmp-order-invoice-footer.php';
