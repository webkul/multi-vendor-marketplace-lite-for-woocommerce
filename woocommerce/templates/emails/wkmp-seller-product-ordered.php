<?php
/**
 * Email templates Product ordered.
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

$email_message = esc_html__( 'You have received an order from', 'wk-marketplace' ) . '&nbsp;' . $seller_order->get_formatted_billing_full_name();

require __DIR__ . '/wkmp-common-email-data.php';
