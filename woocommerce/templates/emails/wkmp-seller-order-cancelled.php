<?php
/**
 * Email templates cancel order.
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

$email_message = html_entity_decode( wp_sprintf( /* translators: %1$d: Order number, %2$s: Billing full name. */  esc_html__( 'Alas. Just to let you know — order #%1$d belonging to %2$s has been cancelled:', 'wk-marketplace' ), esc_html( $seller_order->get_order_number() ), esc_html( $seller_order->get_formatted_billing_full_name() ) ), ENT_QUOTES, 'UTF-8' );

require __DIR__ . '/wkmp-common-email-data.php';
