<?php
/**
 * Invoice header.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$suffix     = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? '' : '.min';
$asset_path = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? 'build' : 'dist';
?>
<!DOCTYPE html>
	<html>
	<head>
		<title><?php esc_html_e( 'Order Invoice', 'wk-marketplace' ); ?></title>

		<?php wp_head(); ?>
	</head>
	<body>
	<div class="mp-invoice-wrapper">
		<button class="wkmp_print_invoice_btn" onclick="javascript:window.print()"><img src="<?php echo esc_url( WKMP_LITE_PLUGIN_URL . 'assets/images/print_btn.webp' ); ?>"></button>
		<h1><?php echo wp_sprintf( /* translators: %d: Order number. */ esc_html__( 'Invoice Of Order #%d', 'wk-marketplace' ), esc_html( $order_id ) ); ?></h1>
