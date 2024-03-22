<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$msg         = esc_html__( 'Your account has been approved by admin ', 'wk-marketplace' );
$admin       = get_option( 'admin_email' );
$footer_text = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ), 'UTF-8' );
$reference   = esc_html__( 'If you have any query, please contact us at -', 'wk-marketplace' );
$thanks_msg  = esc_html__( 'Thanks for choosing Marketplace.', 'wk-marketplace' );

echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ) ) . " =\n\n";

esc_html_e( 'Hi', 'wk-marketplace' ) . ', ' . wp_kses_post( mb_convert_encoding( $user_email, 'UTF-8' ) ) . "\n\n";

echo wp_kses_post( $msg ) . "\n\n";

echo wp_kses_post( $reference ) . "\n\n";

echo '<a href="mailto:' . esc_attr( $admin ) . '">' . esc_html( $admin ) . '</a>' . "\n\n";

echo wp_kses_post( $thanks_msg ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( $footer_text );
