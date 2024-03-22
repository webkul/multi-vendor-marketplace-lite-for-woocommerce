<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$file_name = ( $file['name'] ) ? $file['name'] : wc_get_filename_from_url( $file['file'] );
?>
<tr>
	<td class="file_name">
		<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File name', 'wk-marketplace' ); ?>" name="_mp_dwnld_file_names[]" value="<?php echo esc_attr( $file_name ); ?>" />
		<input type="hidden" name="_mp_dwnld_file_hashes[]" value="<?php echo esc_attr( $key ); ?>" />
		<a href="javascript:void(0);" id="delprod" class="mp-action delete"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></a>
	</td>
	<td class="file_url">
		<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'http://', 'wk-marketplace' ); ?>" name="_mp_dwnld_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" />
		<a href="#" class="button upload_file_button upload_downloadable_file" data-choose="<?php esc_attr_e( 'Choose file', 'wk-marketplace' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'wk-marketplace' ); ?>"><?php esc_html_e( 'Choose file', 'wk-marketplace' ); ?></a>
	</td>
</tr>
