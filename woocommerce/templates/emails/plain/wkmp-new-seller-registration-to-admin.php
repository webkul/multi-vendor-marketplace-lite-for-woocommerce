<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$msg          = wp_sprintf( /* translators: %s: Blog name. */ esc_html__( 'New Seller registration on %s:', 'wk-marketplace' ), get_option( 'blogname' ) ) . "\n\n\r\n\r\n\n\n";
$username     = esc_html__( 'Username :- ', 'wk-marketplace' ) . mb_convert_encoding( $data['user_name'], 'UTF-8' );
$seller_email = esc_html__( 'User email :- ', 'wk-marketplace' ) . mb_convert_encoding( $data['user_email'], 'UTF-8' );
$shop_url     = esc_html__( 'Seller Shop URL :- ', 'wk-marketplace' ) . mb_convert_encoding( $data['shop_url'], 'UTF-8' );

$footer_text = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );

echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ) ) . " =\n\n";
echo wp_sprintf( /* translators: %s: User Name. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), wp_kses_post( mb_convert_encoding( $data['user_name'], 'UTF-8' ) ) ) . "\n\n";

echo wp_kses_post( $msg ) . "\n";
echo wp_kses_post( $username ) . "\n";
echo wp_kses_post( $seller_email ) . "\n";
echo wp_kses_post( $shop_url ) . "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( $footer_text );
