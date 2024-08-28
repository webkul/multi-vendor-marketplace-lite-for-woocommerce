<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WC_Admin_Report' ) ) {
	include WC_ABSPATH . 'includes/admin/reports/class-wc-admin-report.php';
}

if ( ! class_exists( 'WKMP_Dashboard_Sale_Order' ) ) {
	/**
	 * Dashboard sale order.
	 *
	 * Class WKMP_Dashboard_Sale_Order
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Dashboard
	 */
	class WKMP_Dashboard_Sale_Order extends \WC_Admin_Report {
		/**
		 * Dashboard DB Object.
		 *
		 * @var object $dashboard_db_obj Dashboard DB Object.
		 */
		private $dashboard_db_obj;

		/**
		 * Seller orders.
		 *
		 * @var array $seller_orders Seller orders.
		 */
		private $seller_orders;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Dashboard_Sale_Order constructor.
		 *
		 * @param object $db_obj DB Object.
		 * @param object $marketplace Marketplace object.
		 * @param array  $seller_orders Seller orders.
		 * @param int    $seller_id Seller id.
		 */
		public function __construct( $db_obj, $marketplace, $seller_orders, $seller_id ) {
			$this->dashboard_db_obj = $db_obj;
			$this->seller_orders    = $seller_orders;

			$this->wkmp_show_sale_orders( $seller_id );
		}

		/**
		 * Show sale orders.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_show_sale_orders( $seller_id ) {
			$order_amount  = '';
			$seller_orders = $this->seller_orders;

			$time       = \WK_Caching::wk_get_request_data( 'sort' );
			$sort_array = array(
				'year',
				'month',
				'7day',
				'last_month',
			);

			if ( ! in_array( $time, $sort_array, true ) ) {
				$time = 'year';
			}

			$this->calculate_current_range( $time );

			if ( ! empty( $seller_orders['order_id'] ) ) {
				$seller_order_ids = $seller_orders['order_id'];
				$seller_order_ids = explode( ',', $seller_order_ids );

				$data = (array) $this->get_order_report_data(
					array(
						'data'         => array(
							'post_date'   => array(
								'type'     => 'post_data',
								'function' => '',
								'name'     => 'post_date',
							),
							'_line_total' => array(
								'type'     => 'order_item_meta',
								'function' => 'SUM',
								'name'     => 'total_sales',
							),
							'ID'          => array(
								'type'     => 'post_data',
								'function' => 'COUNT',
								'name'     => 'count',
							),
						),
						'where'        => array(
							array(
								'key'      => 'posts.ID',
								'type'     => 'post_data',
								'value'    => $seller_order_ids,
								'operator' => 'IN',
							),
						),
						'query_type'   => 'get_results',
						'filter_range' => false,
						'order_types'  => wc_get_order_types( 'order-count' ),
						'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
						'group_by'     => $this->group_by_query,
					)
				);

				$amount = array( 'order_amounts' => $this->prepare_chart_data( $data, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby ) );
				$count  = array( 'order_count' => $this->prepare_chart_data( $data, 'post_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby ) );

				switch ( $time ) {
					case 'year':
						$labels = array(
							esc_html__( 'Jan', 'wk-marketplace' ),
							esc_html__( 'Feb', 'wk-marketplace' ),
							esc_html__( 'Mar', 'wk-marketplace' ),
							esc_html__( 'Apr', 'wk-marketplace' ),
							esc_html__( 'May', 'wk-marketplace' ),
							esc_html__( 'Jun', 'wk-marketplace' ),
							esc_html__( 'Jul', 'wk-marketplace' ),
							esc_html__( 'Aug', 'wk-marketplace' ),
							esc_html__( 'Sep', 'wk-marketplace' ),
							esc_html__( 'Oct', 'wk-marketplace' ),
							esc_html__( 'Nov', 'wk-marketplace' ),
							esc_html__( 'Dec', 'wk-marketplace' ),
						);
						break;

					case 'month':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = gmdate( 'd M', substr( $key, 0, - 3 ) );
						}
						break;

					case '7day':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = gmdate( 'd M', substr( $key, 0, - 3 ) );
						}
						break;

					case 'last_month':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = gmdate( 'd M', substr( $key, 0, - 3 ) );
						}
						break;

					default:
						break;
				}

				$order_amount = wp_json_encode(
					array(
						'order_amount' => array_map( array( $this->dashboard_db_obj, 'wkmp_round_chart_totals' ), array_values( $amount['order_amounts'] ) ),
						'labels'       => $labels,
						'count'        => array_map( array( $this->dashboard_db_obj, 'wkmp_round_chart_totals' ), array_values( $count['order_count'] ) ),
					)
				);
			}

			if ( $order_amount ) {
				$primary_color   = apply_filters( 'wkmp_active_color_code', '#96588a' );
				$secondary_color = apply_filters( 'wkmp_graph_dataset_color_code', '#673AB7' );
				?>
				<div class="mp-store-sale-order-history-section">
					<div class="header">
						<h2><?php esc_html_e( 'Sale & Order History', 'wk-marketplace' ); ?></h2>
						<div class="select-interval">
							<form method="get">
								<?php
								if ( is_admin() ) {
									echo '<input type="hidden" name="page" value="seller" />';
								}
								?>
								<select id="mp-update-sale-order" name="sort" onchange='this.form.submit()'>
									<option value="year" <?php echo ( 'year' === $time ) ? 'selected' : ''; ?>><?php esc_html_e( 'This Year', 'wk-marketplace' ); ?></option>
									<option value="month" <?php echo ( 'month' === $time ) ? 'selected' : ''; ?>><?php esc_html_e( 'This Month', 'wk-marketplace' ); ?></option>
									<option value="last_month" <?php echo ( 'last_month' === $time ) ? 'selected' : ''; ?>><?php esc_html_e( 'Last Month', 'wk-marketplace' ); ?></option>
									<option value="7day" <?php echo ( '7day' === $time ) ? 'selected' : ''; ?>><?php esc_html_e( 'Last 7 Days', 'wk-marketplace' ); ?></option>
								</select>
							</form>
						</div>
					</div>
					<canvas id="sale-order-history" style="width: 100%;"></canvas>
				</div>

				<script>
					var order_data = jQuery.parseJSON('<?php echo wp_kses_post( $order_amount ); ?>');
					if (order_data) {
						lineChart(order_data)
					}

					function lineChart($order_amount) {
						var data = order_data.order_amount
						var count = order_data.count
						var label = order_data.labels
						$labels = new Array()
						$sales = new Array()
						$count = new Array()
						jQuery.each(data, function (i) {
							$labels.push(label[i])
							$sales.push(data[i][1])
						});

						jQuery.each(count, function (i) {
							$count.push(parseInt(count[i][1]))
						});

						var data = {
							labels: $labels,
							datasets: [
								{
									label: 'Sale',
									borderColor: '<?php echo esc_attr( $secondary_color ); ?>',
									backgroundColor: '<?php echo esc_attr( $secondary_color ); ?>',
									data: $sales,
									fill: false,
									yAxisID: 'y-axis-1'
								},
								{
									label: 'Order',
									borderColor: '<?php echo esc_attr( $primary_color ); ?>',
									backgroundColor: '<?php echo esc_attr( $primary_color ); ?>',
									data: $count,
									fill: false,
									yAxisID: 'y-axis-2'
								}
							]
						};

						var ctx = document.getElementById("sale-order-history").getContext("2d");
						new Chart(ctx, {
							type: 'line',
							data: data,
							stacked: false,
							options: {
								responsive: true,
								scales: {
									yAxes: [{
										type: 'linear',
										display: true,
										position: "left",
										id: 'y-axis-1',
										gridLines: {
											drawOnChartArea: false,
										},
										ticks: {
											callback: function (label, index, labels) {
												if (label >= 1000) {
													return label / 1000 + 'K';
												} else {
													if (Math.floor(label) === label) {
														return label;
													}
												}
											}
										}
									}, {
										type: 'linear',
										display: true,
										position: "right",
										id: 'y-axis-2',
										ticks: {
											callback: function (label, index, labels) {
												if (label >= 1000) {
													return label / 1000 + 'k';
												} else {
													if (Math.floor(label) === label) {
														return label;
													}
												}
											},
										},
										gridLines: {
											drawOnChartArea: false,
										},
									}
									],
									xAxes: [
										{
											gridLines: {
												display: false
											},
										}
									]
								}
							}
						})
					}
				</script>
				<?php
			}
		}
	}
}
