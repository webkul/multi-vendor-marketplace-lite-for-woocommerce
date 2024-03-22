<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$_product     = wc_get_product( $product );
$product_name = mb_convert_encoding( $_product->get_name(), 'UTF-8' );
$user_name    = mb_convert_encoding( get_user_meta( $user, 'first_name', true ), 'UTF-8' );
$welcome      = esc_html__( 'Vendor ', 'wk-marketplace' ) . mb_convert_encoding( $user_name, 'UTF-8' ) . esc_html__( ' has requested to publish ', 'wk-marketplace' ) . '<strong>' . $product_name . '</strong> ' . esc_html__( 'product', 'wk-marketplace' ) . ' ! ';
$msg          = esc_html__( 'Please review the request', 'wk-marketplace' );
$review_here  = sprintf( admin_url( 'post.php?post=%s&action=edit' ), $product );
$admin        = get_option( 'admin_email' );
$footer_text  = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );

echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ) ) . " =\n\n";

echo wp_kses_post( esc_html__( 'Hi', 'wk-marketplace' ) ) . ', ' . wp_kses_post( $admin ) . "\n\n";

echo wp_kses_post( $welcome );

echo wp_kses_post( $msg ) . "\n\n" . '<a href=' . esc_url( $review_here ) . '>' . wp_kses_post( esc_html__( 'Here', 'wk-marketplace' ) ) . '</a>' . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( $footer_text );
