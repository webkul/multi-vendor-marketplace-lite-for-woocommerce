<?php
/**
 * Seller product edit Linked Product tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

?>
<div class="wkmp_container" id="linkedtabwk">
	<?php
	if ( $product->is_type( 'grouped' ) ) {
		include __DIR__ . '/wkmp-product-linked.php';
	}
	?>
	<div class="options_group wkmp_profile_input">
		<p class="form-field">
			<label for="upsell_ids"><?php esc_html_e( 'Upsells', 'wk-marketplace' ); ?></label>
			<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="upsell_ids" name="upsell_ids[]" data-placeholder="<?php esc_attr_e( 'Search&hellip;', 'wk-marketplace' ); ?>">
				<?php
				$upsell_ids = $product->get_upsell_ids( 'edit' );
				$upsell_ids = array_map( 'intval', $upsell_ids );

				foreach ( $product_array as $key => $value ) {
					$product_id = empty( $value->ID ) ? 0 : intval( $value->ID );
					$item       = wc_get_product( $product_id );

					if ( is_object( $item ) && intval( $wk_pro_id ) !== $product_id ) {
						?>
						<option value="<?php echo esc_attr( $value->ID ); ?>" <?php echo in_array( $product_id, $upsell_ids, true ) ? 'selected' : ''; ?>> <?php echo wp_kses_post( $item->get_formatted_name() ); ?></option>
					<?php } ?>
				<?php } ?>
			</select>
		</p>
		<?php if ( ! $product->is_type( 'external' ) && ! $product->is_type( 'grouped' ) ) { ?>
			<p class="form-field hide_if_grouped hide_if_external">
				<label for="crosssell_ids"><?php esc_html_e( 'Cross-sells', 'wk-marketplace' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="crosssell_ids" name="crosssell_ids[]" data-placeholder="<?php esc_attr_e( 'Search&hellip;', 'wk-marketplace' ); ?>">
					<?php
					$crosssell_ids = $product->get_cross_sell_ids( 'edit' );
					$crosssell_ids = array_map( 'intval', $crosssell_ids );

					foreach ( $product_array as $key => $value ) {
						$product_id = empty( $value->ID ) ? 0 : intval( $value->ID );
						$item       = wc_get_product( $product_id );

						if ( is_object( $item ) && intval( $wk_pro_id ) !== $product_id ) {
							?>
							<option value="<?php echo esc_attr( $value->ID ); ?>" <?php echo ( in_array( $product_id, $crosssell_ids, true ) ) ? 'selected' : ''; ?>><?php echo wp_kses_post( $item->get_formatted_name() ); ?></option>
							<?php
						}
					}
					?>
				</select>
			</p>
		<?php } ?>
	</div>
</div>
