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
<form method="GET" id="wkmp-product-list-form" style="margin-bottom:unset;">
	<?php do_action( 'wkmp_before_seller_product_list', $this->seller_id, $filter ); ?>
	<div class="wkmp-table-action-wrap">
		<div class="wkmp-action-section left">
			<button type="button" data-form_id="#wkmp-delete-product" class="button wkmp-bulk-delete" title="<?php esc_attr_e( 'Delete', 'wk-marketplace' ); ?>">
				<span class="dashicons dashicons-trash"></span></button>&nbsp;&nbsp;
			<a href="<?php echo esc_url( get_permalink() . get_option( '_wkmp_add_product_endpoint', 'seller-add-product' ) ); ?>" class="button add-product" title="<?php esc_attr_e( 'Add Product', 'wk-marketplace' ); ?>"><span class="dashicons dashicons-plus-alt"></span></a>
			<?php if ( $wkmp_min_order_enabled || $wkmp_product_qty_limit_enabled ) { ?>
				<a id="wkmp_product_misc_settings" class="wkmp-minimum-order settings" href="javascript:void(0);"><?php esc_html_e( 'Miscellaneous Settings', 'wk-marketplace' ); ?></a>
			<?php } ?>
		</div>
		<div class="wkmp-action-section right wkmp-text-right">
			<input type="text" name="wkmp_search" placeholder="<?php esc_attr_e( 'Search Product by Name or Description', 'wk-marketplace' ); ?>" title="<?php esc_attr_e( 'Search Product by Name or Description', 'wk-marketplace' ); ?>" value="<?php echo isset( $filter_name ) ? esc_attr( wp_unslash( $filter_name ) ) : ''; ?>">
			<?php wp_nonce_field( 'wkmp_product_search_nonce_action', 'wkmp_product_search_nonce' ); ?>
			<input type="submit" value="<?php esc_attr_e( 'Search', 'wk-marketplace' ); ?>" data-action="search"/>
		</div>
	</div>
</form>

<form action="" method="post" enctype="multipart/form-data" id="wkmp-delete-product" style="margin-bottom:unset;">
	<div class="wkmp-table-responsive wkmp-seller-products-lists">
		<table class="table table-bordered table-hover">
			<caption class="wkmp-seller-products-lists-caption"><?php echo apply_filters( 'wkmp_seller_product_table_caption', wp_sprintf( /* translators: %1$s: Anchor for per page. %2$s: Closing anchor tag. */ esc_html__( 'Click on the Name to edit a product. Hover on rows to see actions. %1$s Per Page Settings %3$s', 'wk-marketplace' ), '<a id="wkmp_products_per_page_settings" class="wkmp-products-per-page-settings" href="javascript:void(0);">', '</a>' ) ); ?></caption>
			<thead>
			<tr>
				<td style="width:1px;"><input type="checkbox" id="wkmp-checked-all"></td>
				<td><?php esc_html_e( 'Image', 'wk-marketplace' ); ?></td>
				<td><?php esc_html_e( 'Name', 'wk-marketplace' ); ?></td>
				<td><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></td>
				<td><?php esc_html_e( 'Status', 'wk-marketplace' ); ?></td>
				<td><?php esc_html_e( 'Stock', 'wk-marketplace' ); ?></td>
			</tr>
			</thead>
			<tbody>
			<?php
			if ( $products ) {
				foreach ( $products as $key => $product ) {
					?>
					<tr>
						<td><input type="checkbox" name="selected[]" value="<?php echo esc_attr( $product['product_id'] ); ?>"/></td>
						<td><img src="<?php echo esc_url( $product['image'] ); ?>" height="50" width="60" class="wkmp-img-thumbnail" style="display:unset;"/></td>
						<td>
							<a class="wkmp-seller-product-name" href="<?php echo esc_url( $product['edit'] ); ?>"><?php echo esc_html( $product['name'] ); ?></a>
							<div class="wkmp-row-actions wkmp_hide">
								<a class="wkmp-seller-product-edit" href="<?php echo esc_url( $product['edit'] ); ?>"><?php esc_html_e( 'Edit |', 'wk-marketplace' ); ?></a>
								<?php
								if ( 'publish' === strtolower( $product['status'] ) ) {
									?>
									<a class="wkmp-seller-product-view" href="<?php echo esc_url( $product['product_href'] ); ?>"><?php esc_html_e( 'View |', 'wk-marketplace' ); ?></a>
									<?php
								}
								?>
								<a href="javascript:void(0);" data-product_id="<?php echo esc_attr( $product['product_id'] ); ?>" class="wkmp_delete_seller_product"><?php esc_html_e( 'Delete', 'wk-marketplace' ); ?></a>
							</div>
						</td>
						<td><?php echo wp_kses_post( $product['price'] ); ?></td>
						<td data-title="<?php esc_attr_e( 'Status', 'wk-marketplace' ); ?>"><?php echo esc_html( $product['status'] ); ?></td>
						<td data-title="<?php esc_attr_e( 'Stock', 'wk-marketplace' ); ?>"><?php echo esc_html( $product['stock'] ); ?>
							<?php
							if ( ! empty( $product['stock_quantity'] ) ) {
								echo esc_attr( '(' . $product['stock_quantity'] . ')' );
							}
							?>
						</td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="7" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	<?php wp_nonce_field( 'wkmp-delete-product-nonce-action', 'wkmp-delete-product-nonce' ); ?>
</form>

<?php
echo wp_kses_post( $pagination['results'] );
echo wp_kses_post( $pagination['pagination'] );

$amount_placeholder    = empty( $wkmp_min_order_amount ) ? esc_html__( 'No Restrictions.', 'wk-marketplace' ) : esc_html__( 'Enter minimum amount.', 'wk-marketplace' );
$qty_placeholder       = empty( $wkmp_max_product_qty ) ? esc_html__( 'No Restrictions.', 'wk-marketplace' ) : esc_html__( 'Enter maximum purchasable product quantity.', 'wk-marketplace' );
$clear_amount_btn_text = empty( $wkmp_min_order_amount ) ? esc_html__( 'Enable', 'wk-marketplace' ) : esc_html__( 'Clear', 'wk-marketplace' );
$clear_qty_btn_text    = empty( $wkmp_max_product_qty ) ? esc_html__( 'Enable', 'wk-marketplace' ) : esc_html__( 'Clear', 'wk-marketplace' );
$thousand_seperator    = wc_get_price_thousand_separator();
$wkmp_min_order_amount = (float) str_replace( $thousand_seperator, '', $wkmp_min_order_amount );
?>
<div id="wkmp_minimum_order_model" class="wkmp-min-order-popup wkmp-popup-modal">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title"><?php esc_html_e( 'Product Miscellaneous Settings', 'wk-marketplace' ); ?></h4>
		</div>
		<div class="modal-body wkmp-form-wrap">
			<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-min-order-amount-form">
				<?php
				if ( $wkmp_min_order_enabled ) {
					?>
					<div class="form-group wkmp-popup-model">
						<div class="wkmp-width-45">
							<label for="wkmp-message"><b><?php esc_html_e( 'Minimum Order Amount Checkout', 'wk-marketplace' ); ?></b></label>&nbsp;
						</div>
						<div class="wkmp-width-45">
							<input placeholder="<?php echo esc_attr( $amount_placeholder ); ?>" data-empty_allow="<?php echo empty( $wkmp_min_order_amount ) ? 1 : 0; ?>" value="<?php echo esc_attr( $wkmp_min_order_amount ); ?>" type="number" step="0.01" min="1" name="_wkmp_minimum_order_amount" <?php echo empty( $wkmp_min_order_amount ) ? 'readonly' : ''; ?> >
						</div>
						<div class="wkmp-width-10">
							<a id="wkmp_clear_min_order_amount" href="javascript:void(0);"><?php echo esc_html( $clear_amount_btn_text ); ?></a>
						</div>
					</div>
					<div id="wkmp-amount-error" class="wkmp-text-danger"></div>
					<?php
				}

				if ( $wkmp_product_qty_limit_enabled ) {
					?>
					<div class="form-group wkmp-popup-model">
						<div class="wkmp-width-45">
							<label for="wkmp-message"><b><?php esc_html_e( 'Maximum Purchasable Product Quantity', 'wk-marketplace' ); ?></b></label>&nbsp;
						</div>
						<div class="wkmp-width-45">
							<input placeholder="<?php echo esc_attr( $qty_placeholder ); ?>" data-empty_allow="<?php echo empty( $wkmp_max_product_qty ) ? 1 : 0; ?>" value="<?php echo esc_attr( $wkmp_max_product_qty ); ?>" type="number" step="1" min="0" name="_wkmp_max_product_qty_limit" <?php echo empty( $wkmp_max_product_qty ) ? 'readonly' : ''; ?> >
						</div>
						<div class="wkmp-width-10">
							<a id="wkmp_clear_max_qty_limit" href="javascript:void(0);"><?php echo esc_html( $clear_qty_btn_text ); ?></a>
						</div>
					</div>
					<div id="wkmp-max-qty-limit-error" class="wkmp-text-danger"></div>
					<?php
				}
				wp_nonce_field( 'wkmp-min-order-nonce-action', 'wkmp-min-order-nonce' );
				?>
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
			<button id="wkmp-submit-min-order-amount-update" type="submit" form="wkmp-seller-min-order-amount-form" class="button"><?php esc_html_e( 'Save', 'wk-marketplace' ); ?></button>
		</div>
	</div>
</div>

<!-- Per page settings model. -->
<div id="wkmp_products_per_page_settings_model" class="wkmp-per-page-settings-model wkmp-popup-modal">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title"><?php esc_html_e( 'Per Page Products Settings', 'wk-marketplace' ); ?></h4>
		</div>
		<div class="modal-body wkmp-form-wrap">
			<form action="" method="post" enctype="multipart/form-data" id="wkmp_seller_min_order_amount_form">
				<div class="form-group wkmp-popup-model">
					<div class="wkmp-width-45">
						<label for="wkmp-message"><b><?php esc_html_e( 'Show Products Per Page', 'wk-marketplace' ); ?></b></label>&nbsp;
					</div>
					<div class="wkmp-width-45">
						<input placeholder="<?php esc_attr_e( 'Enter number of products to be shown per page', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $limit ); ?>" type="number" step="1" min="2" name="_wkmp_products_per_page">
					</div>
				</div>
				<div id="wkmp_product_per_page_error" class="wkmp-text-danger"></div>
				<?php wp_nonce_field( 'wkmp-per_page_product-nonce-action', 'wkmp-product-per-page-nonce' ); ?>
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
			<button id="wkmp-submit-product-per-page-update" type="submit" form="wkmp-product-per-page-form" class="button"><?php esc_html_e( 'Save', 'wk-marketplace' ); ?></button>
		</div>
	</div>
</div>

<div class="wkmp-ajax-loader wkmp_hide"><div class="wkmp-loading-wheel"></div></div>

