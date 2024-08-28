<?php
/**
 * Seller product at front
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Dashboard_Order_Product' ) ) {
	/**
	 * Dashboard order product.
	 *
	 * Class WKMP_Dashboard_Order_Product
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Dashboard
	 */
	class WKMP_Dashboard_Order_Product {
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
		 * WKMP_Dashboard_Order_Product constructor.
		 *
		 * @param object $db_obj DB Object.
		 * @param object $marketplace Marketplace object.
		 * @param array  $seller_orders Seller orders.
		 * @param int    $seller_id Seller id.
		 */
		public function __construct( $db_obj, $marketplace, $seller_orders, $seller_id ) {
			$this->dashboard_db_obj = $db_obj;
			$this->seller_orders    = $seller_orders;
			$this->wkmp_show_seller_products( $seller_id );
		}

		/**
		 * Showing Seller products.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_show_seller_products( $seller_id ) {
			$seller_orders  = $this->seller_orders;
			$order_ids      = $seller_orders['order_id'];
			$total_products = $this->dashboard_db_obj->wkmp_get_total_products_count( $seller_id );
			$order_items    = $this->dashboard_db_obj->wkmp_top_3_product( $seller_id );
			$status         = array();
			$total_orders   = empty( $seller_orders['total_orders'] ) ? 0 : intval( $seller_orders['total_orders'] );
			$total_orders   = apply_filters( 'wkmp_get_total_order_count', $total_orders, $seller_id );

			foreach ( explode( ',', $order_ids ) as $value ) {
				$status[] = get_post_field( 'post_status', $value );
			}

			$status = wp_json_encode( array( 'status' => array_count_values( $status ) ) );

			$args = array(
				'post_type'      => 'product',
				'author'         => $seller_id,
				'meta_key'       => 'total_sales',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			);

			$loop            = new \WP_Query( $args );
			$primary_color   = apply_filters( 'wkmp_active_color_code', '#96588a' );
			list($r, $g, $b) = sscanf( $primary_color, '#%02x%02x%02x' );

			$secondary_color    = apply_filters( 'wkmp_graph_dataset_color_code', '#673AB7' );
			list($rs, $gs, $bs) = sscanf( $secondary_color, '#%02x%02x%02x' );

			$shift = 50;
			?>
			<div class="mp-store-order-product-section">
				<div class="mp-store-order-status-section">
					<div class="section-header">
						<div class="summary-stats">
							<h2><?php echo esc_html( $total_orders ); ?></h2>
							<p><?php esc_html_e( 'Total Orders', 'wk-marketplace' ); ?></p>
						</div>
						<div class="summary-icon order-icon"></div>
					</div>

					<div class="section-body" style="min-height: 280px;">
						<canvas id="mp-order-status-chart" style="width: 100%; height: 550px; position: absolute;"></canvas>
						<script>
							var statusVar = jQuery.parseJSON('<?php echo wp_kses_post( $status ); ?>')
							var statusArr = new Array()
							var $labels = [
								'wc-completed',
								'wc-pending',
								'wc-processing',
								'wc-on-hold',
								'wc-cancelled',
								'wc-refunded',
								'wc-failed'
							]
							jQuery.each($labels, function (i) {
								if (statusVar.status[$labels[i]]) {
									statusArr.push(statusVar.status[$labels[i]])
								} else {
									statusArr.push(0)
								}
							})

							var data = {
								datasets: [{
									data: statusArr,
									backgroundColor: [
										'rgba(<?php echo esc_attr( $r . ',' . $g . ',' . $b . ', 1' ); ?>)',
										'rgba(<?php echo esc_attr( $r . ',' . ( $g + $shift ) . ',' . ( $b + $shift ) . ', 1' ); ?>)',
										'rgba(<?php echo esc_attr( $r . ',' . ( $g + ( 1.5 * $shift ) ) . ',' . ( $b + ( 1.5 * $shift ) ) . ', 1' ); ?>)',
										'rgba(<?php echo esc_attr( $r . ',' . ( $g + ( 2 * $shift ) ) . ',' . ( $b + ( 2 * $shift ) ) . ', 1' ); ?>)',
										'rgba(<?php echo esc_attr( $rs . ',' . $gs . ',' . $bs . ', 1' ); ?>)',
										'rgba(<?php echo esc_attr( $rs . ',' . ( $gs + $shift ) . ',' . ( $bs + $shift ) . ', 1' ); ?>)',
										'rgba(<?php echo esc_attr( $rs . ',' . ( $gs + ( 1.5 * $shift ) ) . ',' . ( $bs + ( 1.5 * $shift ) ) . ', 1' ); ?>)'
									],
								}],

								// These labels appear in the legend and in the tooltips when hovering different arcs
								labels: [
									'<?php esc_html_e( 'Completed', 'wk-marketplace' ); ?>',
									'<?php esc_html_e( 'Pending', 'wk-marketplace' ); ?>',
									'<?php esc_html_e( 'Processing', 'wk-marketplace' ); ?>',
									'<?php esc_html_e( 'OnHold', 'wk-marketplace' ); ?>',
									'<?php esc_html_e( 'Cancelled', 'wk-marketplace' ); ?>',
									'<?php esc_html_e( 'Refunded', 'wk-marketplace' ); ?>',
									'<?php esc_html_e( 'Failed', 'wk-marketplace' ); ?>'
								]
							};
							var options = {
								legend: {
									display: true,
									position: 'right',
									verticalAlign: "center",
									labels: {
										boxWidth: 20,
										padding: 15
									}
								},
								responsive: true
							};
							var ctx = document.getElementById("mp-order-status-chart").getContext('2d');

							var myDoughnutChart = new Chart(ctx, {
								type: 'doughnut',
								data: data,
								options: options
							});
						</script>
					</div>

				</div>

				<div class="mp-store-product-status-section">

					<div class="section-header">
						<div class="summary-stats">
							<h2><?php echo esc_html( $total_products ); ?></h2>
							<p><?php esc_html_e( 'Total Products', 'wk-marketplace' ); ?></p>
						</div>
						<div class="summary-icon cubes"></div>
					</div>

					<div class="section-body">
						<p><?php esc_html_e( 'Top Selling Product', 'wk-marketplace' ); ?></p>
						<div class="product-list">
							<?php foreach ( $order_items as $value ) : ?>
								<a href="<?php echo esc_url( get_permalink( $value->ID ) ); ?>"><?php echo esc_html( $value->item_name ); ?></a>
								<p><?php echo esc_html( $value->sales ) . ' ' . esc_html__( 'Sale', 'wk-marketplace' ); ?></p>
							<?php endforeach; ?>
						</div>
					</div>

					<?php if ( $loop->have_posts() ) : ?>
						<div class="section-footer">
							<?php
							while ( $loop->have_posts() ) :
								$loop->the_post();
								?>
								<a id="id-<?php the_id(); ?>" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
									<?php the_title(); ?></a>
								<p><?php echo esc_html( get_post_meta( get_the_ID(), 'total_sales', true ) ); ?><?php esc_html_e( ' Least Sale', 'wk-marketplace' ); ?></p>

							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}
}
