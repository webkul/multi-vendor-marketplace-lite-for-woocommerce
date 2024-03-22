<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( $data ) {
	$mail_to = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
	$message = empty( $data['message'] ) ? '' : $data['message'];
	do_action( 'woocommerce_email_header', $email_heading, $mail_to );
	$result = '<p>' . esc_html__( 'Hi', 'wk-marketplace' ) . ',</p>
            <p>' . $message . '</p>';

	if ( ! empty( $additional_content ) ) {
		$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
	}
	echo wp_kses_post( $result );
	do_action( 'woocommerce_email_footer', $mail_to );
}
