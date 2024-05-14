<?php
/**
 * Link Sections.
 *
 * @package WkMarketplace\Template\Fron\Seller
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

echo '<div class="mp-shop-social-links">';

do_action( 'wkmp_before_social_icons', $seller_info );

if ( ! empty( $seller_info->social_facebook ) ) {
	echo '<a href="' . esc_url( $seller_info->social_facebook ) . '" target="_blank" class="mp-social-icon fb"></a>';
}
if ( ! empty( $seller_info->social_instagram ) ) {
	echo '<a href="' . esc_url( $seller_info->social_instagram ) . '" target="_blank" class="mp-social-icon instagram"></a>';
}
if ( ! empty( $seller_info->social_twitter ) ) {
	echo '<a href="' . esc_url( $seller_info->social_twitter ) . '" target="_blank" class="mp-social-icon twitter-x"><svg viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg></a>';
}
if ( ! empty( $seller_info->social_linkedin ) ) {
	echo '<a href="' . esc_url( $seller_info->social_linkedin ) . '" target="_blank" class="mp-social-icon in"></a>';
}
if ( ! empty( $seller_info->social_youtube ) ) {
	echo '<a href="' . esc_url( $seller_info->social_youtube ) . '" target="_blank" class="mp-social-icon yt"></a>';
}
echo '</div>';
