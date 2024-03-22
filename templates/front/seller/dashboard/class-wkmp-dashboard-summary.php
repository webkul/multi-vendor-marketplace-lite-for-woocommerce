<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Dashboard_Summary' ) ) {
	/**
	 * Dashboard summary.
	 *
	 * Class WKMP_Dashboard_Summary
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Dashboard
	 */
	class WKMP_Dashboard_Summary {
		/**
		 * Seller orders.
		 *
		 * @var object $seller_orders Seller orders.
		 */
		private $seller_orders;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Dashboard_Summary constructor.
		 *
		 * @param object $db_obj DB Object.
		 * @param object $marketplace Marketplace object.
		 * @param object $seller_orders Seller orders.
		 * @param int    $seller_id Seller id.
		 */
		public function __construct( $db_obj, $marketplace, $seller_orders, $seller_id ) {
			$this->seller_orders = $seller_orders;

			$this->wkmp_seller_dashboard_summary();
		}

		/**
		 * Show seller dashboard summary.
		 *
		 * @return void
		 */
		public function wkmp_seller_dashboard_summary() {
			$data          = $this->seller_orders;
			$total_payout  = isset( $data->paid_amount ) ? $data->paid_amount : 0;
			$total_sales   = isset( $data->seller_total_ammount ) ? $data->seller_total_ammount : 0;
			$total_refund  = isset( $data->total_refunded_amount ) ? $data->total_refunded_amount : 0;
			$remaining_amt = $total_sales - $total_payout;
			?>
			<div class="mp-store-summary">
				<div class="mp-store-summary-section life-time-sale">
					<div class="summary-stats">
						<h2><?php echo wp_kses_post( $this->get_formatted_price_html( $total_sales ) ); ?></h2>
						<p><?php esc_html_e( 'Life Time Sale', 'wk-marketplace' ); ?></p>
					</div>
					<div class="summary-icon"><span><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span></div>
				</div>
				<div class="mp-store-summary-section total-payout">
					<div class="summary-stats">
						<h2><?php echo wp_kses_post( $this->get_formatted_price_html( $total_payout ) ); ?></h2>
						<p><?php esc_html_e( 'Total Payout', 'wk-marketplace' ); ?></p>
					</div>
					<div class="summary-icon payout"></div>
				</div>
				<div class="mp-store-summary-section remaining-amount">
					<div class="summary-stats">
						<h2><?php echo wp_kses_post( $this->get_formatted_price_html( $remaining_amt ) ); ?></h2>
						<p><?php esc_html_e( 'Remaining Amount', 'wk-marketplace' ); ?></p>
					</div>
					<div class="summary-icon remaining"></div>
				</div>
				<div class="mp-store-summary-section life-time-sale">
					<div class="summary-stats">
						<h2><?php echo wp_kses_post( $this->get_formatted_price_html( $total_refund ) ); ?></h2>
						<p><?php esc_html_e( 'Refunded Amount', 'wk-marketplace' ); ?></p>
					</div>
					<div class="summary-icon"><span><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span></div>
				</div>
			</div>
			<?php
		}

		/**
		 * Returning formatted html.
		 *
		 * @param float $total Total amount.
		 *
		 * @return string
		 */
		public function get_formatted_price_html( $total ) {
			$currency_pos    = get_option( 'woocommerce_currency_pos' );
			$currency        = get_woocommerce_currency_symbol();
			$formatted_total = number_format( $total, 2 );

			if ( $total > 1000 ) {
				$formatted_total = number_format( $total / 1000, 2 ) . 'K';
			}

			$html = '';

			if ( 'left' === $currency_pos || 'left_space' === $currency_pos ) {
				$html .= '<sup>' . esc_html( $currency ) . '</sup>';
				$html .= ( 'left_space' === $currency_pos ) ? '&nbsp;' : '';
				$html .= esc_html( $formatted_total );
			} else {
				$html .= esc_html( $formatted_total );
				$html .= ( 'right_space' === $currency_pos ) ? '&nbsp;' : '';
				$html .= '<sup>' . esc_html( $currency ) . '</sup>';
			}

			return $html;
		}
	}
}
