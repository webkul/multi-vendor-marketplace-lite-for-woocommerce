<?php
/**
 * Email common data.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$order_id = empty( $data['order_id'] ) ? 0 : intval( $data['order_id'] );

if ( $order_id < 1 ) {
	return false;
}

$seller_order = wc_get_order( $order_id );
$seller_email = empty( $data['seller_email'] ) ? '' : $data['seller_email'];
$mail_to      = empty( $data['mail_to'] ) ? $seller_email : $data['mail_to'];

$product_details = empty( $data['product_details'] ) ? array() : $data['product_details'];
$com_data        = empty( $data['commission_data'] ) ? array() : $data['commission_data'];
$date_string     = empty( $data['date_string'] ) ? gmdate( 'Y-m-d H:i:s' ) : $data['date_string'];

$subtotal      = 0;
$total_tax     = 0;
$total_payment = 0;

$fees = $seller_order->get_fees();

$total_discount  = $seller_order->get_total_discount();
$shipping_method = $seller_order->get_shipping_method();
$payment_method  = $seller_order->get_payment_method_title();

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '
	<div style="margin-bottom: 40px;">
		<p>' . $email_message . '</p>
		<h3>' . esc_html__( 'Order', 'wk-marketplace' ) . ' #' . $seller_order->get_ID() . ' ( ' . $date_string . ' )</h3>
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;" border="1">
			<tr>
				<th class="td" style="text-align:' . esc_attr( $text_align ) . ';">' . esc_html__( 'Product', 'wk-marketplace' ) . '</th>
				<th class="td" style="text-align:' . esc_attr( $text_align ) . ';">' . esc_html__( 'Quantity', 'wk-marketplace' ) . '</th>
				<th class="td" style="text-align:' . esc_attr( $text_align ) . ';">' . esc_html__( 'Price', 'wk-marketplace' ) . '</th>
			</tr>
			<tr>';

foreach ( $product_details as $product_id => $details ) {
	$product      = new WC_Product( $product_id );
	$detail_count = is_iterable( $details ) ? count( $details ) : 0;

	for ( $i = 0; $i < $detail_count; ++$i ) {
		$total_tax     = floatval( $total_tax ) + floatval( $details[ $i ]['tax'] );
		$subtotal      = floatval( $subtotal ) + floatval( $details[ $i ]['product_total_price'] );
		$total_payment = floatval( $total_payment ) + floatval( $details[ $i ]['product_total_price'] );

		if ( 0 === intval( $details[ $i ]['variable_id'] ) ) {
			$result .= '<tr class="order_item alt-table-row" style="border-bottom-width: 2px;">
								<td class="product-name td">
									<span>' . html_entity_decode( $details[ $i ]['product_name'], ENT_QUOTES, 'UTF-8' ) . '</span><br />';
			if ( ! empty( $details[ $i ]['meta_data'] ) ) {
				foreach ( $details[ $i ]['meta_data'] as $m_data ) {
					$result .= '<b>' . wc_attribute_label( $m_data['key'] ) . '</b> : ' . $m_data['value'] . '<br>';
				}
			}
			$result .= '</td><td class="td">' . $details[ $i ]['qty'] . '</td>
									<td class="product-total td">
									' . wc_price( $details[ $i ]['product_total_price'], array( 'currency' => $seller_order->get_currency() ) ) . '
								</td>
							</tr>';
		} else {
			$attribute      = $product->get_attributes();
			$attribute_name = '';

			foreach ( $attribute as $key => $value ) {
				$attribute_name = $value['name'];
			}

			$result .= '<tr class="order_item alt-table-row td" style="border-bottom-width: 2px;">
							<td class="product-name td">
								<span>' . html_entity_decode( $details[ $i ]['product_name'], ENT_QUOTES, 'UTF-8' ) . '</span>';
			if ( ! empty( $details[ $i ]['meta_data'] ) ) {
				foreach ( $details[ $i ]['meta_data'] as $m_data ) {
					$result .= '<b>' . wc_attribute_label( $m_data['key'] ) . '</b> : ' . $m_data['value'] . '<br>';
				}
			}

			$result .= '</td>
							<td class="td">' . $details[ $i ]['qty'] . '</td>
							<td class="product-total td">
								' . wc_price( $details[ $i ]['product_total_price'], array( 'currency' => $seller_order->get_currency() ) ) . '
							</td>
						</tr>';
		}
	}
}

if ( ! empty( $subtotal ) ) {
	$result .= '<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . html_entity_decode( esc_html__( 'Subtotal', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . ' : </th>
						<td class="td">' . wc_price( $subtotal ) . '</td>
    				</tr>';
}
$total_order_fee = 0;

foreach ( $seller_order->get_items( 'fee' ) as $item_id => $item_fee ) {
	$fee_name         = $item_fee->get_name();
	$fee_amount       = $item_fee->get_total();
	$result          .= '<tr><th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . html_entity_decode( esc_html( apply_filters( 'wkmp_seller_order_fee_name', $fee_name ) ), ENT_QUOTES, 'UTF-8' ) . ' : </th>
						<td class="td">' . wc_price( $fee_amount ) . '</td></tr>';
	$total_order_fee += floatval( $fee_amount );
}
$total_payment += floatval( $total_order_fee );

if ( ! empty( $total_discount ) ) {
	$total_payment -= $total_discount;
	$result        .= '<tr>
						<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . html_entity_decode( esc_html__( 'Discount', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . ' : </th>
						<td class="td">-' . wc_price( $total_discount, array( 'currency' => $seller_order->get_currency() ) ) . '</td>
					</tr>';
}

if ( ! empty( $shipping_method ) ) :
	$result .= '<tr>
					<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . html_entity_decode( esc_html__( 'Shipping', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . ' : </th>
					<td class="td">' . wc_price( $com_data['shipping'] ? $com_data['shipping'] : 0, array( 'currency' => $seller_order->get_currency() ) ) . '</td>
				</tr>';
endif;

$total_payment += $com_data['shipping'];

if ( ! empty( $payment_method ) ) :
	$result .= '<tr>
					<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . html_entity_decode( esc_html__( 'Payment Method', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . ' : </th>
					<td class="td">' . $payment_method . '</td>
				</tr>';
endif;

$result .= '<tr>
                <th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . esc_html__( 'Total', 'wk-marketplace' ) . ' : </th>';

if ( ! empty( $seller_order_refund_data['refunded_amount'] ) ) {
	$result .= '<td class="td"><strong><del>' . wc_price( $total_payment, array( 'currency' => $seller_order->get_currency() ) ) . '</del></strong> ' . wc_price( $total_payment - $seller_order_refund_data['refunded_amount'], array( 'currency' => $seller_order->get_currency() ) ) . '</td>';
} else {
	$result .= '<td class="td">' . wc_price( $total_payment, array( 'currency' => $seller_order->get_currency() ) ) . '</td>';
}

$result .= '</tr>';

if ( ! empty( $seller_order_refund_data['refunded_amount'] ) ) {
	$result .= '<tr>
        		<th class="td" scope="row" colspan="2" style="text-align:' . esc_attr( $text_align ) . ';">' . html_entity_decode( esc_html__( 'Refunded', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . ' : </th>
			<td class="td">' . wc_price( $seller_order_refund_data['refunded_amount'], array( 'currency' => $seller_order->get_currency() ) ) . '</td>
	 	</tr>';
}

$result .= '</tr>
		</table>';

$result .= '<table id="addresses" style="width:100%">
				<tr>
					<td class="td" valign="top" width="49%">
						<h3>' . esc_html__( 'Billing address', 'wk-marketplace' ) . '</h3>
						<p class="text">' . html_entity_decode( $seller_order->get_formatted_billing_address(), ENT_QUOTES, 'UTF-8' ) . '</p>
					</td>';

if ( ! wc_ship_to_billing_address_only() && $seller_order->needs_shipping_address() ) :
	$shipping = '';
	if ( $seller_order->get_formatted_shipping_address() ) :
		$shipping = html_entity_decode( $seller_order->get_formatted_shipping_address(), ENT_QUOTES, 'UTF-8' );
	endif;

	if ( ! empty( $shipping ) ) {
		$result .= '<td class="td" valign="top" width="49%">
									<h3>' . esc_html__( 'Shipping address', 'wk-marketplace' ) . '</h3>
									<p class="text">' . $shipping . '</p>
								</td>';
	}
endif;

$result .= '</tr>
		</table>';

$result .= '</div>';

if ( ! empty( $additional_content ) ) {
	$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
}

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $mail_to );
