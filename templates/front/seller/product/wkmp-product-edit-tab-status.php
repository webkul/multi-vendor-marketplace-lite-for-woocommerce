<?php
/**
 * Seller product edit Status Product tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

?>
<div class="wkmp_container" id="pro_statustabwk">
	<?php if ( get_option( '_wkmp_allow_seller_to_publish', true ) ) { ?>
		<div class="mp-sidebar-container">
			<div class="mp_wk-post-status wkmp-toggle-sidebar">
				<div class="wkmp-status-wrapper">
					<label for="post_status"><?php esc_html_e( 'Product Status: ', 'wk-marketplace' ); ?></label>
					<?php
					$prod_status = '';
					if ( ! empty( $post_row_data[0]->post_status ) ) {
						$prod_status = $post_row_data[0]->post_status;
						$prod_label  = ( 'publish' === $prod_status ) ? esc_html__( 'Online', 'wk-marketplace' ) : esc_html__( 'Draft', 'wk-marketplace' );
						?>
						<span class="mp-toggle-selected-display <?php echo ( 'publish' === $prod_status ) ? 'green' : ''; ?>"><?php echo esc_html( $prod_label ); ?></span>
					<?php } ?>
				</div>
				<div id="wkmp_product_status_checkbox_wrap" class="wkmp-toggle-select-container">
					<label for="Online"><input class="wkmp-toggle-select" type="radio" name="mp_product_status" value="publish" <?php checked( 'publish', $prod_status, true ); ?>><?php esc_html_e( 'Online', 'wk-marketplace' ); ?></label>
					<label for="Draft"><input class="wkmp-toggle-select" type="radio" name="mp_product_status" value="draft" <?php checked( 'draft', $prod_status, true ); ?>><?php esc_html_e( 'Draft', 'wk-marketplace' ); ?></label>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php $show_vertual = 'simple' === $product->get_type(); ?>

	<?php if ( apply_filters( 'wkmp_show_vertual_downloadable', $show_vertual, $product ) ) { ?>
		<div class="wkmp-side-head">
			<label class="checkbox-inline">
				<input type="checkbox" id="_ckvirtual" class="wk-dwn-check" name="_virtual" value="yes" <?php echo ( isset( $meta_arr['_virtual'] ) && 'yes' === $meta_arr['_virtual'] ) ? 'checked' : ''; ?>/>&nbsp;&nbsp;
				<?php esc_html_e( 'Virtual', 'wk-marketplace' ); ?>
			</label>
		</div>
		<hr class="mp-section-seperate">
		<!-- downloadable starts -->
		<div class="wkmp-side-head">
			<label class="checkbox-inline">
				<input type="checkbox" id="_ckdownloadable" class="wk-dwn-check" name="_downloadable" value="yes" <?php echo ( isset( $meta_arr['_downloadable'] ) && 'yes' === $meta_arr['_downloadable'] ) ? 'checked' : ''; ?>/>&nbsp;&nbsp;
				<?php esc_html_e( 'Downloadable Product', 'wk-marketplace' ); ?>
			</label>
		</div>

		<div class="wk-mp-side-body" style="display:<?php echo ( isset( $meta_arr['_downloadable'] ) && 'yes' === $meta_arr['_downloadable'] ) ? 'block' : 'none'; ?>">
			<?php $mp_downloadable_files = get_post_meta( $wk_pro_id, '_downloadable_files', true ); ?>
			<div class="form-field downloadable_files wkmp-table-responsive">
				<label><?php esc_html_e( 'Downloadable files', 'wk-marketplace' ); ?></label>
				<table style="display: table;" cellpadding="0" cellspacing="0" class="table table-bordered table-hover">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'wk-marketplace' ); ?></th>
						<th colspan="2"><?php esc_html_e( 'File URL', 'wk-marketplace' ); ?></th>
						<th>&nbsp;</th>
					</tr>
					</thead>
					<tbody>
					<?php
					if ( $mp_downloadable_files ) {
						foreach ( $mp_downloadable_files as $key => $file ) {
							include __DIR__ . '/wkmp-product-download.php';
						}
					}
					?>
					</tbody>
					<tfoot>
					<tr>
						<th colspan="5">
							<a href="#" class="button insert" data-row="
							<?php
							$key  = '';
							$file = array(
								'file' => '',
								'name' => '',
							);
							ob_start();
							$file_name = ( $file['name'] ) ? $file['name'] : wc_get_filename_from_url( $file['file'] );
							?>
							<tr>
								<td class="file_name">
									<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File name', 'wk-marketplace' ); ?>" name="_mp_dwnld_file_names[]" value="<?php echo esc_attr( $file_name ); ?>" />
									<input type="hidden" name="_mp_dwnld_file_hashes[]" value="<?php echo esc_attr( $key ); ?>" />
									<a href="#" id="delprod" class="mp-action delete"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></a>
								</td>
								<td class="file_url">
									<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'http://', 'wk-marketplace' ); ?>" name="_mp_dwnld_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" />
									<a href="#" class="button upload_file_button upload_downloadable_file" data-choose="<?php esc_attr_e( 'Choose file', 'wk-marketplace' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'wk-marketplace' ); ?>"><?php esc_html_e( 'Choose file', 'wk-marketplace' ); ?></a>
								</td>
							</tr>
							<?php echo esc_attr( ob_get_clean() ); ?>">
								<?php esc_html_e( 'Add File', 'wk-marketplace' ); ?>
							</a>
						</th>
					</tr>
					</tfoot>
				</table>
			</div>
			<?php
			$download_limit  = isset( $meta_arr['_download_limit'] ) ? $meta_arr['_download_limit'] : '';
			$download_expiry = isset( $meta_arr['_download_expiry '] ) ? $meta_arr['_download_expiry '] : '';

			$download_limit_value  = ( $download_limit > 0 ) ? $download_limit : '';
			$download_expiry_value = ( $download_expiry > 0 ) ? $download_expiry : '';
			?>
			<p class="form-field _download_limit_field wkmp_profile_input">
				<label for="_download_limit"><?php esc_html_e( 'Download limit', 'wk-marketplace' ); ?></label>
				<input type="number" class="short wkmp_product_input" style="padding: 3px 5px;" name="_download_limit" id="_download_limit" value="<?php echo esc_attr( $download_limit_value ); ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wk-marketplace' ); ?>" step="1" min="0"/>
				<span class="description"><?php esc_html_e( 'Leave blank for unlimited re-downloads.', 'wk-marketplace' ); ?></span>
			</p>

			<p class="form-field _download_expiry_field ">
				<label for="_download_expiry"><?php esc_html_e( 'Download expiry', 'wk-marketplace' ); ?></label>
				<input type="number" class="short wkmp_product_input" style="padding: 3px 5px;" name="_download_expiry" id="_download_expiry" value="<?php echo esc_attr( $download_expiry_value ); ?>" placeholder="<?php esc_attr_e( 'Never', 'wk-marketplace' ); ?>" step="1" min="0"/>
				<span class="description"><?php esc_html_e( 'Enter the number of days before a download link expires, or leave blank.', 'wk-marketplace' ); ?></span>
			</p>
		</div>
	<?php } ?>

	<hr class="mp-section-seperate">
	<!-- downloadable ends -->
	<div class="wkmp-side-head"><label><?php esc_html_e( 'Image Gallery', 'wk-marketplace' ); ?></label></div>
	<div id="wk-mp-product-images">
		<div id="product_images_container">
			<?php
			if ( isset( $meta_arr['_product_image_gallery'] ) && ! empty( $meta_arr['_product_image_gallery'] ) ) {
				$gallery_image_ids = explode( ',', get_post_meta( $wk_pro_id, '_product_image_gallery', true ) );
				foreach ( $gallery_image_ids as $image_id ) {
					$image_url = wp_get_attachment_image_src( $image_id );
					?>
					<div class='mp_pro_image_gallary'><img src='<?php echo esc_url( $image_url[0] ); ?>' width=50 height=50/>
						<a href="javascript:void(0);" id="<?php echo esc_attr( $wk_pro_id . 'i_' . $image_id ); ?>" class="mp-img-delete_gal" title="<?php esc_attr_e( 'Delete image', 'wk-marketplace' ); ?>">
							<?php esc_html_e( 'Delete', 'wk-marketplace' ); ?>
						</a>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
		<div id="handleFileSelectgalaray"></div>
		<input type="hidden" class="wkmp_product_input" name="product_image_Galary_ids" id="product_image_Galary_ids" value="<?php echo isset( $meta_arr['_product_image_gallery'] ) ? esc_attr( $meta_arr['_product_image_gallery'] ) : ''; ?>"/>
	</div>
	<a href="javascript:void(0);" class="add-mp-product-images btn">+ <?php esc_html_e( 'Add product images', 'wk-marketplace' ); ?></a></p>
	<?php wp_nonce_field( 'wkmp_edit_product_nonce_action', 'wkmp_edit_product_nonce_field' ); ?>
</div>
