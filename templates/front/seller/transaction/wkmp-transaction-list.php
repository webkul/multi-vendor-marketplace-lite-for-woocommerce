<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>

<form method="GET" id="wkmp-transaction-list-form">
	<div class="wkmp-table-action-wrap">
		<div class="wkmp-action-section left">
			<input type="text" name="wkmp_search" placeholder="<?php esc_attr_e( 'Search by Transaction ID', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $filter_name ); ?>">
			<?php wp_nonce_field( 'wkmp_transaction_search_nonce_action', 'wkmp_transaction_search_nonce' ); ?>
			<input type="submit" value="<?php esc_attr_e( 'Search', 'wk-marketplace' ); ?>" data-action="search"/>
		</div>
	</div>
</form>
<div class="wkmp-table-responsive">
	<table class="table table-bordered table-hover">
		<thead>
		<tr>
			<td><?php esc_html_e( 'Transaction ID', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Date', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Amount', 'wk-marketplace' ); ?></td>
			<td><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></td>
		</tr>
		</thead>
		<tbody>
		<?php if ( $transactions ) { ?>
			<?php foreach ( $transactions as $key => $transaction ) { ?>
				<tr>
					<td><a href="<?php echo esc_url( $transaction['view'] ); ?>"><?php echo esc_html( $transaction['transaction_id'] ); ?></a></td>
					<td><?php echo esc_html( $transaction['created_on'] ); ?></td>
					<td><?php echo wp_kses_post( $transaction['amount'] ); ?></td>
					<td><a href="<?php echo esc_url( $transaction['view'] ); ?>" class="button" style="padding:12px;"><span class="dashicons dashicons-visibility"></span></a></td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="4" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div><!-- wkmp-overflow x-a uot end here-->
<?php
echo wp_kses_post( $pagination['results'] );
echo wp_kses_post( $pagination['pagination'] );
