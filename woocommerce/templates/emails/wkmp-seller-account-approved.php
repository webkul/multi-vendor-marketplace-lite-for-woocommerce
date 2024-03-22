<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$mail_to           = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
$seller_first_name = empty( $data['seller_first_name'] ) ? '' : $data['seller_first_name'];
$mail_data         = empty( $data['mail_data'] ) ? array() : $data['mail_data'];

$msg       = html_entity_decode( esc_html__( 'Your account has been approved by admin.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );
$admin     = get_option( 'admin_email', false );
$next_step = html_entity_decode( esc_html__( 'You can add your products and continue selling.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '<p>' . html_entity_decode( esc_html__( 'Hi ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . html_entity_decode( $seller_first_name, ENT_QUOTES, 'UTF-8' ) . ',</p>
			<p>' . $msg . '.</p>
			<p>' . $mail_data['reference_msg'] . ' <a href="mailto:' . $admin . '">' . $admin . '</a></p>
			<p>' . $next_step . '</p>
			<p><a target="_blank" href="' . esc_url( $mail_data['seller_url'] ) . '">' . $mail_data['click_text'] . '</a>' . $mail_data['profile_msg'] . '</p>';

if ( ! empty( $additional_content ) ) {
	$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
}

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $mail_to );
