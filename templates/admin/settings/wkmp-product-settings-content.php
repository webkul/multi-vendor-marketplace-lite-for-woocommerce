<?php
/**
 * Product Settings Template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

settings_errors();
$categories       = array();
$wc_product_types = wc_get_product_types();

$allowed_types = apply_filters( 'wkmp_allowed_product_types', array( 'simple', 'variable', 'grouped', 'external' ) );
$final_types   = array_intersect_key( $wc_product_types, array_flip( $allowed_types ) );
?>
<form method="post" action="options.php">
	<?php
	settings_fields( 'wkmp-product-settings-group' );

	$product_categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	if ( ! empty( $product_categories ) ) :
		foreach ( $product_categories as $key => $value ) :
			$categories[ $value->slug ] = $value->name;
		endforeach;
	endif;

	$fields = array();

	$fields['_wkmp_product_options'] = array(
		'title'       => __( 'Seller Product Settings', 'wk-marketplace' ),
		'type'        => 'section_start',
		'description' => '',
	);

	$fields['_wkmp_allow_seller_to_publish'] = array(
		'type'        => 'checkbox',
		'label'       => esc_html__( 'Allow Seller to Publish', 'wk-marketplace' ),
		'description' => esc_html__( 'If Checked, Seller can publish his/her items online directly.', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_allow_seller_to_publish', true ),
		'show_lock'   => true,
	);

	if ( defined( 'WCML_VERSION' ) && version_compare( WCML_VERSION, '4.12.0', '>' ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
		$fields['_wkmp_wcml_allow_product_translate'] = array(
			'type'        => 'checkbox',
			'label'       => esc_html__( 'Allow seller to translate products', 'wk-marketplace' ),
			'description' => esc_html__( 'If Checked, Seller can translate their products from their backend dashboard.', 'wk-marketplace' ),
			'value'       => get_option( '_wkmp_wcml_allow_product_translate', false ),
		);
	}

	$fields['_wkmp_seller_allowed_product_types'] = array(
		'type'        => 'multi-select',
		'label'       => esc_html__( 'Allowed Product Types', 'wk-marketplace' ),
		'options'     => $final_types,
		'description' => wp_sprintf( /* translators: %1$d : Allowed product types count, %2$s: Allowed product types. */ esc_html__( 'If none selected, all %1$d products types (%2$s) will be allowed to seller.', 'wk-marketplace' ), count( $final_types ), implode( ',', $final_types ) ),
		'value'       => get_option( '_wkmp_seller_allowed_product_types', array() ),
		'placeholder' => esc_html__( 'Select product type', 'wk-marketplace' ) . '...',
	);

	$fields['_wkmp_seller_allowed_categories'] = array(
		'type'        => 'multi-select',
		'label'       => esc_html__( 'Allowed Categories', 'wk-marketplace' ),
		'options'     => $categories,
		'description' => esc_html__( 'If none selected, all available woocommerce categories will be allowed to seller.', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_seller_allowed_categories' ),
		'placeholder' => esc_html__( 'Select Categories', 'wk-marketplace' ) . '...',
	);

	$fields['_wkmp_minimum_order'] = array(
		'title'       => __( 'Minimum Order Amount Settings', 'wk-marketplace' ),
		'type'        => 'section_start',
		'description' => '',
	);

	$fields['_wkmp_enable_minimum_order_amount'] = array(
		'type'        => 'checkbox',
		'label'       => esc_html__( 'Enable', 'wk-marketplace' ),
		'description' => esc_html__( 'If Checked, Customer can not purchase product below set amount.', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_enable_minimum_order_amount' ),
	);

	$fields['_wkmp_minimum_order_amount'] = array(
		'type'        => 'number',
		'min'         => '0',
		'max'         => '',
		'step'        => '0.01',
		'label'       => esc_html__( 'Minimum Amount', 'wk-marketplace' ),
		'description' => esc_html__( 'Minimum product(s) total (including taxes).', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_minimum_order_amount' ),
	);

	$fields['_wkmp_seller_min_amount_admin_default'] = array(
		'type'        => 'checkbox',
		'label'       => esc_html__( 'Amount Value for Seller', 'wk-marketplace' ),
		'description' => esc_html__( 'If checked, amount value will be used for those sellers who have not filled the minimum order amount.', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_seller_min_amount_admin_default' ),
	);

	$fields['_wkmp_product_qty_limit'] = array(
		'title'       => __( 'Product Quantity Limit Settings', 'wk-marketplace' ),
		'type'        => 'section_start',
		'description' => '',
	);

	$fields['_wkmp_enable_product_qty_limit'] = array(
		'type'        => 'checkbox',
		'label'       => esc_html__( 'Enable', 'wk-marketplace' ),
		'description' => esc_html__( 'If Checked, Seller can add limit on product purchase for customer.', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_enable_product_qty_limit' ),
	);

	$fields['_wkmp_max_product_qty_limit'] = array(
		'type'        => 'number',
		'min'         => 0,
		'max'         => '',
		'label'       => esc_html__( 'Default Maximum Quantity', 'wk-marketplace' ),
		'description' => esc_html__( 'Default maximum quantity for seller\'s product.', 'wk-marketplace' ),
		'value'       => get_option( '_wkmp_max_product_qty_limit' ),
	);

	$form_fields = apply_filters( 'wkmp_pro_admin_product_settings', array( 'entry' => array( 'fields' => $fields ) ) );

	$this->form_helper->wkmp_form_field_builder( $form_fields );

	submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' );
	?>
</form>
<hr/>
