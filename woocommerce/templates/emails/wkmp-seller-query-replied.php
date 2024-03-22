<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( $data ) {
	$mail_to    = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
	$mail_data  = empty( $data['mail_data'] ) ? array() : $data['mail_data'];
	$reply_data = empty( $data['reply_data'] ) ? array() : $data['reply_data'];
	$query_info = empty( $reply_data['query_info'] ) ? array() : map_deep( $reply_data['query_info'], 'sanitize_text_field' );

	$msg         = esc_html__( 'We have received your query about: ', 'wk-marketplace' ) . "\r\n\r\n";
	$closing_msg = esc_html__( 'Please, do contact us if you have additional queries. Thanks again!', 'wk-marketplace' );

	do_action( 'woocommerce_email_header', $email_heading, $mail_to );

	$result = '
			<p>' . $mail_data['hi_msg'] . ',</p>
			<p>' . $msg . '</p>
			<p><strong>' . $mail_data['subject_label'] . '</strong>' . stripslashes( wptexturize( $query_info->subject ) ) . '</p>
			<p><strong>' . $mail_data['message_label'] . '</strong>' . stripslashes( wptexturize( $query_info->message ) ) . '</p>
			<p><strong>' . $mail_data['answer_label'] . '</strong>' . stripslashes( wptexturize( str_replace( '<br />', "\n", html_entity_decode( $reply_data['reply_message'], ENT_QUOTES, 'UTF-8' ) ) ) ) . '</p>
			<p>' . $closing_msg . '</p>';

	if ( ! empty( $additional_content ) ) {
		$result .= wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	}
	echo wp_kses_post( $result );

	do_action( 'woocommerce_email_footer', $mail_to );
}
