<?php
/**
 * Refund Product.
 *
 * @package WkMarketplace
 */
defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! empty( $details['tax_rates'] ) ) {
	foreach ( $details['tax_rates'] as $tax_id => $tax_cost ) {
		if ( ! in_array( $tax_id, $seller_tax_rate_ids, true ) ) {
			continue;
		}
		$single_amount    = $tax_cost / (float) $details['qty'];
		$remaining_amount = $tax_cost;
		?>
		<br>
		<span><?php echo esc_html( $tax_list_name[ $tax_id ] ); ?>:</span>
		<span><?php echo wp_kses_data( wc_price( $tax_cost ) ); ?></span>
		<?php
		if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_tax'][ $tax_id ] ) ) {
			$remaining_amount -= $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_tax'][ $tax_id ];
			?>
			<p class="wkmp-refund wkmp-green"><?php echo wp_kses_data( wc_price( $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_tax'][ $tax_id ], array( 'currency' => $order_currency ) ) ); ?></p>
			<?php
		}
		if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_tax'][ $tax_id ] ) && (float) $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_tax'][ $tax_id ] === (float) $tax_cost ) {
			continue;
		}
		?>
		<p class="wkmp-order-refund wkmp-item-refund-<?php echo esc_attr( $details['item_key'] ); ?>" style="display:none;">
			<input type="number" name="refund_line_tax[<?php echo esc_attr( $details['item_key'] ); ?>][<?php echo esc_attr( $tax_id ); ?>]" id="refund_line_tax[<?php echo esc_attr( $details['item_key'] ); ?>][<?php echo esc_attr( $tax_id ); ?>]" class="form-control wkmp-tax" data-order-item-id="<?php echo esc_attr( $tax_id ); ?>" value="0" max="<?php echo esc_attr( $remaining_amount ); ?>" data-qty="0" step="<?php echo esc_attr( $single_amount ); ?>" min="0" readonly>
		</p>
		<?php
	}
}
