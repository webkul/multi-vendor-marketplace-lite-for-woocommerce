<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

// Check if product author is same as of logged in user.
if ( $post_row_data && intval( $product_auth ) === get_current_user_id() ) {
	$product = wc_get_product( $wk_pro_id );

	if ( ! is_a( $product, 'WC_Product' ) ) {
		wc_print_notice( esc_html__( 'Invalid product id', 'wk-marketplace' ), 'error' );
		return false;
	}

	$post_meta_row_data = get_post_meta( $wk_pro_id );

	$meta_arr = array(
		'_virtual'      => '',
		'_manage_stock' => '',
	);

	foreach ( $post_meta_row_data as $key => $value ) {
		$meta_arr[ $key ] = $value[0];
	}

	$product_attributes = get_post_meta( $wk_pro_id, '_product_attributes', true );
	$product_attributes = empty( $product_attributes ) ? array() : $product_attributes;
	$display_variation  = 'no';

	if ( ! empty( $product_attributes ) ) {
		foreach ( $product_attributes as $variation ) {
			if ( 1 === $variation['is_variation'] ) {
				$display_variation = 'yes';
			}
		}
	}

	$wc_product_types = wc_get_product_types();
	$allowed_types    = apply_filters( 'wkmp_allowed_product_types', array( 'simple', 'variable', 'grouped', 'external' ) );
	$final_types      = array_intersect_key( $wc_product_types, array_flip( $allowed_types ) );
	$seller_types     = get_option( '_wkmp_seller_allowed_product_types', array() );
	$mp_product_types = empty( $seller_types ) ? $final_types : array_intersect_key( $final_types, array_flip( $seller_types ) );

	$thumbnail_img   = wp_get_attachment_image_src( get_post_meta( $wk_pro_id, '_thumbnail_id', true ) );
	$thumbnail_image = ( is_array( $thumbnail_img ) && count( $thumbnail_img ) > 0 ) ? $thumbnail_img[0] : '';
	?>

	<div class="wkmp-add-product-form">
		<div class="wkmp-product-navigation">
			<a href="<?php echo esc_url( wc_get_endpoint_url( get_option( '_wkmp_product_list_endpoint', 'seller-products' ) ) ); ?>" class="wkmp-back-to-products-list" title="<?php esc_attr_e( 'Back to Product List', 'wk-marketplace' ); ?>"><span class="dashicons dashicons-arrow-left-alt"></span></a>
			<a href="<?php echo esc_url( get_permalink( $wk_pro_id ) ); ?>" class="wkmp-pro-product-view-link" target="_blank"  title="<?php esc_attr_e( 'View Product as Customer', 'wk-marketplace' ); ?>"><?php esc_html_e( 'View', 'wk-marketplace' ); ?></a>
		</div>
		<input type="hidden" name="var_variation_display" id="var_variation_display" value="<?php echo esc_attr( $display_variation ); ?>"/>

		<ul id='edit_product_tab'>
			<li><a id='edit_tab'><?php esc_html_e( 'Edit', 'wk-marketplace' ); ?></a></li>
			<?php
			$show          = '';
			$hide_ship_tab = false;
			if ( in_array( $product->get_type(), array( 'grouped', 'external' ), true ) ) {
				$show = "style='display:none;'";
			}

			if ( 'yes' === $meta_arr['_virtual'] || 'external' === $product->get_type() ) {
				$hide_ship_tab = true;
			}

			/**
			 * Set display of inventory Tab.
			 *
			 * @since 5.0.0
			 */
			$show = apply_filters( 'wkmp_hide_inventory_tab', $show, $product );
			/**
			 * Filter to add Dynamic Tabs in pattern
			 * $tabs[] = array(
			 *    'tab_id'   => Unique Tab Id,
			 *  'tab_name' => Corresponding Tab name to be displayed.
			 * )
			 *
			 * @since  5.0.1
			 *
			 * Corresponding content hook will be generated in Pattern
			 * "wkmp_tab_content_{$tab_id}"
			 */
			$pro_tabs    = apply_filters( 'wkmp_add_tab_after_edit_tab', array(), $product );
			$tab_content = array();

			if ( ! empty( $pro_tabs ) ) {
				foreach ( $pro_tabs as $tab_key => $tab_value ) {
					if ( isset( $tab_value['tab_id'] ) && $tab_value['tab_name'] ) {
						$tab_content[] = $tab_value['tab_id'];
						?>
						<li><a id="<?php echo esc_attr( $tab_value['tab_id'] ); ?>tab"><?php echo esc_html( $tab_value['tab_name'] ); ?></a></li>
						<?php
					}
				}
			}
			?>
			<li <?php echo esc_attr( $show ); ?>><a id='inventorytab'><?php esc_html_e( 'Inventory', 'wk-marketplace' ); ?></a></li>
			<li class="<?php echo ( $hide_ship_tab ) ? 'wkmp_hide' : ''; ?>"><a id='shippingtab'><?php esc_html_e( 'Shipping', 'wk-marketplace' ); ?></a></li>
			<li><a id='linkedtab'><?php esc_html_e( 'Linked Products', 'wk-marketplace' ); ?></a></li>
			<li><a id='attributestab'><?php esc_html_e( 'Attributes', 'wk-marketplace' ); ?></a></li>
			<li class="wkmp_hide"><a id='external_affiliate_tab'><?php esc_html_e( 'External/Affiliate', 'wk-marketplace' ); ?></a></li>
			<li class="wkmp_hide"><a id='avariationtab'><?php esc_html_e( 'Variations', 'wk-marketplace' ); ?></a></li>
			<li><a id='pro_statustab'><?php esc_html_e( 'Product Status', 'wk-marketplace' ); ?></a></li>
			<?php do_action( 'mp_edit_product_tab_links' ); ?>
		</ul>

		<form action="" method="post" enctype="multipart/form-data" id="product-form">
			<?php
			require_once __DIR__ . '/wkmp-product-edit-tab.php';

			// Custom_tab start here.
			if ( ! empty( $tab_content ) ) {
				foreach ( $tab_content as $tab_content_key => $tab_content_value ) {
					?>
					<div class="wkmp_container" id="<?php echo esc_attr( $tab_content_value ); ?>tabwk">
						<?php do_action( "wkmp_tab_content_{$tab_content_value}", $wk_pro_id ); ?>
					</div>
					<?php
				}
			}

			// Custom_tab end here.
			require_once __DIR__ . '/wkmp-product-edit-tab-inventory.php';
			require_once __DIR__ . '/wkmp-product-edit-tab-shipping.php';
			require_once __DIR__ . '/wkmp-product-edit-tab-linked.php';
			require_once __DIR__ . '/wkmp-product-edit-tab-attributes.php';
			require_once __DIR__ . '/wkmp-product-edit-tab-affiliates.php';
			require_once __DIR__ . '/wkmp-product-edit-tab-variations.php';
			require_once __DIR__ . '/wkmp-product-edit-tab-status.php';

			do_action( 'mp_edit_product_tabs_content', $wk_pro_id );
			?>
			<br>
			<input type="submit" name="add_product_sub" id="add_product_sub" value="<?php esc_attr_e( 'Update', 'wk-marketplace' ); ?>" class="button"/></td>
		</form>
	</div><!-- wkmp-add-product-form end here -->
<?php } elseif ( empty( $product_auth ) ) { ?>
	<h2> <?php esc_html_e( 'This product is no longer exist.', 'wk-marketplace' ); ?> </h2>
	<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_product_list_endpoint', 'seller-products' ) ); ?>"><?php esc_html_e( 'Go to product list.', 'wk-marketplace' ); ?></a>

<?php } else { ?>
		<div class="woocommerce-Message woocommerce-Message--info woocommerce-error">
			<a class="woocommerce-Button button" href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_product_list_endpoint', 'seller-products' ) ); ?>">
				<?php esc_html_e( 'Go To Products', 'wk-marketplace' ); ?>
			</a>
			<?php esc_html_e( "Sorry, but you can not edit other sellers' product..!", 'wk-marketplace' ); ?>
		</div>
	<?php
}

