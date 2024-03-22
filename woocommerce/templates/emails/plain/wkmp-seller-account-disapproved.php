<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$msg         = esc_html__( 'Your account has been Disapproved by admin ', 'wk-marketplace' );
$admin       = get_option( 'admin_email' );
$reference   = esc_html__( 'If you have any query, please contact us at -', 'wk-marketplace' );
$thanks_msg  = esc_html__( 'Thanks for choosing Marketplace.', 'wk-marketplace' );
$footer_text = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );

echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ) ) . " =\n\n";

echo esc_html__( 'Hi', 'wk-marketplace' ) . ', ' . wp_kses_post( mb_convert_encoding( $user_email, 'UTF-8' ) ) . " \n\n";

echo wp_kses_post( $msg ) . "\n\n";

echo wp_kses_post( $reference ) . "\n\n";

echo '<a href="mailto:' . wp_kses_post( $admin ) . '">' . wp_kses_post( $admin ) . '</a>' . "\n\n";

echo wp_kses_post( $thanks_msg ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( $footer_text );
