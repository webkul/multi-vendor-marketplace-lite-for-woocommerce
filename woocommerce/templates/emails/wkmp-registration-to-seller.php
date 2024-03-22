<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$login_url    = empty( $data['user_login'] ) ? '' : $data['user_login'];
$mail_to      = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
$auto_approve = empty( $data['auto_approve'] ) ? false : $data['auto_approve'];
$mail_data    = empty( $data['mail_data'] ) ? array() : $data['mail_data'];

$welcome  = sprintf( html_entity_decode( esc_html__( 'Welcome to ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . get_option( 'blogname' ) . '!' ) . "\r\n\n";
$username = html_entity_decode( esc_html__( 'User :- ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . $data['user_email'];
$password = html_entity_decode( esc_html__( 'User Password :- ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . $data['user_pass'];

$msg = html_entity_decode( esc_html__( 'Your account has been created awaiting for admin approval.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . "\n\n\r\n\r\n\n\n";
$msg = $auto_approve ? html_entity_decode( esc_html__( 'You can add your products and continue selling.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . "\n\n\r\n\r\n\n\n" : $msg;

$admin = get_option( 'admin_email', false );

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '<p>' . $mail_data['hi_msg'] . ', ' . $login_url . '</p>
			<p> <strong>' . $welcome . '</strong><p>
			<p>' . $username . '</p>
			<p>' . $password . '</p>
			<p>' . $msg . '</p>
			<p><a target="_blank" href="' . esc_url( $mail_data['seller_url'] ) . '">' . $mail_data['click_text'] . '</a>' . $mail_data['profile_msg'] . '</p>
			<p>' . $mail_data['reference_msg'] . ' :-</p>
			<p><a href="mailto:' . $admin . '">' . $admin . '</a></p>';

if ( ! empty( $additional_content ) ) {
	$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
}

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $mail_to );
