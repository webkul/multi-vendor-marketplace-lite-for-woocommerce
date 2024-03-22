<?php
/**
 * Seller product edit Variations Product tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

?>
<div class="wkmp_container woocommerce" id="avariationtabwk">
	<div id="wkmp_remove_notice_wrap" class="woocommerce-message notice inline wkmp_hide">
	</div>
	<div id="mp_attribute_variations">
		<?php $this->wkmp_attributes_variation( $wk_pro_id ); ?>
	</div>
	<div class="input_fields_toolbar_variation">
		<div id="mp-loader"></div>
		<button id="mp_var_attribute_call" class="btn btn-success"><?php esc_html_e( '+ Add Variation', 'wk-marketplace' ); ?></button>
	</div>
</div>
