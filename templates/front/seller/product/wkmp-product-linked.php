<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>
<div id="linked_product_data" class="wkmp_profile_input">
	<div class="options_group show_if_grouped">
		<p class="form-field">
			<label for="grouped_products"><?php esc_html_e( 'Grouped products', 'wk-marketplace' ); ?></label>
			<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="mp_grouped_products[]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search&hellip;', 'wk-marketplace' ); ?>">
				<?php
				$product_ids = $product->is_type( 'grouped' ) ? $product->get_children( 'edit' ) : array();
				foreach ( $product_array as $key => $value ) {
					$item = wc_get_product( $value->ID );
					if ( is_object( $item ) && $wk_pro_id !== $value->ID ) {
						?>
						<option value="<?php echo esc_attr( $value->ID ); ?>" <?php echo in_array( intval( $value->ID ), $product_ids, true ) ? 'selected' : ''; ?>>
							<?php echo wp_kses_data( $item->get_formatted_name() ); ?>
						</option>
						<?php
					}
				}
				?>
			</select>
		</p>
	</div>
</div>
