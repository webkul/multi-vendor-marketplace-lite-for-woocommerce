<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>
<form action="<?php echo esc_url( get_permalink() . get_option( '_wkmp_add_product_endpoint', 'seller-add-product' ) ); ?>" method="post">
	<table style="width:100%">
		<tbody>
		<tr>
			<td>
				<label for="mp_seller_product_categories"><?php esc_html_e( 'Product categories', 'wk-marketplace' ); ?></label>
			</td>
			<td>
			<?php
			echo wp_kses(
				str_replace( '<select', '<select style="width:100%" data-placeholder="' . esc_attr__( 'Choose category(s)', 'wk-marketplace' ) . '" multiple="multiple" ', $product_categories ),
				array(
					'select' => array(
						'data-placeholder' => array(),
						'multiple'         => array(),
						'id'               => array(),
						'name'             => array(),
						'class'            => array(),
						'style'            => array(),
						'tabindex'         => array(),
						'aria-hidden'      => array(),
					),
					'option' => array(
						'value'    => array(),
						'selected' => array(),
					),
				)
			);
			?>
			</td>
		</tr>
		<tr>
			<td>
				<label for="product_type"><?php esc_html_e( 'Product Type', 'wk-marketplace' ); ?></label>
			</td>
			<td>
				<select name="product_type" id="product_type" class="mp-toggle-select">
				<?php
				foreach ( $mp_product_types as $key => $pro_type ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $pro_type ); ?></option>
					<?php
				}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<?php wp_nonce_field( 'wkmp_select_type_cat_nonce_action', 'wkmp_select_type_cat_nonce_name' ); ?>
				<input type="submit" name="wkmp_add_product_next_step" id="wkmp_add_product_next_step" value='<?php esc_attr_e( 'Next', 'wk-marketplace' ); ?>' class="button"/>
			</td>
		</tr>
		</tbody>
	</table>
</form>


