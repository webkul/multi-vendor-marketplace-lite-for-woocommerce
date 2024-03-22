<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$product_id = empty( $data['product_id'] ) ? 0 : $data['product_id'];
$_product   = ( $product_id > 0 ) ? wc_get_product( $product_id ) : '';

if ( is_a( $_product, 'WC_Product' ) ) {
	$product_name   = html_entity_decode( $_product->get_name(), ENT_QUOTES, 'UTF-8' );
	$seller_id      = empty( $data['seller_id'] ) ? 0 : $data['seller_id'];
	$mail_to        = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
	$mail_data      = empty( $data['mail_data'] ) ? array() : $data['mail_data'];
	$approve_status = empty( $data['status'] ) ? 'approve' : $data['status'];
	$first_name     = empty( $data['seller_first_name'] ) ? '' : $data['seller_first_name'];

	$user_name = html_entity_decode( $first_name, ENT_QUOTES, 'UTF-8' );
	$msg       = html_entity_decode( esc_html__( ' here to view it ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );
	$welcome   = html_entity_decode( esc_html__( 'Congrats! Your product ( ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . '<strong>' . $product_name . '</strong> ' . html_entity_decode( esc_html__( ' ) has been published!', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );

	if ( 'approve' !== $approve_status ) {
		$welcome = html_entity_decode( esc_html__( 'Unfortunately! Your product ( ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . '<strong>' . $product_name . '</strong> ' . html_entity_decode( esc_html__( ' ) has been rejected by Admin!', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );
	}

	$review_here = ' <a href="' . get_permalink( $product_id ) . '">' . $mail_data['click_text'] . '</a>';

	do_action( 'woocommerce_email_header', $email_heading, $mail_to );

	$result = '<p>' . $mail_data['hi_msg'] . $user_name . ',</p>
				<p>' . $welcome . '<p>
			<p>' . $review_here . $msg . '</p>';

	if ( ! empty( $additional_content ) ) {
		$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
	}

	echo wp_kses_post( $result );

	do_action( 'woocommerce_email_footer', $mail_to );
}
