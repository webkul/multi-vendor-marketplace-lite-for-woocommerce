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
$msg          = '';
$review_here  = '';
$welcome      = esc_html__( 'Unfortunately! Your product ( ', 'wk-marketplace' ) . '<strong>' . esc_html( $product_name ) . '</strong> ' . esc_html__( ' ) has been rejected by Admin!', 'wk-marketplace' );
$footer_text  = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );

if ( $status ) {
	$welcome     = esc_html__( 'Congrats! Your product ( ', 'wk-marketplace' ) . '<strong>' . esc_html( $product_name ) . '</strong> ' . esc_html__( ' ) has been published!', 'wk-marketplace' );
	$msg         = esc_html__( 'Click here to view it ', 'wk-marketplace' );
	$review_here = get_the_permalink( $product );
	$review_here = ' <a href=' . $review_here . '>' . esc_html__( 'Here', 'wk-marketplace' ) . '</a>';
}

echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ), 'UTF-8' ) . " =\n\n";

echo wp_kses_post( esc_html__( 'Hi', 'wk-marketplace' ) ) . ', ' . wp_kses_post( $user_name ) . "\n\n";

echo wp_kses_post( $welcome );

echo wp_kses_post( $msg ) . "\n\n" . wp_kses_post( $review_here ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( $footer_text );
