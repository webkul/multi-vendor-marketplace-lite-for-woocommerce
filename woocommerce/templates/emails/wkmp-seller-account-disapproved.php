<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$mail_to    = empty( $data['mail_to'] ) ? '' : $data['mail_to'];
$first_name = empty( $data['first_name'] ) ? '' : $data['first_name'];

$msg       = html_entity_decode( esc_html__( 'Your account has been Disapproved by admin ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );
$admin     = get_option( 'admin_email' );
$reference = html_entity_decode( esc_html__( 'If you have any query, please contact us at -', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' );

do_action( 'woocommerce_email_header', $email_heading, $mail_to );

$result = '<p>' . html_entity_decode( esc_html__( 'Hi ', 'wk-marketplace' ), ENT_QUOTES, 'UTF-8' ) . html_entity_decode( $first_name, ENT_QUOTES, 'UTF-8' ) . ',</p>
		<p>' . $msg . '.</p>
		<p>' . $reference . ' <a href="mailto:' . $admin . '">' . $admin . '</a></p>';

if ( ! empty( $additional_content ) ) {
	$result .= '<p> ' . html_entity_decode( $additional_content, ENT_QUOTES, 'UTF-8' ) . '</p>';
}
echo wp_kses_post( $result );

do_action( 'woocommerce_email_footer', $mail_to );
