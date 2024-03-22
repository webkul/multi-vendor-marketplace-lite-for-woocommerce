<?php
/**
 * Email templates order refunded.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Common;

$order_id = empty( $data['order_id'] ) ? 0 : intval( $data['order_id'] );

if ( $order_id < 1 ) {
	return false;
}

$seller_order = wc_get_order( $order_id );
$date_string  = empty( $data['date_string'] ) ? gmdate( 'Y-m-d H:i:s' ) : $data['date_string'];

$commission_helper        = Common\WKMP_Commission::get_instance();
$seller_order_refund_data = $commission_helper->wkmp_get_seller_order_refund_data( $order_id );

$email_message = '<p>' . html_entity_decode( /* translators: %s Order number. */ wp_sprintf( esc_html__( 'Your order %s has been fully refunded. There are more details below for your reference:', 'wk-marketplace' ), esc_html( $seller_order->get_order_number() ) ), ENT_QUOTES, 'UTF-8' ) . '</p>';

if ( ! empty( $refunded_amount ) ) {
	$email_message = '<p>' . html_entity_decode( /* translators: %1$s Order number, %2$s Price. */ wp_sprintf( esc_html__( 'Your order %1$s has been refunded by amount %2$s. There are more details below for your reference:', 'wk-marketplace' ), esc_html( $seller_order->get_order_number() ), wc_price( $refunded_amount, array( 'currency' => $seller_order->get_currency() ) ) ), ENT_QUOTES, 'UTF-8' ) . '</p>';
}

require __DIR__ . '/wkmp-common-email-data.php';
