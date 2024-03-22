<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$seller_email     = empty( $data['seller_email'] ) ? '' : $data['seller_email'];
$message_to_admin = empty( $data['message'] ) ? '' : $data['message'];
$subject          = empty( $data['subject'] ) ? '' : $data['subject'];
$mail_to          = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
$mail_data        = empty( $data['mail_data'] ) ? array() : $data['mail_data'];
$seller_name      = esc_html__( 'Someone', 'wk-marketplace' );

$user_obj = get_user_by( 'email', $seller_email );

if ( $user_obj instanceof \WP_User ) {
	$seller_name = $user_obj->first_name . ' ' . $user_obj->last_name;
}

$msg     = html_entity_decode( $seller_name . esc_html__( ' asked a query from following account:', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );
$subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '<p>' . $msg . '</p>
			<p><strong>' . $mail_data['email_label'] . '</strong>' . $seller_email . '</p>
			<p><strong>' . $mail_data['subject_label'] . '</strong>' . stripslashes( wptexturize( $subject ) ) . '</p>
			<p><strong>' . $mail_data['message_label'] . '</strong>' . stripslashes( wptexturize( $message_to_admin ) ) . '</p>';

if ( ! empty( $additional_content ) ) {
	$result .= wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $mail_to );
