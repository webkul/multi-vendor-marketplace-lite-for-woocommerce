<?php
/**
 * Email templates.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

global $wpdb;

if ( $data ) {
	$query_id = $data['q_id'];
	$adm_msg  = mb_convert_encoding( $data['adm_msg'] );
	$query    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where id = %d", $query_id ) );

	if ( $query ) {
		$q_data            = $query[0];
		$msg               = esc_html__( 'We received your query about: ', 'wk-marketplace' );
		$admin             = esc_html__( 'Message : ', 'wk-marketplace' );
		$admin_message     = mb_convert_encoding( $q_data->message, 'UTF-8' );
		$reference         = esc_html__( 'Subject : ', 'wk-marketplace' );
		$reference_message = mb_convert_encoding( $q_data->subject, 'UTF-8' );
		$adm_ans           = esc_html__( 'Answer : ', 'wk-marketplace' );
		$closing_msg       = esc_html__( 'Please, do contact us if you have additional queries. Thanks again!', 'wk-marketplace' );
		$footer_text       = apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );

		echo '= ' . wp_kses_post( mb_convert_encoding( $email_heading, 'UTF-8' ) ) . " =\n\n";

		echo sprintf( /* translators: %s Customer first name */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_html( mb_convert_encoding( $customer_email, 'UTF-8' ) ) ) . "\n\n";

		echo wp_kses_post( $msg ) . "\n";
		echo wp_kses_post( $reference_message ) . "\n";
		echo wp_kses_post( $admin ) . ' ' . wp_kses_post( $admin_message ) . "\n";
		echo wp_kses_post( $adm_ans ) . ' ' . wp_kses_post( $adm_msg ) . "\n";
		echo wp_kses_post( $closing_msg ) . "\n";

		echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

		echo wp_kses_post( $footer_text );
	}
}
