<?php
/**
 * Seller product edit Linked Product tab.
 *
 * @package Multi Vendor Marketplace
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.
$placehoder = esc_attr__( 'Use “|” to separate different options. Enter options for customers to choose from, f.e. “Blue” or “Large”.', 'wk-marketplace' );
?>
<div class="wkmp_container" id="attributestabwk">
	<div class="input_fields_toolbar">
		<p class="wkmp-attribute-placeholder"><?php echo esc_html( $placehoder ); ?></p>
		<button class="btn btn-success add-variant-attribute"><?php esc_html_e( 'Add an attribute', 'wk-marketplace' ); ?></button>
	</div>
	<div class="wk_marketplace_attributes">
		<?php
		if ( ! empty( $product_attributes ) ) {
			$i = 0;
			foreach ( $product_attributes as $key_at => $proatt ) {
				$optin = $product->get_attribute( $key_at );
				$optin = str_replace( ',', ' |', $optin );
				?>

				<div class="wkmp_attributes">
					<div class="box-header attribute-remove">
						<input type="text" class="mp-attributes-name wkmp_product_input" placeholder="Attribute name" name="pro_att[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( str_replace( '-', ' ', esc_attr( $proatt['name'] ) ) ); ?>"/>
						<input type="text" class="option wkmp_product_input" title="<?php echo esc_attr( $placehoder ); ?>" placeholder=" <?php echo esc_attr( $placehoder ); ?>" name="pro_att[<?php echo esc_attr( $i ); ?>][value]" value="<?php echo esc_attr( $proatt['value'] ); ?> "/>
						<input type="hidden" name="pro_att[<?php echo esc_attr( $i ); ?>][position]" class="attribute_position" value="<?php echo esc_attr( $proatt['position'] ); ?>"/>
						<span class="mp_actions">
					<button class="mp_attribute_remove btn btn-danger"><?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
				</span>
					</div>

					<div class="box-inside clearfix">
						<div class="wk-mp-attribute-config">
							<div class="checkbox-inline">
								<input type="checkbox" id="is_visible_page[<?php echo esc_attr( $i ); ?>]" class="checkbox" name="pro_att[<?php echo esc_attr( $i ); ?>][is_visible]" value="1" <?php echo ( 1 === intval( $proatt['is_visible'] ) ) ? 'checked' : ''; ?>/>
								<label for="is_visible_page[<?php echo esc_attr( $i ); ?>]"><?php esc_html_e( 'Visible on the product page', 'wk-marketplace' ); ?></label>
							</div>
							<?php if ( $product->is_type( 'variable' ) ) { ?>
								<div class="checkbox-inline">
									<input type="checkbox" class="checkbox" name="pro_att[<?php echo esc_attr( $i ); ?>][is_variation]" value="1" id="used_for_variation[<?php echo esc_attr( $i ); ?>]" <?php echo ( 1 === intval( $proatt['is_variation'] ) ) ? 'checked' : ''; ?>/>
									<label for="used_for_variation[<?php echo esc_attr( $i ); ?>]"><?php esc_html_e( 'Used for variations', 'wk-marketplace' ); ?></label>
								</div>
							<?php } ?>
							<input type="hidden" name="pro_att[<?php echo esc_attr( $i ); ?>][is_taxonomy]" value="<?php echo isset( $proatt['taxonomy'] ) ? esc_attr( $proatt['taxonomy'] ) : ''; ?>"/>
						</div>
						<div class="attribute-options"></div>
					</div>
				</div>
				<?php
				++$i;
			}
		}
		?>
	</div>
</div>
