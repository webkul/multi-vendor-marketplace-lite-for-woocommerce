<?php
/**
 * Seller product edit tab.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

?>
<div class="wkmp_container form" id="edit_tabwk">
	<div class="wkmp_profile_input">
		<label for="product_type"><?php esc_html_e( 'Product Type: ', 'wk-marketplace' ); ?></label>
		<select name="product_type" id="product_type" class="mp-toggle-select">
			<?php
			foreach ( $mp_product_types as $key => $pro_type ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $product->get_type() ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $pro_type ); ?></option>
				<?php
			}
			?>
		</select>
	</div>

	<div class="wkmp_profile_input">
		<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
		<input class="wkmp_product_input" type="text" name="product_name" id="product_name" size="54" value="<?php echo isset( $post_row_data[0]->post_title ) ? esc_attr( $post_row_data[0]->post_title ) : ''; ?>"/>
		<div id="pro_name_error" class="wkmp-error-class"></div>
	</div>

	<div class="wkmp_profile_input" style="display:none">
		<?php
		if ( ! empty( $wk_pro_id ) ) {
			?>
			<input type="hidden" value="<?php echo esc_attr( $wk_pro_id ); ?>" name="sell_pr_id" id="sell_pr_id"/>
			<input type="hidden" value="<?php echo esc_attr( $product->get_type() ); ?>" name="sell_pr_type" id="sell_pr_type"/>
			<input type="hidden" name="active_product_tab" id="active_product_tab" value="<?php echo isset( $posted_data['active_product_tab'] ) ? esc_attr( $posted_data['active_product_tab'] ) : ''; ?>"/>
		<?php } ?>
	</div>

	<div class="wkmp_profile_input">
		<label for="product_desc"><?php esc_html_e( 'About Product', 'wk-marketplace' ); ?></label>
		<?php
		$settings = array(
			'media_buttons' => true, // show insert/upload button(s).
			'textarea_name' => 'product_desc',
			'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
			'tabindex'      => '',
			'teeny'         => false,
			'dfw'           => false,
			'tinymce'       => true,
			'quicktags'     => false,
		);

		$content = '';

		if ( isset( $post_row_data[0]->post_content ) ) {
			$content = html_entity_decode( $post_row_data[0]->post_content );
		}

		wp_editor( $content, 'product_desc', $settings );
		?>
		<div id="long_desc_error" class="wkmp-error-class"></div>
	</div>

	<div class="wkmp_profile_input">
		<label for="product_category"><?php esc_html_e( 'Product Category', 'wk-marketplace' ); ?></label>
		<?php
		echo wp_kses(
			str_replace( '<select', '<select data-placeholder="' . esc_attr__( 'Choose category(s)', 'wk-marketplace' ) . '" multiple="multiple" ', $product_categories ),
			array(
				'select' => array(
					'data-placeholder' => array(),
					'multiple'         => array(),
					'id'               => array(),
					'name'             => array(),
					'class'            => array(),
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
	</div>

	<div class="wkmp_profile_input">
		<label for="fileUpload"><?php esc_html_e( 'Product Thumbnail', 'wk-marketplace' ); ?></label>
		<?php if ( isset( $meta_arr['image'] ) ) { ?>
			<img src="<?php echo esc_url( $meta_arr['image'] ); ?>" width="50" height="50">
		<?php } ?>
		<div id="product_image"></div>
		<input type="hidden" id="product_thumb_image_mp" name="product_thumb_image_mp" value="<?php echo isset( $meta_arr['_thumbnail_id'] ) ? esc_attr( $meta_arr['_thumbnail_id'] ) : ''; ?>"/>
		<?php
		if ( ! empty( $thumbnail_image ) ) {
			echo '<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;"><img style="display:inline;vertical-align:middle;" src="' . esc_url( $thumbnail_image ) . '" width=50 height=50 data-placeholder-url="' . esc_url( wc_placeholder_img_src() ) . '" /><span title="' . esc_attr__( 'Remove', 'wk-marketplace' ) . '" class="mp-image-remove-icon">x</span></div>';
		} else {
			echo '<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;"><img style="display:inline;vertical-align:middle;" src="' . esc_url( wc_placeholder_img_src() ) . '" width=50 height=50 data-placeholder-url="' . esc_url( wc_placeholder_img_src() ) . '" /></div>';
		}
		?>
		<p>
			<a class="upload mp_product_thumb_image button" data-type-error="<?php esc_attr_e( 'Only jpg|png|jpeg files are allowed.', 'wk-marketplace' ); ?>" href="javascript:void(0);"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></a>
		</p>
	</div>

	<?php
	do_action( 'wkmp_after_edit_product_thumb', $wk_pro_id );
	$show_sku = apply_filters( 'wkmp_show_sku_to_seller', true );

	if ( $show_sku ) {
		$dynamic_sku_enabled = $posted_data['dynamic_sku_enabled'];
		$dynamic_sku_prefix  = $posted_data['dynamic_sku_prefix'];
		$dynamic_sku         = $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix );
		?>

	<div class="wkmp_profile_input">
		<label for="product_sku"><?php esc_html_e( 'Product SKU', 'wk-marketplace' ); ?>
			<span class="wkmp-front-wc-help-tooltip help">
				<div class="wkmp-help-tip-sol">
				<?php esc_html_e( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wk-marketplace' ); ?>
				</div>
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
		$p_sku = isset( $meta_arr['_sku'] ) ? $meta_arr['_sku'] : '';
		if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
			?>
			<span class="wkmp-sku-prefix-span"><?php echo esc_html( $dynamic_sku_prefix ); ?></span>
			<?php
		}
		?>
		<input class="wkmp_product_input" type="text" name="product_sku" id="product_sku" value="<?php echo esc_attr( $p_sku ); ?>"/>
		</div>
		<div id="pro_sku_error" class="wkmp-error-class"></div>
	</div>

		<?php
	}
	$hide_price = in_array( $product->get_type(), array( 'grouped', 'variable' ), true );
	$style      = $hide_price ? 'style=display:none' : '';
	?>
	<div class="wkmp_profile_input" <?php echo esc_attr( $style ); ?> id="regularPrice">
		<label for="regu_price"><?php esc_html_e( 'Regular Price', 'wk-marketplace' ); ?></label>
		<input class="wkmp_product_input" type="text" name="regu_price" id="regu_price" value="<?php echo isset( $meta_arr['_regular_price'] ) ? esc_attr( wc_format_localized_decimal( $meta_arr['_regular_price'] ) ) : ''; ?>"/>
		<div id="regl_pr_error" class="wkmp-error-class"></div>
	</div>

	<div class="wkmp_profile_input" <?php echo esc_attr( $style ); ?> id="salePrice">
		<label for="sale_price"><?php esc_html_e( 'Sale Price', 'wk-marketplace' ); ?></label>
		<input class="wkmp_product_input" type="text" name="sale_price" id="sale_price" value="<?php echo isset( $meta_arr['_sale_price'] ) ? esc_attr( wc_format_localized_decimal( $meta_arr['_sale_price'] ) ) : ''; ?>"/>
		<div id="sale_pr_error" class="wkmp-error-class"></div>
	</div>

	<div class="wkmp_profile_input">
		<label for="short_desc"><?php esc_html_e( 'Product Short Description ', 'wk-marketplace' ); ?></label>
		<?php
		$settings = array(
			'media_buttons'    => false, // show insert/upload button(s).
			'textarea_name'    => 'short_desc',
			'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
			'tabindex'         => '',
			'editor_class'     => 'backend',
			'teeny'            => false,
			'dfw'              => false,
			'tinymce'          => true,
			'quicktags'        => false,
			'drag_drop_upload' => true,
		);

		$short_content = '';

		if ( isset( $post_row_data[0]->post_excerpt ) ) {
			$short_content = html_entity_decode( $post_row_data[0]->post_excerpt );
		}

		wp_editor( $short_content, 'short_desc', $settings );
		?>
		<div id="short_desc_error" class="wkmp-error-class"></div>
	</div>
</div>
