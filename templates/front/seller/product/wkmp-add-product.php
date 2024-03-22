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
<form action="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_product_list_endpoint', 'seller-products' ) ); ?>" method="post" enctype="multipart/form-data" id="product-form">
	<fieldset>
		<div class="wkmp_profile_input">
			<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-marketplace' ); ?><span class="required">*</span></label>
			<input class="wkmp_product_input" type="text" name="product_name" id="product_name" size="54" value=""/>
			<div id="pro_name_error" class="wkmp-error-class"></div>
		</div>

		<div class="wkmp_profile_input">
			<label for="product_desc"><?php esc_html_e( 'About Product', 'wk-marketplace' ); ?></label>
			<?php
			$settings = array(
				'media_buttons' => true,
				'textarea_name' => 'product_desc',
				'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
				'tabindex'      => '',
				'teeny'         => false,
				'dfw'           => false,
				'tinymce'       => true,
				'quicktags'     => false,
			);

			$content = '';

			wp_editor( $content, 'product_desc', $settings );

			$reg_val = '';
			$sel_val = '';

			if ( 'variable' === $product_type || 'grouped' === $product_type ) {
				$reg_val = 'disabled';
				$sel_val = 'disabled';
			}
			?>
			<div id="long_desc_error" class="wkmp-error-class"></div>
		</div>

		<div class="wkmp_profile_input">
			<?php
			$product_cat = ( count( $product_cats ) > 0 ) ? $product_cats[0] : '-';
			$product_cat = ( count( $product_cats ) < 2 ) ? $product_cat : implode( ',', $product_cats );
			?>
			<input type="hidden" name="product_cate" value="<?php echo esc_attr( $product_cat ); ?>">
			<input type="hidden" name="product_type" value="<?php echo esc_attr( $product_type ); ?>">
		</div>

		<div class="wkmp_profile_input">
			<label for="fileUpload"><?php esc_html_e( 'Product Thumbnail', 'wk-marketplace' ); ?></label>
			<div id="product_image"></div>
			<input type="hidden" id="product_thumb_image_mp" name="product_thumb_image_mp"/>
			<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;">
				<img style="display:inline;vertical-align:middle;" src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width=50 height=50 data-placeholder-url="<?php echo esc_url( wc_placeholder_img_src() ); ?>"/>
			</div>
			<p>
				<a class="upload mp_product_thumb_image button" data-type-error="<?php esc_attr_e( 'Only jpg|png|jpeg files are allowed.', 'wk-marketplace' ); ?>" href="javascript:void(0);"><?php esc_html_e( 'Upload Thumb', 'wk-marketplace' ); ?></a>
			</p>
		</div>

		<?php
		do_action( 'wkmp_after_edit_product_thumb', 0 );
		$show_sku = apply_filters( 'wkmp_show_sku_to_seller', true );
		if ( $show_sku ) {
			$dynamic_sku = $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix );
			?>
		<div class=" wkmp_profile_input">
			<label for="product_sku"><?php esc_html_e( 'Product SKU', 'wk-marketplace' ); ?>
			<span class="wkmp-front-wc-help-tooltip help">
				<div class="wkmp-help-tip-sol"><?php esc_html_e( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wk-marketplace' ); ?></div>
				<span class="help-tip"></span>
			</span>
				<?php
				if ( $dynamic_sku ) {
					?>
					<span class="wkmp-seller-prefix">(<?php printf( /* Translators: %s: SKU prefix. */ esc_html__( 'Prefix: %s will be added automatically as it is enabled by admin.', 'wk-marketplace' ), esc_html( $dynamic_sku_prefix ) ); ?>)</span>
					<?php
				}
				?>
			</label>
			<div style="<?php echo ( $dynamic_sku ) ? 'display:table' : ''; ?>" class="wkmp-sku-input-wrap wkmp-prefix">
			<?php
			if ( $dynamic_sku ) {
				?>
				<span class="wkmp-sku-prefix-span"><?php echo esc_html( $dynamic_sku_prefix ); ?></span>
				<?php
			}
			?>
			<input class="wkmp_product_input" type="text" name="product_sku" id="product_sku" value=""/>
			</div>
			<div id="pro_sku_error" class="wkmp-error-class"></div>
		</div>
			<?php
		}

		$prod_type  = empty( $product_type ) ? 'simple' : $product_type;
		$show_price = ! in_array( $prod_type, array( 'grouped', 'variable' ), true );

		if ( apply_filters( 'wkmp_show_seller_price', $show_price, $prod_type ) ) {
			?>
			<div class="wkmp_profile_input">
				<label for="regu_price"><?php esc_html_e( 'Regular Price', 'wk-marketplace' ); ?></label>
				<input class="wkmp_product_input" type="text" name="regu_price" id="regu_price" value=""/>
				<div id="regl_pr_error" class="wkmp-error-class"></div>
			</div>

			<div class="wkmp_profile_input">
				<label for="sale_price"><?php esc_html_e( 'Sale Price', 'wk-marketplace' ); ?></label>
				<input class="wkmp_product_input" type="text" name="sale_price" id="sale_price" value=""/>
				<div id="sale_pr_error" class="wkmp-error-class"></div>
			</div>
		<?php } ?>
		<div class="wkmp_profile_input">
			<label for="short_desc"><?php esc_html_e( 'Product Short Description ', 'wk-marketplace' ); ?></label>
			<?php
			$settings = array(
				'media_buttons'    => false, // show insert/upload button(s).
				'textarea_name'    => 'short_desc',
				'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
				'tabindex'         => '',
				'editor_class'     => 'frontend',
				'teeny'            => false,
				'dfw'              => false,
				'tinymce'          => true,
				'quicktags'        => false,
				'drag_drop_upload' => true,
			);

			$short_content = '';

			wp_editor( $short_content, 'short_desc', $settings );
			?>
			<div id="short_desc_error" class="wkmp-error-class"></div>
		</div>

		<div class="wkmp_profile_input">
			<?php wp_nonce_field( 'wkmp_add_product_submit_nonce_action', 'wkmp_add_product_submit_nonce_name' ); ?>
			<input type="submit" name="add_product_sub" id="add_product_sub" value='<?php esc_attr_e( 'Save', 'wk-marketplace' ); ?>' class="button"/></td>
		</div>
			<?php do_action( 'wkmp_after_add_product_form', $this->seller_id ); ?>
	</fieldset>
</form>
