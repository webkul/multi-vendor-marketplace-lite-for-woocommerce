<?php
/**
 * Seller product edit inventory tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

?>
<div class="wkmp_container" id="inventorytabwk">
	<?php
	if ( 'external' !== $product->get_type() ) {
		?>
	<div class="wkmp_profile_input">
		<label for="wk-mp-stock"><?php esc_html_e( 'Manage Stock', 'wk-marketplace' ); ?></label>
		<p>
			<input type="checkbox" class="wkmp_stock_management" id="wk_stock_management" name="wk_stock_management" value="yes" <?php echo ( 'yes' === $meta_arr['_manage_stock'] ) ? 'checked' : ''; ?>/>
			<label for="wk_stock_management"><?php esc_html_e( 'Enable stock management at product level', 'wk-marketplace' ); ?></label>
		</p>
	</div>
		<?php
		$show_stock_fields = 'display:none;';
		$hide_stock_status = 'display:block;';

		if ( 'yes' === $meta_arr['_manage_stock'] ) {
			$show_stock_fields = 'display:block;';
			$hide_stock_status = 'display:none;';
		}
		?>
	<div class="wkmp_profile_input" style="<?php echo esc_attr( $show_stock_fields ); ?>">
		<label for="wk-mp-stock"><?php esc_html_e( 'Stock Qty', 'wk-marketplace' ); ?></label>
		<input type="text" class="wkmp_product_input" placeholder="0" name="wk-mp-stock-qty" id="wk-mp-stock-qty" value="<?php echo isset( $meta_arr['_stock'] ) ? esc_attr( $meta_arr['_stock'] ) : ''; ?>"/>
	</div>

	<div class="wkmp_profile_input" style="<?php echo esc_attr( $show_stock_fields ); ?>">
		<label for="wk-mp-backorders"><?php esc_html_e( 'Allow Backorders', 'wk-marketplace' ); ?></label>
		<select name="_backorders" id="_backorders" class="form-control">
			<option value="no" <?php echo ( isset( $meta_arr['_backorders'] ) && 'no' === $meta_arr['_backorders'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Do not allow', 'wk-marketplace' ); ?></option>
			<option value="notify" <?php echo ( isset( $meta_arr['_backorders'] ) && 'notify' === $meta_arr['_backorders'] ) ? 'selected="selected"' : ''; ?>> <?php esc_html_e( 'Allow but notify customer', 'wk-marketplace' ); ?></option>
			<option value="yes" <?php echo ( isset( $meta_arr['_backorders'] ) && 'yes' === $meta_arr['_backorders'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Allow', 'wk-marketplace' ); ?></option>
		</select>
	</div>

	<div class="wkmp_profile_input" style="<?php echo esc_attr( $show_stock_fields ); ?>">
		<label for="wk-mp-stock-threshold"><?php esc_html_e( 'Low stock threshold', 'wk-marketplace' ); ?></label>
		<input type="text" class="wkmp_product_input" placeholder="<?php echo esc_attr__( '0', 'wk-marketplace' ); ?>" name="wk-mp-stock-threshold" id="wk-mp-stock-threshold" value="<?php echo isset( $meta_arr['_low_stock_amount'] ) ? esc_attr( $meta_arr['_low_stock_amount'] ) : ''; ?>"/>
	</div>

	<div class="wkmp_profile_input" style="<?php echo esc_attr( $hide_stock_status ); ?>">
		<label for="wk-mp-stock"><?php esc_html_e( 'Stock Status', 'wk-marketplace' ); ?></label>
		<select name="_stock_status" id="_stock_status" class="form-control">
			<option value="instock" <?php echo ( isset( $meta_arr['_stock_status'] ) && 'instock' === $meta_arr['_stock_status'] ) ? 'selected="selected"' : ''; ?>> <?php esc_html_e( 'In Stock', 'wk-marketplace' ); ?></option>
			<option value="outofstock" <?php echo ( isset( $meta_arr['_stock_status'] ) && 'outofstock' === $meta_arr['_stock_status'] ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Out of Stock', 'wk-marketplace' ); ?></option>
		</select>
	</div>

	<div class="wkmp_profile_input">
		<label for="wk-mp-sold-individual"><?php esc_html_e( 'Sold individually', 'wk-marketplace' ); ?></label>
		<p>
			<input type="checkbox" class="wkmp_sold_individual" id="wk_sold_individual" name="wk_sold_individual" value="yes" <?php echo ( isset( $meta_arr['_sold_individually'] ) && 'yes' === $meta_arr['_sold_individually'] ) ? 'checked' : ''; ?>/>
			<label for="wk_sold_individual"><?php esc_html_e( 'Enable this to only allow one of this item to be bought in a single order', 'wk-marketplace' ); ?></label>
		</p>
	</div>
		<?php
	}

	if ( 'grouped' !== $product->get_type() ) {
		$qty_limit         = get_user_meta( $product_auth, '_wkmp_max_product_qty_limit', true );
		$qty_limit         = empty( $qty_limit ) ? get_option( '_wkmp_max_product_qty_limit', 0 ) : $qty_limit;
		$prod_qty_limit    = isset( $meta_arr['_wkmp_max_product_qty_limit'] ) ? $meta_arr['_wkmp_max_product_qty_limit'] : '';
		$sold_individually = $product->get_sold_individually();
		$qty_limit_css     = $sold_individually ? 'style=display:none' : '';
		?>
		<div class="wkmp_profile_input wkmp-max-product-qty-limit" <?php echo esc_attr( $qty_limit_css ); ?>>
			<label for="_wkmp_max_product_qty_limit"><?php printf( /* Translators: %s: Quantity Limit. */ esc_html__( 'Maximum Purchasable Quantity (Globally set value is: %s)', 'wk-marketplace' ), esc_html( $qty_limit ) ); ?></label>
			<p>
				<input min="0" type="number" class="wkmp_product_input" name="_wkmp_max_product_qty_limit" placeholder="<?php esc_attr_e( 'Enter maximum allowed quantity for this product that can be purchased.', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $prod_qty_limit ); ?>"/>
			</p>
		</div>

		<?php
	}
	do_action( 'mp_edit_product_field', $wk_pro_id );
	?>
</div>
