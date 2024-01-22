<?php
/**
 * Email templates Order processing.
 *
 * @package Multi Vendor Marketplace
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$order_id = empty( $data['order_id'] ) ? 0 : intval( $data['order_id'] );

if ( $order_id < 1 ) {
	return false;
}

$seller_order = wc_get_order( $order_id );
$date_string  = empty( $data['date_string'] ) ? gmdate( 'Y-m-d H:i:s' ) : $data['date_string'];

$email_message = html_entity_decode( wp_sprintf( /* translators: %1$s Order number, %2$s Order date. */  esc_html__( 'Just to let you know, your order status is now being changed to processing: Order id: # %1$s (%2$s)', 'wk-marketplace' ), esc_html( $seller_order->get_order_number() ), esc_html( $date_string ) ), ENT_QUOTES, 'UTF-8' );

require __DIR__ . '/wkmp-common-email-data.php';
