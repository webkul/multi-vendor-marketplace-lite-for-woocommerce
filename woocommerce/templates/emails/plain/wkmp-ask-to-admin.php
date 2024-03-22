<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$username      = esc_html__( 'Email: ', 'wk-marketplace' );
$username_mail = mb_convert_encoding( $data['email'], 'UTF-8' );

$user_obj          = get_user_by( 'email', $username_mail );
$user_name         = $user_obj->first_name ? $user_obj->first_name . ' ' . $user_obj->last_name : esc_html__( 'Someone', 'wk-marketplace' );
$msg               = mb_convert_encoding( $user_name . esc_html__( ' asked a query from following account:', 'wk-marketplace' ), 'UTF-8' );
$admin             = esc_html__( 'Message: ', 'wk-marketplace' );
$admin_message     = mb_convert_encoding( $data['ask'], 'UTF-8' );
$footer_text       = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ), 'UTF-8' );
$reference         = esc_html__( 'Subject: ', 'wk-marketplace' );
$reference_message = mb_convert_encoding( $data['subject'], 'UTF-8' );

echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ) ) . " =\n\n";

echo esc_html__( 'Hi', 'wk-marketplace' ) . ', ' . wp_kses_post( $admin_email ) . "\n\n";

echo wp_kses_post( $msg ) . "\n\n";

echo '<strong>' . wp_kses_post( $username ) . '</strong>' . wp_kses_post( $username_mail ) . "\n\n";
echo '<strong>' . wp_kses_post( $reference ) . '</strong>' . wp_kses_post( $reference_message ) . "\n\n";
echo '<strong>' . wp_kses_post( $admin ) . '</strong>' . wp_kses_post( $admin_message ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( $footer_text );
