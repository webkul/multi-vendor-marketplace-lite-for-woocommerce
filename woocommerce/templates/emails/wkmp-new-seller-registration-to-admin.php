<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$msg          = sprintf( /* translators: %s: Blog name. */ esc_html__( 'New Seller registration on %s ', 'wk-marketplace' ), get_option( 'blogname' ) ) . "\n\n\r\n\r\n\n\n";
$username     = empty( $data['user_name'] ) ? '' : $data['user_name'];
$seller_email = empty( $data['user_email'] ) ? '' : $data['user_email'];
$shop_url     = empty( $data['shop_url'] ) ? '' : $data['shop_url'];
$mail_to      = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
$mail_data    = empty( $data['mail_data'] ) ? array() : $data['mail_data'];

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '<p> <strong>' . $msg . '</strong><p>
			<p><b>' . html_entity_decode( esc_html__( 'Username :- ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . '</b> ' . $username . '</p>
			<p><b>' . html_entity_decode( esc_html__( 'User email :- ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . '</b> ' . $seller_email . '</p>
			<p><b>' . html_entity_decode( esc_html__( 'Seller Shop URL :- ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . '</b> ' . $shop_url . '</p>';

if ( ! $mail_data['auto_approve'] ) {
	$result .= '<p><a target="_blank" href="' . esc_url( $mail_data['mp_admin_url'] ) . '">' . $mail_data['click_text'] . '</a>' . $mail_data['approve_msg'] . '</p>';
}


if ( ! empty( $additional_content ) ) {
	$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
}

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $mail_to );
