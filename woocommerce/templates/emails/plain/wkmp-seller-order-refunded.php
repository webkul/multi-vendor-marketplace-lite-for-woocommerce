<?php
/**
 * Email templates.
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

require WKMP_LITE_PLUGIN_FILE . 'helper/common/class-wkmp-commission.php';

use WkMarketplace\Helper\Common;

$commission_obj = Common\WKMP_Commission::get_instance();

$reward_point_weightage = ! empty( $GLOBALS['reward'] ) ? $GLOBALS['reward']->get_woocommerce_reward_point_weightage() : 0;
$seller_id              = get_user_by( 'email', $customer_email )->ID;

$order_id     = empty( $data['order_id'] ) ? 0 : intval( $data['order_id'] );
$seller_order = wc_get_order( $order_id );

$product_details  = empty( $data['product_details'] ) ? array() : $data['product_details'];
$common_functions = empty( $data['common_functions'] ) ? '' : $data['common_functions'];

$com_data = $commission_obj->wkmp_get_seller_final_order_info( $seller_order->get_id(), $seller_id );

$seller_order_refund_data = $commission_obj->wkmp_get_seller_order_refund_data( $seller_order->get_id(), $seller_id );

$subtotal      = 0;
$total_tax     = 0;
$total_payment = 0;

$fees = $seller_order->get_fees();

$total_discount  = $seller_order->get_total_discount();
$shipping_method = $seller_order->get_shipping_method();
$payment_method  = $seller_order->get_payment_method_title();

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_header', $email_heading, $email );
$date_string = empty( $data['date_string'] ) ? gmdate( 'Y-m-d H:i:s' ) : $data['date_string'];

echo sprintf( /* translators: %s: Login URL. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_attr( mb_convert_encoding( $loginurl, 'UTF-8' ) ) ) . "\n\n";

$result  = esc_html__( 'Your ', 'wk-marketplace' ) . '&nbsp; Order #' . $seller_order->get_ID() . ' (' . $date_string . ') ' . "\n\n";
$result .= esc_html__( ' has been refunded by amount ', 'wk-marketplace' ) . '&nbsp; ' . wc_price( $refunded_amount, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";

foreach ( $product_details as $product_id => $details ) {
	$product  = new WC_Product( $product_id );
	$detail_c = 0;
	if ( count( $details ) > 0 ) {
		$detail_c = count( $details );
	}
	for ( $i = 0; $i < $detail_c; ++$i ) {
		$total_payment = floatval( $total_payment ) + floatval( $details[ $i ]['product_total_price'] ) + floatval( $seller_order->get_total_shipping() );
		if ( 0 === intval( $details[ $i ]['variable_id'] ) ) {
			$result .= mb_convert_encoding( $details[ $i ]['product_name'], 'UTF-8' );
			$result .= empty( $common_functions ) ? '' : esc_html__( ' SKU: ', 'wk-marketplace' ) . $common_functions->wkmp_get_sku( $product );
		} else {
			$attributes = $product->get_attributes();

			$attribute_name = '';
			foreach ( $attributes as $key => $value ) {
				$attribute_name .= ' ' . $value['name'];
			}

			$result .= mb_convert_encoding( $details[ $i ]['product_name'], 'UTF-8' ) . $attribute_name;

			$result .= empty( $common_functions ) ? '' : esc_html__( ' SKU: ', 'wk-marketplace' ) . $common_functions->wkmp_get_sku( $product );

			if ( ! empty( $details[ $i ]['meta_data'] ) ) {
				foreach ( $details[ $i ]['meta_data'] as $m_data ) {
					$result .= '( ' . wc_attribute_label( $m_data['key'] ) . ' : ' . strtoupper( $m_data['value'] ) . ' )';
				}
			}

			$result .= ' X ' . $details[ $i ]['qty'] . ' = ' . $seller_order->get_currency() . ' ' . $details[ $i ]['product_total_price'] . "\n\n";
		}
	}
}

if ( ! empty( $total_discount ) ) {
	$total_payment -= $total_discount;
	$result        .= esc_html__( 'Discount:- ', 'wk-marketplace' ) . wc_price( $total_discount, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";
}

if ( ! empty( $shipping_method ) ) :
	$result .= esc_html__( 'Shipping: ', 'wk-marketplace' ) . wc_price( ( $seller_order->get_total_shipping() ? $seller_order->get_total_shipping() : 0 ), array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";
endif;

$total_fee_amount = 0;

if ( ! empty( $fees ) ) {
	foreach ( $fees as $key => $fee ) {
		$fee_name   = $fee->get_data()['name'];
		$fee_amount = floatval( $fee->get_data()['total'] );

		$total_fee_amount += $fee_amount;

		$result .= mb_convert_encoding( $fee_name, 'UTF-8' ) . ' : ' . wc_price( $fee_amount, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";
	}
}

$total_payment += $total_fee_amount;

if ( ! empty( $payment_method ) ) :
	$result .= esc_html__( 'Payment Method: ', 'wk-marketplace' ) . $payment_method . "\n\n";
endif;

$result .= esc_html__( 'Total: ', 'wk-marketplace' ) . wc_price( $total_payment, array( 'currency' => $seller_order->get_currency() ) ) . "\n\n";

$text_align = is_rtl() ? 'right' : 'left';

$result .= esc_html__( 'Billing address: ', 'wk-marketplace' ) . "\n\n";

foreach ( $seller_order->get_address( 'billing' ) as $add ) {
	if ( $add ) {
		$result .= mb_convert_encoding( $add, 'UTF-8' ) . "\n";
	}
}
if ( ! wc_ship_to_billing_address_only() && $seller_order->needs_shipping_address() ) :
	$shipping = '';
	if ( $seller_order->get_formatted_shipping_address() ) :
		$shipping = mb_convert_encoding( $seller_order->get_formatted_shipping_address(), 'UTF-8' );
	endif;

	if ( ! empty( $shiping ) ) {
		$result .= esc_html__( 'Shipping address: ', 'wk-marketplace' ) . "\n\n";
		foreach ( $seller_order->get_address( 'billing' ) as $add ) {
			if ( $add ) {
				$result .= mb_convert_encoding( $add, 'UTF-8' ) . "\n";
			}
		}
	}
endif;

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $email );
