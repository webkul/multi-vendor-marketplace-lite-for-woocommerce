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
	<div class="wkmp-table-action-wrap">
		<div class="wkmp-action-section left">
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

