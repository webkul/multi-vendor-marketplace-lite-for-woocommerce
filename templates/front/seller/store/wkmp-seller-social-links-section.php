<?php
/**
 * Link Sections.
 *
 * @package WkMarketplace\Template\Fron\Seller
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

echo '<div class="mp-shop-social-links">';

if ( ! empty( $seller_info->social_facebook ) ) {
	echo '<a href="' . esc_url( $seller_info->social_facebook ) . '" target="_blank" class="mp-social-icon fb"></a>';
}
if ( ! empty( $seller_info->social_instagram ) ) {
	echo '<a href="' . esc_url( $seller_info->social_instagram ) . '" target="_blank" class="mp-social-icon instagram"></a>';
}
if ( ! empty( $seller_info->social_twitter ) ) {
	echo '<a href="' . esc_url( $seller_info->social_twitter ) . '" target="_blank" class="mp-social-icon twitter"></a>';
}
if ( ! empty( $seller_info->social_linkedin ) ) {
	echo '<a href="' . esc_url( $seller_info->social_linkedin ) . '" target="_blank" class="mp-social-icon in"></a>';
}
if ( ! empty( $seller_info->social_youtube ) ) {
	echo '<a href="' . esc_url( $seller_info->social_youtube ) . '" target="_blank" class="mp-social-icon yt"></a>';
}
echo '</div>';
