<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$seller_id               = get_current_user_id();
$dynamic_sku_enabled     = get_user_meta( $seller_id, '_wkmp_enable_seller_dynamic_sku', true );
$dynamic_sku_prefix      = get_user_meta( $seller_id, '_wkmp_dynamic_sku_prefix', true );
$variable_product_images = '';

if ( $thumb_id ) {
	$variable_product_images = get_post_meta( $thumb_id, '_wp_attached_file', true );
}

$variable_image = WKMP_LITE_PLUGIN_URL . 'assets/images/placeholder.png';
if ( ! empty( $variable_product_images ) ) {
	$variable_image = content_url() . '/uploads/' . $variable_product_images;
}

$postmeta_row_data = get_post_meta( $wk_pro_id );

foreach ( $postmeta_row_data as $key => $value ) {
	$meta_arr[ $key ] = $value[0];
}

$product_attributes = get_post_meta( $wk_pro_id, '_product_attributes', true );
$postmeta_variation = get_post_meta( $variation_id );

foreach ( $postmeta_variation as $key => $value ) {
	$variation_arr[ $key ] = $value[0];
}

$wc_currency = get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) );

$show_stock_fields = 'display:none;';
$show_stock_status = 'display:table-row;';

if ( isset( $variation_arr['_manage_stock'] ) && 'yes' === $variation_arr['_manage_stock'] ) {
	$show_stock_fields = 'display:table-row;';
	$show_stock_status = 'display:none;';
}
$hide_virtual_style = 'display:' . ( isset( $variation_arr['_virtual'] ) && 'yes' === $variation_arr['_virtual'] ) ? 'none' : 'table-row'
?>

<div class="wkmp_marketplace_variation">
	<h3>
		<button type="button" data-var_id="<?php echo esc_attr( $variation_id ); ?>" class="mp_attribute_remove btn btn-danger wkmp_var_btn" rel="<?php echo esc_attr( $variation_id ); ?>"><?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
		<input type='hidden' value="<?php echo esc_attr( $variation_id ); ?>" name="mp_attribute_variation_name[]" id="mp_attribute_variation_id_<?php echo esc_attr( $variation_id ); ?>"/>
		<?php foreach ( is_iterable( $product_attributes ) ? $product_attributes : array() as $variation ) { ?>
			<label><?php echo esc_html( ucfirst( str_replace( '-', ' ', $variation['name'] ) ) ) . ' '; ?></label>
			<?php
			if ( 1 === $variation['is_variation'] ) {
				$var_name = 'attribute_' . sanitize_title( $variation['name'] ) . '';
				?>
				<input type="hidden" value="<?php echo esc_attr( $variation['name'] ); ?>" name="mp_attribute_name[<?php echo esc_attr( $variation_id ); ?>][]"/>
				<select name="attribute_<?php echo esc_attr( $variation['name'] ); ?>[<?php echo esc_attr( $variation_id ); ?>]">
					<option value=""><?php printf( /* translators: %s: Variation name. */ esc_attr__( 'Choose %s', 'wk-marketplace' ), esc_attr( str_replace( '-', ' ', $variation['name'] ) ) ); ?></option>
					<?php
					$att_val = explode( '|', $variation['value'] );
					foreach ( $att_val as $value ) {
						?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php echo ( isset( $variation_arr[ $var_name ] ) && trim( $value ) === trim( $variation_arr[ $var_name ] ) ) ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
						<?php
					}
					?>
				</select>
				<?php
			}
		}
		?>
		<input class="variation_menu_order" name="wkmp_variation_menu_order[<?php echo esc_attr( $variation_id ); ?>]" value="0" type="hidden">
	</h3>

	<table style="display: table;" cellpadding="0" cellspacing="0">
		<tbody>
		<tr>
			<td class="variation_data" rowspan="2">
				<table class="data_table" cellpadding="0" cellspacing="0" style="display:table;">
					<tbody>
					<tr class="variable_pricing">
						<td style="width: 50%;">
							<label><?php esc_html_e( 'Regular Price: ', 'wk-marketplace' ) . '(' . $wc_currency . ')'; ?></label>
							<input size="5" name="wkmp_variable_regular_price[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_regular_price'] ) ? esc_attr( wc_format_localized_decimal( $variation_arr['_regular_price'] ) ) : ''; ?>" class="wc_input_price wkmp_variable_regular_price wkmp_product_input" placeholder="<?php esc_attr_e( 'Variation price (required)', 'wk-marketplace' ); ?>" type="text">
						</td>
						<td>
							<label><?php esc_html_e( 'Sale Price: ', 'wk-marketplace' ) . '(' . $wc_currency . ')'; ?>
								<a href="javascript:void(0);" class="mp_sale_schedule"><?php esc_html_e( 'Schedule', 'wk-marketplace' ); ?></a><a href="javascript:void(0);" class="mp_cancel_sale_schedule" style="display:none"><?php esc_html_e( 'Cancel schedule', 'wk-marketplace' ); ?></a>
							</label>
							<input size="5" name="wkmp_variable_sale_price[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_sale_price'] ) ? esc_attr( wc_format_localized_decimal( $variation_arr['_sale_price'] ) ) : ''; ?>" class="wc_input_price wkmp_variable_sale_price wkmp_product_input" type="text">
							<span class="sale_pr_error error-class"></span>
						</td>
					</tr>
					<tr class="mp_sale_price_dates_fields" style="display:none">
						<td>
							<label><?php esc_html_e( 'Sale start date: ', 'wk-marketplace' ); ?></label>
							<input  type="date" id="wkmp_variation_sale_start_date_<?php echo esc_attr( $variation_id ); ?>" class="sale_price_dates_from hasDatepicker wkmp_product_input" name="wkmp_variable_sale_price_dates_from[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_sale_price_dates_from'] ) ? esc_attr( $variation_arr['_sale_price_dates_from'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'From… YYYY-MM-DD', 'wk-marketplace' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" >
						</td>
						<td>
							<label><?php esc_html_e( 'Sale end date: ', 'wk-marketplace' ); ?></label>
							<input type="date" class="hasDatepicker wkmp_product_input" id="wkmp_variation_sale_end_date_<?php echo esc_attr( $variation_id ); ?>" name="wkmp_variable_sale_price_dates_to[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_sale_price_dates_to'] ) ? esc_attr( $variation_arr['_sale_price_dates_to'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'To… YYYY-MM-DD', 'wk-marketplace' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
						</td>
					</tr>
					<tr class="mpshow_if_variation_manage_stock wkmp_stock_qty" style="<?php echo esc_attr( $show_stock_fields ); ?>">
						<td>
							<label><?php esc_html_e( 'Stock Qty: ', 'wk-marketplace' ); ?></label>
							<input class="wkmp_variable_stock wkmp_product_input" size="5" name="wkmp_variable_stock[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_stock'] ) ? esc_attr( $variation_arr['_stock'] ) : ''; ?>" step="any" type="number">
						</td>
						<td>
							<label><?php esc_html_e( 'Allow Backorders?', 'wk-marketplace' ); ?></label>
							<select name="wkmp_variable_backorders[<?php echo esc_attr( $variation_id ); ?>]" style="width:100%;">
								<option value="no" <?php echo ( isset( $variation_arr['_backorders'] ) && 'no' === $variation_arr['_backorders'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Do not allow', 'wk-marketplace' ); ?></option>
								<option value="notify" <?php echo ( isset( $variation_arr['_backorders'] ) && 'notify' === $variation_arr['_backorders'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Allow, but notify customer', 'wk-marketplace' ); ?></option>
								<option value="yes" <?php echo ( isset( $variation_arr['_backorders'] ) && 'yes' === $variation_arr['_backorders'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Allow', 'wk-marketplace' ); ?></option>
							</select>
						</td>
					</tr>
					<tr class="wkmp_stock_status" style="<?php echo esc_attr( $show_stock_status ); ?>">
						<td colspan="2">
							<label><?php esc_html_e( 'Stock status', 'wk-marketplace' ); ?></label>
							<select name="wkmp_variable_stock_status[<?php echo esc_attr( $variation_id ); ?>]" style="width:100%;">
								<option value="instock" <?php echo ( isset( $variation_arr['_stock_status'] ) && 'instock' === $variation_arr['_stock_status'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'In stock', 'wk-marketplace' ); ?></option>
								<option value="outofstock" <?php echo ( isset( $variation_arr['_stock_status'] ) && 'outofstock' === $variation_arr['_stock_status'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Out of stock', 'wk-marketplace' ); ?></option>
							</select>
						</td>
					</tr>
					<tr class="virtual" style="<?php echo esc_attr( $hide_virtual_style ); ?>">
						<td style="display: table-cell;" class="mp_hide_if_variation_virtual">
							<label><?php echo wp_sprintf( /* translators: %s: Weight unit. */ esc_html__( 'Weight (%s):', 'wk-marketplace' ), esc_html( get_option( 'woocommerce_weight_unit', 'kg' ) ) ); ?></label>
							<input size="5" name="wkmp_variable_weight[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_weight'] ) ? esc_attr( wc_format_localized_decimal( $variation_arr['_weight'] ) ) : ''; ?>" placeholder="0" class="wc_input_decimal wkmp_product_input" type="text">
						</td>
						<td style="display: table-cell;" class="dimensions_field mp_hide_if_variation_virtual">
							<label for="product_length"><?php echo wp_sprintf( /* translators: %s: Weight unit. */ esc_html__( 'Dimensions (L×W×H) (%s):', 'wk-marketplace' ), esc_html( get_option( 'woocommerce_dimension_unit', 'cm' ) ) ); ?></label>
							<input id="product_length_<?php echo esc_attr( $variation_id ); ?>" class="input-text wc_input_decimal wkmp_product_input" size="6" name="wkmp_variable_length[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_length'] ) ? esc_attr( wc_format_localized_decimal( $variation_arr['_length'] ) ) : ''; ?>" placeholder="0" type="text">
							<input class="input-text wc_input_decimal wkmp_product_input" size="6" name="wkmp_variable_width[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_width'] ) ? esc_attr( wc_format_localized_decimal( $variation_arr['_width'] ) ) : ''; ?>" placeholder="0" type="text">
							<input class="input-text wc_input_decimal last wkmp_product_input" size="6" name="wkmp_variable_height[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_height'] ) ? esc_attr( wc_format_localized_decimal( $variation_arr['_height'] ) ) : ''; ?>" placeholder="0" type="text">
						</td>
					</tr>

					<?php do_action( 'mp_edit_variable_product_field', $variation_id ); ?>

					<tr class="downloadable mpshow_if_variation_wkmp_downloadable<?php echo esc_attr( $variation_id ); ?>" style="display: <?php echo ( isset( $variation_arr['_downloadable'] ) && 'yes' === $variation_arr['_downloadable'] ) ? 'table-row' : 'none'; ?>">
						<td colspan="2">
							<div class="form-field downloadable_files">
								<label><?php esc_html_e( 'Downloadable Files:', 'wk-marketplace' ); ?></label>
								<div class="widefat">
									<div class="wkmp_variation_downloadable_file" id="variation_downloadable_file_<?php echo esc_attr( $variation_id ); ?>">
										<?php
										$product_vars_downloadables = get_post_meta( $variation_id, '_downloadable_files', true );

										if ( empty( $product_vars_downloadables ) ) {
											$product_vars_downloadables    = array();
											$product_vars_downloadables[0] = array();
										}
										$i = 0;

										foreach ( $product_vars_downloadables as $pro_downloadable ) {
											?>
											<div class="tr_div">
												<div>
													<label for="downloadable_upload_file_name_<?php echo esc_attr( $variation_id . '_' . $i ); ?>"><?php esc_html_e( 'File Name', 'wk-marketplace' ); ?></label>
													<input type="text" class="input_text wkmp_product_input" placeholder="<?php esc_attr_e( 'File Name', 'wk-marketplace' ); ?>" id="downloadable_upload_file_name_<?php echo esc_attr( $variation_id . '_' . $i ); ?>" name="_mp_variation_downloads_files_name[<?php echo esc_attr( $variation_id ); ?>][<?php echo esc_attr( $i ); ?>]" value="<?php echo isset( $pro_downloadable['name'] ) ? esc_attr( $pro_downloadable['name'] ) : ''; ?>">
												</div>
												<div class="file_url">
													<label for="downloadable_upload_file_url_<?php echo esc_attr( $variation_id . '_' . $i ); ?>"><?php esc_html_e( 'File Url', 'wk-marketplace' ); ?></label>
													<input type="text" class="input_text wkmp_product_input" placeholder="http://" id="downloadable_upload_file_url_<?php echo esc_attr( $variation_id . '_' . $i ); ?>" name="_mp_variation_downloads_files_url[<?php echo esc_attr( $variation_id ); ?>][<?php echo esc_attr( $i ); ?>]" value="<?php echo isset( $pro_downloadable['file'] ) ? esc_attr( $pro_downloadable['file'] ) : ''; ?>">
													<a href="javascript:void(0);" class="button wkmp_downloadable_upload_file" id="<?php echo esc_attr( $variation_id . '_' . $i ); ?>"><?php esc_html_e( 'Choose&nbsp;file', 'wk-marketplace' ); ?></a>
													<a href="javascript:void(0);" class="delete mp_var_del" id="mp_var_del_<?php echo esc_attr( $variation_id . '_' . $i ); ?>"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></a>
												</div>
												<div class="file_url_choose"></div>
											</div>
											<?php ++$i; ?>
										<?php } ?>
									</div>
								</div>
							</div>
						</td>
					</tr>
					<tr class="downloadable" style="display:<?php echo ( isset( $variation_arr['_downloadable'] ) && 'yes' === $variation_arr['_downloadable'] ) ? 'table-row' : 'none'; ?>">
						<th colspan="4" class="wkmp_add_file_middle">
							<a href="javascript:void(0);" class="button mp_varnew_file mp_var_down_load_call_<?php echo esc_attr( $variation_id ); ?>" id="<?php echo esc_attr( $variation_id ); ?>"><?php esc_html_e( 'Add File', 'wk-marketplace' ); ?></a>
						</th>
					</tr>
					<?php
					if ( isset( $variation_arr['_downloadable'] ) && 'yes' === $variation_arr['_downloadable'] ) {
						?>
						<tr class="downloadable mpshow_if_variation_wkmp_downloadable<?php echo esc_attr( $variation_id ); ?>" style="display:<?php echo ( isset( $variation_arr['_downloadable'] ) && 'yes' === $variation_arr['_downloadable'] ) ? 'table-row' : 'none'; ?>">
							<td>
								<div>
									<label><?php esc_html_e( 'Download Limit:', 'wk-marketplace' ); ?></label>
									<input size="5" name="wkmp_variable_download_limit[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_download_limit'] ) ? esc_attr( $variation_arr['_download_limit'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wk-marketplace' ); ?>" step="1" min="0" type="number" class="wkmp_product_input"/>
								</div>
							</td>
							<td>
								<div>
									<label><?php esc_html_e( 'Download Expiry:', 'wk-marketplace' ); ?></label>
									<input size="5" name="wkmp_variable_download_expiry[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_download_expiry'] ) ? esc_attr( $variation_arr['_download_expiry'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wk-marketplace' ); ?>" step="1" min="0" type="number" class="wkmp_product_input"/>
								</div>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</td>

			<td class="sku" colspan="2">
				<label><?php esc_html_e( 'SKU', 'wk-marketplace' ); ?>
					<?php
					if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
						?>
						<span class="wkmp-seller-prefix">(<?php printf( /* Translators: %s: SKU prefix. */ esc_html__( 'Prefix: %s', 'wk-marketplace' ), esc_html( $dynamic_sku_prefix ) ); ?>)</span>
						<?php
					}
					?>
				</label>
				<input size="5" id="wkmp_variation_sku_<?php echo esc_attr( $variation_id ); ?>" class="wkmp_variable_sku wkmp_product_input" name="wkmp_variable_sku[<?php echo esc_attr( $variation_id ); ?>]" value="<?php echo isset( $variation_arr['_sku'] ) ? esc_attr( $variation_arr['_sku'] ) : ''; ?>" placeholder="<?php echo isset( $variation_arr['_sku'] ) ? esc_attr( $variation_arr['_sku'] ) : ''; ?>" type="text">
				<div class="wk_variable_sku_err error-class"></div>
			</td>
		</tr>
		<tr>
			<td class="wkmp_upload_image_variation">
				<a href="javascript:void(0);" class="upload_var_image_button" id="var_img_<?php echo esc_attr( $variation_id ); ?>"><img src="<?php echo esc_url( $variable_image ); ?>" id="wkmp_variation_product_var_img_<?php echo esc_attr( $variation_id ); ?>"><input name="upload_var_img[<?php echo esc_attr( $variation_id ); ?>]" id="upload_var_img_<?php echo esc_attr( $variation_id ); ?>" value="<?php echo esc_attr( $thumb_id ); ?>" type="hidden"></a>
			</td>
			<td class="options">
				<label>
					<input class="checkbox" name="wkmp_variable_enabled[<?php echo esc_attr( $variation_id ); ?>]" type="checkbox" checked='checked'>
					<?php esc_html_e( 'Enabled', 'wk-marketplace' ); ?>
				</label>
				<label title="check/uncheck to sell downloadable products">
					<input class="checkbox checkbox_is_downloadable" id="wkmp_downloadable<?php echo esc_attr( $variation_id ); ?>" value="yes" name="wkmp_variable_is_downloadable[<?php echo esc_attr( $variation_id ); ?>]" type="checkbox" <?php echo ( isset( $variation_arr['_downloadable'] ) && 'yes' === $variation_arr['_downloadable'] ) ? 'checked' : ''; ?>>
					<?php esc_html_e( 'Downloadable', 'wk-marketplace' ); ?>
				</label>
				<label title="check/uncheck to assign its weight, dimensions">
					<input class="checkbox checkbox_is_virtual" id="wkmp_virtual<?php echo esc_attr( $variation_id ); ?>" value="yes" name="wkmp_variable_is_virtual[<?php echo esc_attr( $variation_id ); ?>]" type="checkbox" <?php echo ( isset( $variation_arr['_virtual'] ) && 'yes' === $variation_arr['_virtual'] ) ? 'checked' : ''; ?>>
					<?php esc_html_e( 'Virtual', 'wk-marketplace' ); ?>
				</label>
				<label title="check/uncheck to manage stock">
					<input class="checkbox checkbox_manage_stock" id="wkmp_stock<?php echo esc_attr( $variation_id ); ?>" value="yes" name="wkmp_variable_manage_stock[<?php echo esc_attr( $variation_id ); ?>]" type="checkbox" <?php echo ( isset( $variation_arr['_manage_stock'] ) && 'yes' === $variation_arr['_manage_stock'] ) ? 'checked' : ''; ?>>
					<?php esc_html_e( 'Manage stock?', 'wk-marketplace' ); ?>
				</label>
			</td>
		</tr>
		</tbody>
	</table>
</div>
<?php
++$variation_id;
