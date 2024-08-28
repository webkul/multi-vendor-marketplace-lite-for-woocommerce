<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$seller_id      = empty( $views_data['seller_id'] ) ? get_current_user_id() : $views_data['seller_id'];
$order_id       = empty( $views_data['order_id'] ) ? 0 : $views_data['order_id'];
$gateway_name   = empty( $views_data['gateway_name'] ) ? 0 : $views_data['gateway_name'];
$reward_points  = empty( $views_data['reward_points'] ) ? 0 : $views_data['reward_points'];
$order_data     = empty( $views_data['seller_order_data'] ) ? array() : $views_data['seller_order_data'];
$order_currency = $seller_order->get_currency();
$cur_symbol     = get_woocommerce_currency_symbol( $order_currency );

$shipping_method = $seller_order->get_shipping_method();
$payment_method  = $seller_order->get_payment_method_title();
$total_payment   = 0;

do_action( 'wkmp_before_seller_order_review', $seller_order, $mp_order_data );

if ( ! empty( $order_data ) ) {
	?>
	<div class="mp-order-view wrap"><!-- Class: mp-order-view wrap starts here.  -->
		<div id="order_data_details"><!-- id: order_data_details starts here.  -->
			<?php
			do_action( 'wkmp_before_seller_print_invoice_button', $seller_order );
			if ( apply_filters( 'wkmp_enable_refund_for_the_order', true, $seller_order ) && 'wc-refunded' !== $order_status && ( empty( $seller_order_refund_data ) || ! empty( $seller_order_refund_data ) && trim( $seller_order_refund_data['refunded_amount'] ) < trim( $mp_order_data['total_seller_amount'] ) ) ) {
				?>
				<button class="button wkmp-order-refund-button"><?php esc_html_e( 'Refund', 'wk-marketplace' ); ?></button>
				<?php
			}
			if ( apply_filters( 'wkmp_enable_print_for_the_order', true, $order_id ) ) {
				?>
			<a href="<?php echo esc_url( site_url() . '/' . $wkmarketplace->seller_page_slug . '/invoice/' . base64_encode( $order_id ) ); ?>" target="_blank" class="button print-invoice"><?php esc_html_e( 'Print Invoice', 'wk-marketplace' ); ?></a>
				<?php
			}

			do_action( 'wkmp_after_seller_print_invoice_button', $seller_order );
			?>
			<h3><?php echo wp_sprintf( /* translators: %s: Order id. */ esc_html__( 'Order #%s', 'wk-marketplace' ), esc_html( $order_id ) ); ?></h3>

			<div class="wkmp_order_data_detail"> <!-- Class: wkmp_order_data_detail starts here.  -->
				<?php do_action( 'wkmp_order_details_before_order_table', $seller_order ); ?>
				<form method="post" id="wkmp-order-view-form">
					<table class="widefat">
						<thead>
						<tr>
							<th class="product-name"><b><?php esc_html_e( 'Product', 'wk-marketplace' ); ?></b></th>
							<th class="product-total"><b><?php esc_html_e( 'Total', 'wk-marketplace' ); ?></b></th>
							<th class="product-refund wkmp-order-refund" style="display:none;"><b><?php esc_html_e( 'Refund Quantity', 'wk-marketplace' ); ?></b></th>
						</tr>
						</thead>
						<tbody>
						<?php
						$total_payment = apply_filters( 'wkmp_add_order_fee_to_total', round( floatval( $mp_order_data['total_seller_amount'] ), 2 ), $mp_order_data['order_id'] );

						foreach ( $order_data as $product_id => $details ) {
							if ( $details['variable_id'] < 1 ) {
								?>
								<tr class="order_item alt-table-row">
									<td class="product-name toptable">
										<?php
										$prod_link = get_permalink( $product_id );
										if ( empty( $prod_link ) ) {
											echo esc_html( $details['product_name'] ); // No permalink, display product name directly.
										} else {
											?>
											<a class="wkmp-seller-product-name" target="_blank" href="<?php echo esc_url( $prod_link ); ?>"><?php echo esc_html( $details['product_name'] ); ?></a>
											<?php
										}
										?>
										<strong class="product-quantity">× <?php echo esc_html( $details['qty'] ); ?>
										<?php if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] ) ) { ?>
												<br>
												<span class="wkmp-refund wkmp-green"><?php echo esc_html( - $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] ); ?></span>
										<?php } ?>
										</strong>
										<dl class="variation">
										<?php
										if ( ! empty( $details['meta_data'] ) ) {
											foreach ( $details['meta_data'] as $m_data ) {
												echo '<dt class="variation-size">' . esc_html( wc_attribute_label( $m_data['key'] ) ) . ' : ' . wp_kses_post( $m_data['value'] ) . '</dt>';
											}
										}
										?>
										</dl>
										<?php do_action( 'wk_mp_append_order_meta_data', $product_id, $details, $order_id ); ?>
									</td>
									<td class="product-total toptable">
										<?php
										echo wp_kses_data( wc_price( $details['product_total_price'], array( 'currency' => $order_currency ) ) );
										if ( ! empty( $mp_order_data['product'][ $product_id ]['discount'] ) ) {
											?>
											<br>
											<span class="wkmp-order-discount"><?php echo wp_kses_data( wc_price( $mp_order_data['product'][ $product_id ]['discount'], array( 'currency' => $order_currency ) ) ) . ' ' . esc_html__( 'discount', 'wk-marketplace' ); ?></span>
											<?php
										}
										if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_total'] ) ) {
											?>
											<br>
											<span class="wkmp-refund wkmp-green"><?php echo wp_kses_data( wc_price( - $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_total'], array( 'currency' => $order_currency ) ) ); ?></span>
											<?php
										}
										include __DIR__ . '/wkmp-order-refund-product.php';
										?>
									</td>
									<td class="product-refund toptable wkmp-order-refund" style="display:none;">
										<?php
										$product_qty = $details['qty'];
										if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ] ) && $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] >= $product_qty ) {
											?>
											<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></p>
											<?php
										} elseif ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ] ) && $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] < $product_qty ) {
											$refund_qty     = $product_qty - $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'];
											$product_amount = ( $details['product_total_price'] - $mp_order_data['product'][ $product_id ]['commission'] ) / $product_qty;
											?>
											<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">
											<input type="number" name="refund_line_total[<?php echo esc_attr( $details['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $refund_qty ); ?>">
											<?php
										} else {
											$product_amount = 0;
											if ( isset( $mp_order_data['product'][ $product_id ] ) && $mp_order_data['product'][ $product_id ] ) {
												$product_amount = ( $details['product_total_price'] - $mp_order_data['product'][ $product_id ]['commission'] ) / $product_qty;
											}
											?>
											<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">
											<input type="number" name="refund_line_total[<?php echo esc_attr( $details['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $product_qty ); ?>">
										<?php } ?>
									</td>
								</tr>
							<?php } else { ?>
									<?php
									$product        = new \WC_Product( $product_id );
									$attribute      = $product->get_attributes();
									$variation      = new \WC_Product_Variation( $details['variable_id'] );
									$var_attributes = $variation->get_variation_attributes();
									?>
									<tr class="order_item alt-table-row">
										<td class="product-name toptable">
											<a target="_blank" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $details['product_name'] ); ?></a>
											<strong class="product-quantity">× <?php echo esc_html( $details['qty'] ); ?>
												<?php if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] ) ) { ?>
													<br>
													<span class="wkmp-refund wkmp-green"><?php echo esc_html( - $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] ); ?></span>
												<?php } ?>
											</strong>
											<dl class="variation">
												<?php
												foreach ( $attribute as $key => $value ) {
													$attribute_name = empty( $value['name'] ) ? '' : $value['name'];
													$attribute_prop = ( ! empty( $attribute_name ) && ! empty( $var_attributes[ 'attribute_' . strtolower( $attribute_name ) ] ) ) ? strtoupper( $var_attributes[ 'attribute_' . strtolower( $attribute_name ) ] ) : '';
													if ( empty( $attribute_prop ) ) {
														continue;
													}
													?>
													<dt class="variation-size"><?php echo esc_html( $attribute_name . ' : ' . $attribute_prop ); ?></dt>
												<?php } ?>
											</dl>
										</td>
										<td class="product-total toptable">
											<?php
											echo wp_kses_data( wc_price( $details['product_total_price'], array( 'currency' => $order_currency ) ) );
											if ( ! empty( $mp_order_data['product'][ $details['variable_id'] ]['discount'] ) ) {
												?>
												<br>
												<span class="wkmp-order-discount"> <?php echo wp_kses_data( wc_price( $mp_order_data['product'][ $details['variable_id'] ]['discount'], array( 'currency' => $order_currency ) ) ) . ' ' . esc_html__( 'discount', 'wk-marketplace' ); ?></span>
												<?php
											}
											if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_total'] ) ) {
												?>
												<br>
												<span class="wkmp-refund wkmp-green"><?php echo wp_kses_data( wc_price( - $seller_order_refund_data['line_items'][ $details['item_key'] ]['refund_total'], array( 'currency' => $order_currency ) ) ); ?></span>
												<?php
											}
											include __DIR__ . '/wkmp-order-refund-product.php';
											?>
										</td>
										<td class="product-refund toptable wkmp-order-refund" style="display:none;">
											<?php
											$product_qty = $details['qty'];

											if ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ] ) && $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] >= $product_qty ) {
												?>
												<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></p>
												<?php
											} elseif ( ! empty( $seller_order_refund_data['line_items'][ $details['item_key'] ] ) && $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'] < $product_qty ) {
												$refund_qty     = $product_qty - $seller_order_refund_data['line_items'][ $details['item_key'] ]['qty'];
												$product_amount = ( $mp_order_data['product_total'] - $mp_order_data['product'][ $details['variable_id'] ]['commission'] ) / $product_qty;
												?>
												<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">
												<input type="number" name="refund_line_total[<?php echo esc_attr( $details['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $refund_qty ); ?>">
												<?php
											} else {
												$product_amount = ( $mp_order_data['product_total'] - $mp_order_data['product'][ $details['variable_id'] ]['commission'] ) / $product_qty;
												?>
												<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $details['item_key'] ); ?>]" value="<?php echo esc_attr( $product_amount ); ?>">
												<input type="number" name="refund_line_total[<?php echo esc_attr( $details['item_key'] ); ?>]" class="form-control refund_line_total" data-order-item-id="<?php echo esc_attr( $details['item_key'] ); ?>" value="0" min="0" max="<?php echo esc_attr( $product_qty ); ?>">
											<?php } ?>
										</td>
									</tr>
									<?php
							}
						}

						if ( $mp_order_data['product_total'] !== $mp_order_data['total_seller_amount'] ) {
							$tip = $total_payment;

							$tip .= ' = ';
							$tip .= ( $mp_order_data['product_total'] ) . ' ( ' . __( 'Subtotal', 'wk-marketplace' ) . ' ) ';


							if ( $mp_order_data['total_commission'] > 0 ) {
								$tip .= ' - ';
								$tip .= ( $mp_order_data['total_commission'] ) . ' ( ' . __( 'Commission', 'wk-marketplace' ) . ' ) ';
							}

							if ( $mp_order_data['shipping'] > 0 ) {
								$tip .= ' + ';
								$tip .= ( $mp_order_data['shipping'] ) . ' ( ' . __( 'Shipping', 'wk-marketplace' ) . ' ) ';
							}

							if ( ! empty( $mp_order_data['tax'] ) ) {
								$tip .= sprintf( '+ %f ( %s ) ', $mp_order_data['tax'], esc_html__( ' Tax', 'wk-marketplace' ) );
							}
							$tip .= ' ';
						}

						$shipping_cost      = $mp_order_data['shipping'];
						$shipping_seller_id = $mp_order_data['seller_id'];
						$fees               = $seller_order->get_fees();
						?>
						</tbody>
						<tfoot>
						<?php if ( ! empty( $mp_order_data['discount'] ) && ! empty( array_sum( $mp_order_data['discount'] ) ) ) { ?>
							<tr>
								<th scope="row"><b><?php esc_html_e( 'Discount', 'wk-marketplace' ); ?>:</b></th>
								<td class="toptable"><?php echo wp_kses_data( wc_price( array_sum( $mp_order_data['discount'] ), array( 'currency' => $order_currency ) ) ); ?></td>
							</tr>
							<?php
						}

						foreach ( $seller_order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ) {
							$shipping_meta_data = $shipping_item_obj->get_meta_data();
							$shipping_meta_data = empty( $shipping_meta_data ) ? array() : $shipping_meta_data;
							$ship_seller_id     = 0;

							foreach ( $shipping_meta_data as $meta_data ) {
								$get_data = $meta_data->get_data();

								if ( ! empty( $get_data['key'] ) && '_wkmp_seller_id' === $get_data['key'] ) {
									$ship_seller_id = empty( $get_data['value'] ) ? 0 : intval( $get_data['value'] );
									if ( $ship_seller_id > 0 ) {
										break;
									}
								}

								if ( empty( $ship_seller_id ) ) {
									if ( ! empty( $get_data['key'] ) && 'Store name' === $get_data['key'] ) { // Backward compatibility.
										$ship_store_name = empty( $get_data['value'] ) ? '' : $get_data['value'];

										if ( ! empty( $ship_store_name ) ) {
											$ship_seller_id = $wkmarketplace->wkmp_get_seller_id_by_shop_name( $ship_store_name );
											if ( $ship_seller_id > 0 ) {
												break;
											}
										}
									}
								}
							}

							if ( intval( $ship_seller_id ) !== intval( $shipping_seller_id ) ) {
								continue;
							}

							$shipping_method_title = $shipping_item_obj->get_method_title();
							$shipping_tax          = $shipping_item_obj->get_taxes()['total'];
							?>
							<tr>
								<th scope="row"><b><?php esc_html_e( 'Shipping: ', 'wk-marketplace' ); ?></b></th>
								<td class="toptable">
									<?php echo esc_html( $cur_symbol . ( $shipping_cost ? $shipping_cost : 0 ) ); ?>
									<i> <?php esc_html_e( 'via ', 'wk-marketplace' ); ?><?php echo esc_html( $shipping_method_title ); ?></i>
									<?php
									if ( ! empty( $seller_order_refund_data['line_items'][ $item_id ]['refund_total'] ) ) {
										?>
										<br>
										<span class="wkmp-refund wkmp-green"><?php echo wp_kses_data( wc_price( - $seller_order_refund_data['line_items'][ $item_id ]['refund_total'], array( 'currency' => $order_currency ) ) ); ?></span>
									<?php } ?>
								</td>
								<td class="toptable wkmp-order-refund">
									<?php if ( ! empty( $seller_order_refund_data['line_items'][ $item_id ] ) ) { ?>
										<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></p>
									<?php } else { ?>
										<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $shipping_cost ? $shipping_cost : 0 ); ?>">
										<input type="checkbox" name="refund_line_total[<?php echo esc_attr( $item_id ); ?>]" id="refund_line_total[<?php echo esc_attr( $item_id ); ?>]" class="refund_line_total" data-order-item-id="<?php echo esc_attr( $item_id ); ?>" value="1">
										<label for="refund_line_total[<?php echo esc_attr( $item_id ); ?>]"><?php esc_html_e( 'Check to Refund', 'wk-marketplace' ); ?></label>
									<?php } ?>
								</td>
							</tr>

							<?php
							foreach ( $shipping_tax as $tax_line_id => $tax_line_amount ) {
								if ( ! in_array( $tax_line_id, $seller_tax_rate_ids, true ) ) {
									continue;
								}
								$amount_remain = $tax_line_amount;
								?>
								<tr>
									<th><?php echo esc_html( $tax_list_name[ $tax_line_id ] ); ?>:</th>
									<td class="toptable">
										<?php echo wp_kses_data( wc_price( $tax_line_amount, array( 'currency' => $order_currency ) ) ); ?>
										<?php if ( ! empty( $seller_order_refund_data['line_items'][ $item_id ]['refund_tax'][ $tax_line_id ] ) ) { ?>
											<br>
											<span class="wkmp-refund wkmp-green"><?php echo wp_kses_data( wc_price( - $seller_order_refund_data['line_items'][ $item_id ]['refund_tax'][ $tax_line_id ], array( 'currency' => $order_currency ) ) ); ?></span>
											<?php
											$amount_remain -= $seller_order_refund_data['line_items'][ $item_id ]['refund_tax'][ $tax_line_id ];
										}
										?>
									</td>
									<td class="toptable wkmp-order-refund">
										<?php if ( ! empty( $seller_order_refund_data['line_items'][ $item_id ]['refund_tax'][ $tax_line_id ] ) ) { ?>
											<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></p>
										<?php } else { ?>
											<input type="hidden" class="refund_line_tax_amount" name="refund_line_tax_amount[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $tax_line_id ); ?>]" value="<?php echo esc_attr( $amount_remain ? $amount_remain : 0 ); ?>">
											<input type="checkbox" name="refund_line_tax[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $tax_line_id ); ?>]" id="refund_line_tax[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $tax_line_id ); ?>]" class="refund_line_total" data-order-item-id="<?php echo esc_attr( $tax_line_id ); ?>" value="1">
											<label for="refund_line_tax[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $tax_line_id ); ?>]"><?php esc_html_e( 'Check to Refund', 'wk-marketplace' ); ?></label>
										<?php } ?>
									</td>
								</tr>
								<?php
							}
						}

						if ( ! empty( $fees ) ) {
							$fee_amount = 0;
							foreach ( $fees as $key => $fee ) {
								$fee_name = $fee->get_data()['name'];
								if ( 'reward' === $key ) {
									if ( $fee['reward_data'] ) {
										$fee_amount = - 1 * round( floatval( apply_filters( 'mpmc_get_converted_price', ( $fee['reward_data'] * $reward_points ) ) ) );
									} else {
										continue;
									}
								} else {
									$fee_amount = floatval( $fee->get_data()['total'] );
								}

								if ( $fee_amount > 0 ) {
									?>
									<tr>
										<th scope="row"><b><?php echo esc_html( mb_convert_encoding( $fee_name, 'UTF-8' ) ); ?>:</b></th>
										<td class="td">
											<?php
											echo wp_kses_data( wc_price( $fee_amount, array( 'currency' => $order_currency ) ) );
											if ( ! empty( $seller_order_refund_data['line_items'][ $key ]['refund_total'] ) ) {
												?>
												<br>
												<span class="wkmp-refund wkmp-green"><?php echo wp_kses_data( wc_price( - $seller_order_refund_data['line_items'][ $key ]['refund_total'], array( 'currency' => $order_currency ) ) ); ?></span>
											<?php } ?>
										</td>
										<td class="toptable wkmp-order-refund">
											<?php if ( ! empty( $seller_order_refund_data['line_items'][ $key ] ) ) { ?>
												<p class="wkmp-green"><?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?></p>
											<?php } else { ?>
												<input type="hidden" class="item_refund_amount" name="item_refund_amount[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $fee_amount ); ?>">
												<input type="checkbox" name="refund_line_total[<?php echo esc_attr( $key ); ?>]" class="refund_line_total" data-order-item-id="<?php echo esc_attr( $key ); ?>" value="1">
												<label for="refund_line_total[<?php echo esc_attr( $key ); ?>]"><?php esc_html_e( 'Check to Refund', 'wk-marketplace' ); ?></label>
											<?php } ?>
										</td>
									</tr>
									<?php
								}
							}
						}

						if ( ! empty( $payment_method ) ) {
							?>
							<tr>
								<th scope="row"><b><?php esc_html_e( 'Payment Method: ', 'wk-marketplace' ); ?></b></th>
								<td class="toptable">
									<?php echo esc_html( $payment_method ); ?>
									<?php do_action( 'wkmp_after_payment_method_on_order_views', $seller_order ); ?>
								</td>
							</tr>
							<?php
						}
						if ( ! empty( $mp_order_data['total_commission'] ) && $mp_order_data['total_commission'] > 0 ) {
							?>
							<tr class="alt-table-row">
								<th scope="row"><b><?php esc_html_e( 'Admin Commission: ', 'wk-marketplace' ); ?></b></th>
								<td class="toptable">
									<span class="amount"><?php echo esc_html( $cur_symbol . $mp_order_data['total_commission'] ); ?></span>
								</td>
							</tr>
							<?php
						}

						if ( ! empty( $mp_order_data['tax'] ) ) {
							?>
							<tr class="alt-table-row">
								<th scope="row"><b><?php esc_html_e( 'Total Tax: ', 'wk-marketplace' ); ?></b></th>
								<td class="toptable" colspan="3">
									<p class="amount"><?php echo wp_kses_data( wc_price( $mp_order_data['tax'], array( 'currency' => $order_currency ) ) ); ?></p>
								</td>
							</tr>
							<?php
						}
						do_action( 'wkmp_add_seller_order_data', $mp_order_data, $seller_id );
						?>
						<tr class="alt-table-row">
							<th scope="row"><b><?php esc_html_e( 'Total: ', 'wk-marketplace' ); ?></b></th>
							<td class="toptable" colspan="2">
								<?php if ( ! empty( $seller_order_refund_data['refunded_amount'] ) ) { ?>
									<span class="amount"><strong><del><?php echo wp_kses_data( wc_price( $total_payment, array( 'currency' => $order_currency ) ) ); ?></del></strong></span>
									<?php if ( ! empty( $tip ) ) { ?>
										<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( $tip ); ?>"></span>
									<?php } ?>
									<span class="amount"> <?php echo wp_kses_data( wc_price( $total_payment - apply_filters( 'wkmp_add_order_fee_to_total', round( floatval( $seller_order_refund_data['refunded_amount'] ), 2 ), $mp_order_data['order_id'] ), array( 'currency' => $order_currency ) ) ); ?></span>
								<?php } else { ?>
									<span class="amount"><?php echo wp_kses_data( wc_price( $total_payment, array( 'currency' => $order_currency ) ) ); ?></span>
									<?php if ( ! empty( $tip ) ) { ?>
										<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr( $tip ); ?>"></span>
										<?php
									}
								}
								?>
							</td>
						</tr>

						<?php
						if ( ! empty( $seller_order_refund_data['refunded_amount'] ) ) {
							?>
							<tr class="alt-table-row wkmp-green">
								<th scope="row"><b><?php esc_html_e( 'Refunded: ', 'wk-marketplace' ); ?></b></th>
								<td class="toptable" colspan="3">
									<p class="amount"><?php echo wp_kses_data( wc_price( apply_filters( 'wkmp_add_order_fee_to_total', round( floatval( $seller_order_refund_data['refunded_amount'] ), 2 ), $mp_order_data['order_id'] ), array( 'currency' => $order_currency ) ) ); ?></p>
								</td>
							</tr>
						<?php } ?>

						<tr class="wkmp-order-refund" style="border: solid; display:none;">
							<th scope="row"><b><?php esc_html_e( 'Refund Reason (Optional): ', 'wk-marketplace' ); ?></b></th>
							<td class="toptable">
								<input type="text" name="refund_reason" id="refund-reason" class="form-control">
							</td>
							<td class="toptable">
								<input type="checkbox" id="restock_refunded_items" name="restock_refunded_items" class="" value="1">
								<label for="restock_refunded_items"><?php esc_html_e( 'Restock Refunded items', 'wk-marketplace' ); ?></label>
							</td>
						</tr>

						<tr class="wkmp-order-refund" style="display:none;">
							<th scope="row"><b><?php esc_html_e( 'Refund Amount: ', 'wk-marketplace' ); ?></b></th>
							<td class="toptable" style="display:flex;">
								<input type="hidden" id="mp-order-id" name="mp-order-id" value="<?php echo esc_attr( $order_id ); ?>">
								<input type="number" name="refund_total" id="refund-amount" class="form-control" disabled="disabled" step="0.01">
								<label for="refund-amount"><?php echo esc_html( $cur_symbol ); ?></label>
							</td>
						</tr>

						<tr class="wkmp-order-refund" style="display:none;">
							<th scope="row"></th>
							<td class="toptable" colspan="2">
								<?php wp_nonce_field( 'wkmp-seller-refund-nonce-action', 'wkmp-seller-refund-nonce-value' ); ?>
								<input type='hidden' name='mp-seller-id' value="<?php echo esc_attr( $seller_id ); ?>"/>
								<input type="submit" name="refund_manually" class="button form-control" value="<?php esc_attr_e( 'Refund Manually', 'wk-marketplace' ); ?>">
								<?php if ( false !== $payment_gateway && $payment_gateway->can_refund_order( $seller_order ) ) { ?>
									<input type="submit" name="do_api_refund" class="button form-control" value="<?php printf( /* Translators: %s: Gateway name. */ esc_attr__( 'Refund via %s', 'wk-marketplace' ), esc_attr( $gateway_name ) ); ?>">
								<?php } ?>
							</td>
						</tr>
						</tfoot>
					</table>
				</form>
			</div> <!-- Class: wkmp_order_data_detail ends here.  -->
		</div><!-- order_data_details end here -->
		<header>
			<h3><?php esc_html_e( 'Customer details', 'wk-marketplace' ); ?></h3>
		</header>
		<table class="shop_table shop_table_responsive customer_details widefat customer-responsive-view">
			<tbody>
			<tr>
				<th><b><?php esc_html_e( 'Email', 'wk-marketplace' ); ?>:</b></th>
				<td data-title="Email" class="toptable"><?php echo esc_html( $seller_order->get_billing_email() ); ?></td>
			</tr>
			<tr class="alt-table-row">
				<th><b><?php esc_html_e( 'Telephone', 'wk-marketplace' ); ?>:</b></th>
				<td data-title="Telephone" class="toptable"><?php echo esc_html( $seller_order->get_billing_phone() ); ?></td>
			</tr>
			</tbody>
		</table>

		<div class="col2-set addresses">
			<div class="col-1">
				<header class="title">
					<h3><?php esc_html_e( 'Billing Address', 'wk-marketplace' ); ?></h3>
				</header>
				<address>
					<?php echo wp_kses_post( $seller_order->get_formatted_billing_address( esc_html__( 'N/A', 'wk-marketplace' ) ) ); ?>
				</address>
			</div><!-- /.col-1 -->
			<div class="col-2">
				<header class="title">
					<h3><?php esc_html_e( 'Shipping Address', 'wk-marketplace' ); ?></h3>
				</header>
				<address>
					<?php echo wp_kses_post( $seller_order->get_formatted_shipping_address( esc_html__( 'N/A', 'wk-marketplace' ) ) ); ?>
				</address>
			</div><!-- /.col-2 -->
		</div>

		<!-- Order status form start -->
		<div class="mp-status-manage-class">
			<header class="title">
				<h3><?php esc_html_e( 'Order Status', 'wk-marketplace' ); ?></h3>
			</header>
			<?php
			$translated_order_status = array(
				'on-hold'    => __( 'on-hold', 'wk-marketplace' ),
				'pending'    => __( 'pending payment', 'wk-marketplace' ),
				'processing' => __( 'processing', 'wk-marketplace' ),
				'completed'  => __( 'completed', 'wk-marketplace' ),
				'cancelled'  => __( 'cancelled', 'wk-marketplace' ),
				'refunded'   => __( 'refunded', 'wk-marketplace' ),
				'failed'     => __( 'failed', 'wk-marketplace' ),
				'trash'      => __( 'Trashed', 'wk-marketplace' ),
			);

			if ( 'wc-completed' !== $order_status ) {
				?>
				<form method="POST">
					<table class="shop_table shop_table_responsive customer_details widefat">
						<tbody>
						<tr>
							<td><label for="mp-status"><?php esc_html_e( 'Status', 'wk-marketplace' ); ?>:</label></td>
							<td>
								<select name="mp-order-status" id="mp-status" class="wkmp-select form-control">
									<?php foreach ( wc_get_order_statuses() as $key => $value ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $order_status === $key ) ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<?php
							wp_nonce_field( 'mp_order_status_nonce_action', 'mp_order_status_nonce' );
							?>
							<input type='hidden' name='mp-order-id' value="<?php echo esc_attr( $order_id ); ?>"/>
							<input type='hidden' name='mp-seller-id' value="<?php echo esc_attr( $seller_id ); ?>"/>
							<input type='hidden' name='mp-old-order-status' value="<?php echo esc_attr( $order_status ); ?>"/>
							<td><input type="submit" name="mp-submit-status" class="button" value="<?php esc_attr_e( 'Save', 'wk-marketplace' ); ?>"/></td>
						</tr>
						</tbody>
					</table>
				</form>
			<?php } else { ?>
				<p><?php printf( /* translators: %s: Order status. */ esc_html__( 'Status: Order status is %s', 'wk-marketplace' ), esc_html( $translated_order_status[ $seller_order->get_status() ] ) ); ?></p>
				<?php
			}
			do_action( 'wkmp_after_seller_order_status', $order_id );
			?>
		</div><!-- Order status form ends  -->

		<div class="mp-order-notes-refunds"><!-- Order notes and refund sections starts -->
			<?php
			$args = array(
				'post_id' => $order_id,
				'orderby' => 'comment_ID',
				'order'   => 'DESC',
				'approve' => 'approve',
				'type'    => 'order_note',
			);
			remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10 );
			$notes = get_comments( $args );
			add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
			?>
			<div class="mp-order-notes">
				<h3><?php esc_html_e( 'Order Notes', 'wk-marketplace' ); ?> </h3>
				<ul class="order_notes">
					<?php
					if ( $notes ) {
						foreach ( $notes as $note ) {
							?>
							<li>
								<div class="note_content">
									<?php echo wp_kses( wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ), array( 'p' => array() ) ); ?>
								</div>
								<p class="meta">
									<abbr class="exact-date" title="<?php echo esc_attr( $note->comment_date ); ?>"><?php printf( /* translators: %1$s: Date, %2%s: Time. */ esc_html__( 'added on %1$s at %2$s', 'wk-marketplace' ), esc_attr( date_i18n( wc_date_format(), strtotime( $note->comment_date ) ) ), esc_attr( date_i18n( wc_time_format(), strtotime( $note->comment_date ) ) ) ); ?></abbr>
									<?php
									if ( __( 'WooCommerce', 'wk-marketplace' ) !== $note->comment_author ) {
										printf( /* translators: %s: Author. */ esc_html__( ' by %s', 'wk-marketplace' ), esc_html( $note->comment_author ) );
									}
									?>
								</p>
							</li>
							<?php
						}
					} else {
						?>
						<li> <?php esc_html_e( 'There are no notes yet.', 'wk-marketplace' ); ?> </li>
					<?php } ?>
				</ul>
			</div>

			<?php
			$refunds = $seller_order->get_refunds();
			if ( ! empty( $refunds ) ) {
				?>
				<div class="wkmp-order-refund-section">
					<h3><?php esc_html_e( 'Order Refunds', 'wk-marketplace' ); ?> </h3>
					<ul class="order_refunds">
						<?php
						foreach ( $refunds as $refund ) {
							$who_refunded = get_user_by( 'ID', $refund->get_refunded_by() );
							?>
							<li>
								<div>
									<?php
									if ( $who_refunded instanceof \WP_User && $who_refunded->exists() ) {
										printf( /* translators: 1: refund id 2: refund date 3: username */ esc_html__( 'Refund #%1$s - %2$s by %3$s', 'wk-marketplace' ), esc_html( $refund->get_id() ), esc_html( wc_format_datetime( $refund->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ), sprintf( '<abbr class="refund_by" title="%1$s">%2$s</abbr>', /* translators: 1: ID who refunded */ sprintf( esc_attr__( 'ID: %d', 'wk-marketplace' ), absint( $who_refunded->ID ) ), esc_html( $who_refunded->display_name ) ) );
									} else {
										printf( /* translators: 1: refund id 2: refund date */ esc_html__( 'Refund #%1$s - %2$s', 'wk-marketplace' ), esc_html( $refund->get_id() ), esc_html( wc_format_datetime( $refund->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ) );
									}
									?>
									<span>
									<?php echo wp_kses_data( wc_price( '-' . $refund->get_amount(), array( 'currency' => $refund->get_currency() ) ) ); ?>
								</span>
								</div>
								<?php if ( $refund->get_reason() ) { ?>
									<span class="description"><?php echo esc_html( $refund->get_reason() ); ?></span>
								<?php } ?>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<?php
			}
			?>
		</div> <!-- Order notes and refund sections starts -->
	</div><!-- Class: mp-order-view wrap ends here.  -->
	<?php
} else {
	?>
	<h1><?php esc_html_e( 'Cheat\'n huh ???', 'wk-marketplace' ); ?></h1>
	<p><?php esc_html_e( 'Sorry, You can\'t access other seller\'s orders.', 'wk-marketplace' ); ?></p>
	<?php
}

do_action( 'wkmp_after_seller_order_review', $seller_order, $mp_order_data );
