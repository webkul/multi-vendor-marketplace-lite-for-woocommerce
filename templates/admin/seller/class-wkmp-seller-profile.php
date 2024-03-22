<?php
/**
 * Admin template Functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Profile' ) ) {
	/**
	 * Admin seller profile templates class.
	 *
	 * Class WKMP_Seller_Profile
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Seller_Profile {
		/**
		 * Seller Info.
		 *
		 * @var array $seller_info Seller info.
		 */
		private $seller_info;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Seller_Profile constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			global $wkmarketplace;

			$this->seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );
			if ( ! empty( $this->seller_info->ID ) ) {
				$this->wkmp_display_seller_info();
			} else {
				$page_name = \WK_Caching::wk_get_request_data( 'page' );
				$url       = 'admin.php?page=' . $page_name . '&success=2';
				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
		}

		/**
		 * Seller info.
		 */
		public function wkmp_display_seller_info() {
			?>
			<div class="mp-seller-detail">
				<div class="mp-seller-data-wrapper">
					<div class="mp-seller-data">
						<table>
							<tbody>
							<tr>
								<td><p><b><?php esc_html_e( 'Username: ', 'wk-marketplace' ); ?></b></p></td>
								<td><p><?php echo empty( $this->seller_info->user_login ) ? '' : esc_html( $this->seller_info->user_login ); ?></p></td>
							</tr>
							<tr>
								<td><p><b><?php esc_html_e( 'Email: ', 'wk-marketplace' ); ?></b></p></td>
								<td><p>
								<?php
								if ( ! empty( $this->seller_info->user_email ) ) {
									?>
									<a class="wkmp-seller-detail-email" href="mailto:<?php echo esc_attr( $this->seller_info->user_email ); ?>"><?php echo esc_html( $this->seller_info->user_email ); ?></a>
									<?php
								}
								?>
								</p></td>
							</tr>
							<tr>
								<td><p><b><?php esc_html_e( 'Display name: ', 'wk-marketplace' ); ?></b></p></td>
								<td><p><?php echo empty( $this->seller_info->display_name ) ? '' : esc_html( $this->seller_info->display_name ); ?></p></td>
							</tr>
							<tr>
								<td><p><b><?php esc_html_e( 'Shop Name: ', 'wk-marketplace' ); ?></b></p></td>
								<td><p><?php echo empty( $this->seller_info->shop_name ) ? '' : esc_html( $this->seller_info->shop_name ); ?></p></td>
							</tr>
							<tr>
								<td><p><b><?php esc_html_e( 'Shop Address: ', 'wk-marketplace' ); ?></b></p></td>
								<td><p><?php echo empty( $this->seller_info->shop_address ) ? '' : esc_html( $this->seller_info->shop_address ); ?></p></td>
							</tr>
							<tr>
								<td><p><b><?php esc_html_e( 'Payment Details: ', 'wk-marketplace' ); ?></b></p></td>
								<td>
									<p><?php echo isset( $this->seller_info->mp_seller_payment_details ) ? esc_html( $this->seller_info->mp_seller_payment_details ) : esc_html__( 'No information provided.', 'wk-marketplace' ); ?></p>
								</td>
							</tr>
							<?php do_action( 'mp_manage_seller_details', $this->seller_info ); ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
