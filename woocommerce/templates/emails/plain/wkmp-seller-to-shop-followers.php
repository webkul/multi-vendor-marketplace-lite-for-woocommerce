<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

global $wpdb;

if ( $data ) {
	$footer_text = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
	echo sprintf( /* translators: %s Customer first name */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_html( mb_convert_encoding( $data, 'UTF-8' ) ) ) . "\n\n";

	if ( ! empty( $additional_content ) ) {
		echo wp_kses_post( $additional_content );
	}

	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

	echo wp_kses_post( $footer_text );
}
