<?php
/**
 * Seller product edit Linked Product tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.
$placeholder = esc_attr__( 'Use “|” to separate different options. Enter options for customers to choose from, f.e. “Blue” or “Large”.', 'wk-marketplace' );
?>
<div class="wkmp_container" id="attributestabwk">
	<div class="input_fields_toolbar">
		<div class="wkmp-attribute-add-wrap">
			<button class="btn btn-success wkmp-add-variant-attribute"><?php esc_html_e( 'Add New Attribute', 'wk-marketplace' ); ?></button>
			<?php do_action( 'wkmp_after_add_attribute_button', $product_auth, $product_attributes ); ?>
		</div>
		<p class="wkmp-attribute-placeholder"><?php echo esc_html( $placeholder ); ?></p>
	</div>
	<div class="wk_marketplace_attributes">
		<?php
		if ( ! empty( $product_attributes ) ) {
			$pro_disabled = $this->wkmarketplace->wkmp_is_pro_module_disabled();
			$i            = 0;

			foreach ( $product_attributes as $attr_name => $proatt ) {
				$is_global = empty( $proatt['is_global'] ) ? 0 : 1;
				?>
				<div class="wkmp_attributes">
					<div class="box-header attribute-remove">
						<?php
						if ( $is_global && ! $pro_disabled ) {
							do_action( 'wkmp_create_product_attribute_terms_select', $proatt, $i );
						} else {
							?>
							<input type="text" class="mp-attributes-name wkmp_product_input" placeholder="<?php esc_attr_e( 'Attribute name', 'wk-marketplace' ); ?>" name="pro_att[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( str_replace( '-', ' ', esc_attr( $attr_name ) ) ); ?>"/>
							<input type="text" class="option wkmp_product_input" title="<?php echo esc_attr( $placeholder ); ?>" placeholder=" <?php echo esc_attr( $placeholder ); ?>" name="pro_att[<?php echo esc_attr( $i ); ?>][value]" value="<?php echo esc_attr( $proatt['value'] ); ?> "/>
							<?php
						}
						?>
						<input type="hidden" name="pro_att[<?php echo esc_attr( $i ); ?>][position]" class="attribute_position" value="<?php echo esc_attr( $proatt['position'] ); ?>"/>
						<span class="mp_actions"><button class="mp_attribute_remove btn btn-danger"><?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button></span>
					</div>

					<div class="box-inside clearfix">
						<div class="wk-mp-attribute-config">
							<div class="checkbox-inline">
								<input type="checkbox" id="is_visible_page[<?php echo esc_attr( $i ); ?>]" class="checkbox" name="pro_att[<?php echo esc_attr( $i ); ?>][is_visible]" value="1" <?php checked( 1, intval( $proatt['is_visible'] ), true ); ?>/>
								<label class="wkmp-visible-on-product-page-label" for="is_visible_page[<?php echo esc_attr( $i ); ?>]">
									<?php
									echo wp_kses(
										apply_filters( 'wkmp_visible_on_product_page_label', esc_html__( 'Visible on the product page', 'wk-marketplace' ), $is_global, $proatt ),
										array(
											'span' => array( 'class' => array() ),
											'a'    => array(
												'class' => array(),
												'data-select2_id' => array(),
											),
										)
									);
									?>
								</label>
							</div>
							<?php if ( $product->is_type( 'variable' ) ) { ?>
								<div class="checkbox-inline">
									<input type="checkbox" class="checkbox" name="pro_att[<?php echo esc_attr( $i ); ?>][is_variation]" value="1" id="used_for_variation[<?php echo esc_attr( $i ); ?>]" <?php echo ( 1 === intval( $proatt['is_variation'] ) ) ? 'checked' : ''; ?>/>
									<label for="used_for_variation[<?php echo esc_attr( $i ); ?>]"><?php esc_html_e( 'Used for variations', 'wk-marketplace' ); ?></label>
								</div>
							<?php } ?>
							<input type="hidden" name="pro_att[<?php echo esc_attr( $i ); ?>][is_global]" value="<?php echo isset( $proatt['is_global'] ) ? esc_attr( $proatt['is_global'] ) : ''; ?>"/>
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
