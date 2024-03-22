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
	$seller_id = empty( $data['user_id'] ) ? 0 : $data['user_id'];
	$mail_data = empty( $data['mail_data'] ) ? array() : $data['mail_data'];
	$mail_to   = empty( $data['mail_to'] ) ? $mail_data['admin_email'] : $data['mail_to'];

	$product_name = html_entity_decode( $_product->get_name(), ENT_QUOTES, 'UTF-8' );
	$user_name    = html_entity_decode( get_user_meta( $seller_id, 'first_name', true ), ENT_QUOTES, 'UTF-8' );

	$request_msg = $mail_data['vendor_label'] . '<strong>' . $user_name . '</strong>' . $mail_data['request_to_publish'] . '<strong>' . $product_name . '</strong> ';
	$review_msg  = html_entity_decode( esc_html__( ' to review and publish the request.', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );
	$review_here = sprintf( admin_url( 'post.php?post=%s&action=edit' ), $product_id );

	do_action( 'woocommerce_email_header', $email_heading, $mail_to );

	$result = '<p>' . $request_msg . '<p>
			<p><a target="_blank" href="' . $review_here . '">' . $mail_data['click_text'] . '</a>' . $review_msg . '</p>';

	if ( ! empty( $additional_content ) ) {
		$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
	}

	echo wp_kses_post( $result );

	do_action( 'woocommerce_email_footer', $mail_to );
}
