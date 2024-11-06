<?php
/**
 * Seller Orders list at front end.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>

<form method="post" id="wkmp-order-list-form">
	<?php do_action( 'wkmp_before_seller_orders_list', $seller_id, $filter ); ?>
	<div class="wkmp-table-action-wrap">
		<div class="wkmp-action-section left">
			<a id="wkmp_order_per_page_settings" class="wkmp-orders-per-page-settings" href="javascript:void(0);"><?php echo wp_sprintf( /* translators: %s: Per Page. */ esc_html__( 'Items Per Page (%s)', 'wk-marketplace' ), esc_html( $limit ) ); ?></a>
		</div>
		<div class="wkmp-action-section right">
			<input type="text" name="wkmp_search" placeholder="<?php esc_attr_e( 'Search by Order ID', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $search_order_id ); ?>">
			<?php wp_nonce_field( 'wkmp_order_search_nonce_action', 'wkmp_order_search_nonce' ); ?>
			<input type="submit" value="<?php esc_attr_e( 'Search', 'wk-marketplace' ); ?>" data-action="search"/>
		</div>
	</div>
</form>
<div class="wkmp-table-responsive">
<?php if ( apply_filters( 'wkmpship_order_table_change', true ) ) { ?>
	<table class="table table-bordered table-hover">
		<thead>
		<tr>
			<td><?php esc_html_e( 'Order ID', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Status', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Date', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></td>
		</tr>
		</thead>
		<tbody>
		<?php if ( $orders ) { ?>
			<?php foreach ( $orders as $key => $seller_order ) { ?>
				<tr>
					<td><?php echo '#' . esc_html( $seller_order['order_id'] ); ?></td>
					<td><?php echo esc_html( ucfirst( $seller_order['order_status'] ) ); ?></td>
					<td><?php echo esc_html( $seller_order['order_date'] ); ?></td>
					<td><?php echo wp_kses_post( $seller_order['order_total'] ); ?></td>
					<td>
						<a href="<?php echo esc_url( $seller_order['view'] ); ?>" class="button" style="padding:12px;"><span class="dashicons dashicons-visibility"></span></a>
						<?php do_action( 'wkmp_seller_order_table_actions', $seller_order ); ?>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="5" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
			</tr>
		<?php } ?>

		</tbody>
	</table>
	<?php
} else {
	apply_filters( 'wkmp_order_after_table_change', $orders );
}
?>
</div><!-- wkmp-overflowx-auot end here-->
<?php
echo wp_kses_post( $pagination['results'] );
echo wp_kses_post( $pagination['pagination'] );

?>

<!-- Per page settings model. -->
<div id="wkmp_orders_per_page_settings_model" class="wkmp-per-page-settings-model wkmp-popup-modal">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title"><?php esc_html_e( 'Per Page Orders Settings', 'wk-marketplace' ); ?></h4>
		</div>
		<div class="modal-body wkmp-form-wrap">
			<form action="" method="post" enctype="multipart/form-data" id="wkmp_seller_min_order_amount_form">
				<div class="form-group wkmp-popup-model">
					<div class="wkmp-width-45">
						<label for="wkmp-message"><b><?php esc_html_e( 'Show Orders Per Page', 'wk-marketplace' ); ?></b></label>&nbsp;
					</div>
					<div class="wkmp-width-45">
						<input placeholder="<?php esc_attr_e( 'Enter number of orders to be shown per page', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $limit ); ?>" type="number" step="1" min="2" name="_wkmp_orders_per_page">
					</div>
				</div>
				<div id="wkmp_orders_per_page_error" class="wkmp-text-danger"></div>
				<?php wp_nonce_field( 'wkmp-per_page_order-nonce-action', 'wkmp-order-per-page-nonce' ); ?>
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
			<button id="wkmp-submit-order-per-page-update" type="submit" form="wkmp-order-per-page-form" class="button"><?php esc_html_e( 'Save', 'wk-marketplace' ); ?></button>
		</div>
	</div>
</div>
