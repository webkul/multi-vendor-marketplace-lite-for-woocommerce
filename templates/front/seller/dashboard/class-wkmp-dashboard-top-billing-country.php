<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Dashboard_Top_Billing_Country' ) ) {
	/**
	 * Dashboard top billing country.
	 *
	 * Class WKMP_Dashboard_Top_Billing_Country
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Dashboard
	 */
	class WKMP_Dashboard_Top_Billing_Country {
		/**
		 * Constructor of the class.
		 *
		 * WKMP_Dashboard_Top_Billing_Country constructor.
		 *
		 * @param object $db_obj DB Object.
		 * @param object $marketplace Marketplace object.
		 * @param object $seller_orders Seller orders.
		 * @param int    $seller_id Seller id.
		 */
		public function __construct( $db_obj, $marketplace, $seller_orders, $seller_id ) {
			$this->wkmp_show_top_billing_country( $seller_id );
		}

		/**
		 * Show top billing country.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_show_top_billing_country( $seller_id ) {
			?>
			<div class="mp-store-top-billing-country">
				<h4><?php esc_html_e( 'Top Billing Countries', 'wk-marketplace' ); ?></h4>
				<div id="regions_div"></div>
			<?php
			$array_data = $this->wkmp_get_data( $seller_id );

			if ( count( $array_data ) > 1 ) {
				$map_api_key     = get_option( '_wkmp_google_map_api_key', false );
				$primary_color   = apply_filters( 'wkmp_active_color_code', '#96588a' );
				$secondary_color = apply_filters( 'wkmp_graph_dataset_color_code', '#673AB7' );
				?>
				<script>
					var data_array = <?php echo wp_json_encode( $array_data ); ?>;

					google.charts.load('current', {
						'packages': ['geochart'],
						'mapsApiKey': '<?php echo esc_attr( $map_api_key ); ?>'
					});

					google.charts.setOnLoadCallback(drawRegionsMap);

					function drawRegionsMap(){
						var data = google.visualization.arrayToDataTable(data_array);
						var options = {
							dataMode: 'regions',
							colorAxis: {colors: [<?php echo "'" . esc_attr( $secondary_color ) . "'" . ',' . "'" . esc_attr( $primary_color ) . "'"; ?>]}
						};

						var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));

						chart.draw(data, options);
					}
				</script>
				<?php
			} else {
				?>
				<p class="wkmp-no-country-data"><?php esc_html_e( 'There is no country data.', 'wk-marketplace' ); ?></p>
				<?php
			}
			?>
			</div>
				<?php
		}

		/**
		 * Get data.
		 *
		 * @param int $seller_id Seller Id.
		 *
		 * @return array
		 */
		private function wkmp_get_data( $seller_id ) {
			global $wpdb;

			$order_data = array();

			if ( $seller_id > 0 ) {
				$mp_orders = $wpdb->get_results( $wpdb->prepare( "SELECT mpo.amount, mpo.order_id, mpo.product_id FROM {$wpdb->prefix}mporders mpo WHERE mpo.seller_id = %d", esc_sql( $seller_id ) ), ARRAY_A );

				$final_orders = array();

				foreach ( $mp_orders as $value ) {
					$order_id = empty( $value['order_id'] ) ? 0 : intval( $value['order_id'] );
					if ( $order_id > 0 ) {
						$order_obj = wc_get_order( $order_id );

						if ( $order_obj instanceof \WC_Order ) {
							$billing_country = $order_obj->get_billing_country();

							if ( ! empty( $billing_country ) ) {
								if ( isset( $final_orders[ $billing_country ] ) ) {
									$final_orders[ $billing_country ]['amount'] += floatval( $value['amount'] );

									if ( ! in_array( $order_id, $final_orders[ $billing_country ]['orders'], true ) ) {
										$final_orders[ $billing_country ]['orders'][] = $order_id;
									}
								} else {
									$final_orders[ $billing_country ]           = array( 'amount' => floatval( $value['amount'] ) );
									$final_orders[ $billing_country ]['orders'] = array( $order_id );
								}
							}
						}
					}
				}

				$order_data[] = array(
					esc_html__( 'Country', 'wk-marketplace' ),
					esc_html__( 'Total', 'wk-marketplace' ),
					esc_html__( 'Order', 'wk-marketplace' ),
				);

				foreach ( $final_orders as $country => $data ) {
					$country_name = WC()->countries->countries[ $country ];
					$total        = $data['amount'];
					$order_count  = count( $data['orders'] );

					$array_local  = array( $country_name, $total, $order_count );
					$order_data[] = $array_local;
				}
			}

			return $order_data;
		}
	}
}
