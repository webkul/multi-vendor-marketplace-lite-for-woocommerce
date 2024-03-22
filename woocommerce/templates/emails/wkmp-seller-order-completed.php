<?php
/**
 * Email templates Order Completed.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$email_message = esc_html__( 'We have completed your order.', 'wk-marketplace' );

require __DIR__ . '/wkmp-common-email-data.php';
