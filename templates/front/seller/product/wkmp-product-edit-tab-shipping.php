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
<div class="wkmp_container" id="shippingtabwk">
	<div class="options_group wkmp_profile_input">
		<?php
		if ( wc_product_weight_enabled() ) {
			$this->wkmp_wp_text_input(
				array(
					'id'          => '_weight',
					'label'       => __( 'Weight', 'wk-marketplace' ) . ' (' . get_option( 'woocommerce_weight_unit' ) . ')',
					'placeholder' => wc_format_localized_decimal( 0 ),
					'desc_tip'    => 'true',
					'description' => __( 'Weight in decimal form', 'wk-marketplace' ),
					'type'        => 'text',
					'data_type'   => 'decimal',
					'value'       => esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_weight', true ) ) ),
				),
				$wk_pro_id
			);
		}

		if ( wc_product_dimensions_enabled() ) {
			$dimension_unit = get_option( 'woocommerce_dimension_unit', 'cm' );
			?>
			<label for="product_length"><?php echo wp_sprintf( /* translators: %s: Dimensions Unit */ esc_html__( 'Dimensions (%s)', 'wk-marketplace' ), esc_html( $dimension_unit ) ); ?></label>
			<span class="wrap">
				<input id="product_length" placeholder="<?php esc_attr_e( 'Length', 'wk-marketplace' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_length" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_length', true ) ) ); ?>"/>
				<input placeholder="<?php esc_attr_e( 'Width', 'wk-marketplace' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_width" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_width', true ) ) ); ?>"/>
				<input placeholder="<?php esc_attr_e( 'Height', 'wk-marketplace' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="_height" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $wk_pro_id, '_height', true ) ) ); ?>"/>
			</span>
			<?php
		}
		?>
	</div>
	<div class="options_group wkmp_profile_input">
		<?php
		// Shipping Class.

		$user_shipping_classes = get_user_meta( $product_auth, 'shipping-classes', true );
		$user_shipping_classes = empty( $user_shipping_classes ) ? array() : maybe_unserialize( $user_shipping_classes );
		?>
		<label for="product_shipping_class"><?php esc_html_e( 'Shipping class', 'wk-marketplace' ); ?></label>
		<?php
		$option_none = esc_html__( 'No shipping class', 'wk-marketplace' );
		if ( empty( $user_shipping_classes ) ) {
			?>
			<select name="product_shipping_class" id="product_shipping_class" class="select short">
				<option value="-1"><?php echo esc_html( $option_none ); ?></option>
			</select>
			<?php
		} else {
			$classes = get_the_terms( $wk_pro_id, 'product_shipping_class' );

			$current_shipping_class = '';
			if ( $classes && ! is_wp_error( $classes ) ) {
				$current_shipping_class = current( $classes )->term_id;
			}

			$args = array(
				'taxonomy'         => 'product_shipping_class',
				'hide_empty'       => 0,
				'show_option_none' => $option_none,
				'name'             => 'product_shipping_class',
				'id'               => 'product_shipping_class',
				'selected'         => $current_shipping_class,
				'class'            => 'select short',
				'include'          => $user_shipping_classes,
			);
			wp_dropdown_categories( $args );
		}
		do_action( 'marketplace_product_options_shipping', $wk_pro_id );
		?>
	</div>
</div>
