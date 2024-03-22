<?php
/**
 * Seller product edit Affiliate Product tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

?>
<div class="wkmp_container" id="external_affiliate_tabwk">
	<div class="wkmp_profile_input">
		<label for="product_url"><?php esc_html_e( 'Product URL', 'wk-marketplace' ); ?></label>
		<input class="wkmp_product_input" type="text" name="product_url" id="product_url" size="54" value="<?php echo ( isset( $meta_arr['_product_url'] ) ) ? esc_url( $meta_arr['_product_url'] ) : ''; ?>"/>
		<div id="pro_url_error" class="wkmp-error-class"></div>
	</div>

	<div class="wkmp_profile_input">
		<label for="button_txt"><?php esc_html_e( 'Button Text', 'wk-marketplace' ); ?></label>
		<input class="wkmp_product_input" type="text" name="button_txt" id="button_txt" size="54" value="<?php echo isset( $meta_arr['_button_text'] ) ? esc_attr( $meta_arr['_button_text'] ) : ''; ?>"/>
		<div id="pro_btn_txt_error" class="wkmp-error-class"></div>
	</div>
</div>
