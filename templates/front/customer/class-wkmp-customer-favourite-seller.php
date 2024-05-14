<?php
/**
 * Customer Favourite Seller Class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Customer;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper;

if ( ! class_exists( 'WKMP_Customer_Favourite_Seller' ) ) {
	/**
	 * Customer Favorite Seller Class.
	 *
	 * Class WKMP_Customer_Favorite_Seller
	 *
	 * @package WkMarketplace\Templates\Front\Customer
	 */
	class WKMP_Customer_Favourite_Seller {
		/**
		 * Customer id.
		 *
		 * @var int $customer_id Customer id.
		 */
		private $customer_id;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Customer_Favourite_Seller constructor.
		 *
		 * @param int $customer_id Customer id.
		 */
		public function __construct( $customer_id = 0 ) {
			$this->customer_id = $customer_id;
			$nonce             = \WK_Caching::wk_get_request_data( 'wkmp-delete-favourite-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-delete-favourite-nonce-action' ) ) {
				$selected = empty( $_POST['selected'] ) ? array() : wc_clean( wp_unslash( $_POST['selected'] ) );

				if ( ! empty( $selected ) ) {
					// Delete favorite sellers.
					$this->wkmp_delete_customer_favorite_list( $selected );
				}
			}

			$seller_id = \WK_Caching::wk_get_request_data( 'seller-id', array( 'filter' => 'int' ) );

			// Delete favorite seller.
			if ( $seller_id > 0 ) {
				$this->wkmp_delete_customer_favorite_list( array( $seller_id ) );
			}

			$this->wkmp_display_customer_favourite_list();
		}

		/**
		 * Display Seller List
		 */
		public function wkmp_display_customer_favourite_list() {
			global $wkmarketplace;

			$db_obj     = Helper\WKMP_General_Queries::get_instance();
			$seller_ids = $db_obj->wkmp_get_customer_favorite_seller_ids( $this->customer_id );

			$sellers = array();

			foreach ( $seller_ids as $seller_id ) {
				$seller_info  = $wkmarketplace->wkmp_get_seller_info( $seller_id );
				$avatar_image = WKMP_LITE_PLUGIN_URL . 'assets/images/generic-male.png';
				$avatar_image = empty( $seller_info->avatar_image ) ? $avatar_image : $seller_info->avatar_image;

				$seller_name  = empty( $seller_info->first_name ) ? '' : $seller_info->first_name;
				$seller_name .= empty( $seller_info->last_name ) ? '' : ' ' . $seller_info->last_name;
				$shop_address = empty( $seller_info->shop_address ) ? '' : ' ' . $seller_info->shop_address;

				$sellers[] = array(
					'seller_id'  => $seller_id,
					'image'      => $avatar_image,
					'name'       => $seller_name,
					'store_name' => $shop_address,
					'store_href' => $wkmarketplace->wkmp_get_seller_store_url( $seller_id ),
					'delete'     => get_permalink() . 'favourite-seller/?seller_id=' . $seller_id,
				);
			}

			$pagination = $wkmarketplace->wkmp_get_pagination( count( $sellers ), 1, count( $sellers ) ? count( $sellers ) : 20, '' );
			?>
			<div class="wkmp-action-section left wkmp-text-left">
				<button type="button" data-form_id="#wkmp-delete-favourite-seller" class="button wkmp-bulk-delete" title="<?php esc_attr_e( 'Delete', 'wk-marketplace' ); ?>">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>
			<form action="" method="post" enctype="multipart/form-data" id="wkmp-delete-favourite-seller" style="margin-top:10px;margin-bottom:unset;">
				<div class="wkmp-table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
						<tr>
							<th style="width:1px;"><input type="checkbox" id="wkmp-checked-all"></th>
							<th><?php esc_html_e( 'Seller Profile', 'wk-marketplace' ); ?></th>
							<th><?php esc_html_e( 'Seller Name', 'wk-marketplace' ); ?></th>
							<th><?php esc_html_e( 'Seller Collection', 'wk-marketplace' ); ?></th>
							<th><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php if ( $sellers ) { ?>
							<?php foreach ( $sellers as $seller ) { ?>
								<tr>
									<td><input type="checkbox" name="selected[]" value="<?php echo esc_attr( $seller['seller_id'] ); ?>"/></td>
									<td><img src="<?php echo esc_url( $seller['image'] ); ?>" height="50" width="60" class="wkmp-img-thumbnail" style="display:unset;"/></td>
									<td><?php echo esc_html( $seller['name'] ); ?></td>
									<td><a href="<?php echo esc_url( $seller['store_href'] ); ?>"><?php echo esc_html( $seller['store_name'] ); ?></a></td>
									<td><button id="wkmp_delete_single_fav_seller" type="button" class="button"><span class="dashicons dashicons-trash"></span></button></td>
								</tr>
							<?php } ?>
						<?php } else { ?>
							<tr>
								<td colspan="7" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<?php wp_nonce_field( 'wkmp-delete-favourite-nonce-action', 'wkmp-delete-favourite-nonce' ); ?>
			</form>
			<?php
			echo wp_kses_post( $pagination['results'] );
			echo wp_kses_post( $pagination['pagination'] );
		}

		/**
		 * Delete seller from customer favorite list
		 *
		 * @param array $seller_ids Seller ids.
		 *
		 * @return void
		 */
		public function wkmp_delete_customer_favorite_list( $seller_ids ) {
			$seller_ids = array_map( 'intval', $seller_ids );

			if ( $seller_ids ) {
				$db_obj = Helper\WKMP_General_Queries::get_instance();
				foreach ( $seller_ids as $seller_id ) {
					$db_obj->wkmp_update_shop_followers( $seller_id, $this->customer_id );
				}

				wc_print_notice( esc_html__( 'Seller deleted successfully from your favorite seller list', 'wk-marketplace' ), 'success' );
			}
		}
	}
}
