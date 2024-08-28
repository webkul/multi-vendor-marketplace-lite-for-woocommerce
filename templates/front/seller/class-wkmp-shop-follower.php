<?php
/**
 * Seller Shop follower class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper;

if ( ! class_exists( 'WKMP_Shop_Follower' ) ) {
	/**
	 * Seller Shop follower.
	 *
	 * Class WKMP_Shop_Follower
	 *
	 * @package WkMarketplace\Templates\Front\Seller
	 */
	class WKMP_Shop_Follower {
		/**
		 * DB Object.
		 *
		 * @var Helper\WKMP_General_Queries $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Shop_Follower constructor.
		 */
		public function __construct() {
			$this->db_obj = Helper\WKMP_General_Queries::get_instance();
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}

		/**
		 * Delete follower.
		 *
		 * @param array $customer_ids Customer ids.
		 */
		public function wkmp_delete_followers( $customer_ids ) {
			foreach ( $customer_ids as $customer_id ) {
				$this->db_obj->wkmp_update_shop_followers( $this->seller_id, $customer_id );
			}

			wc_print_notice( esc_html__( 'Followers has been deleted successfully', 'wk-marketplace' ), 'success' );
		}

		/**
		 * Get Seller follower by seller id
		 *
		 * @param int    $seller_id seller id.
		 * @param string $search Search term.
		 *
		 * @return array $user_ids
		 */
		public function wkmp_get_seller_followers_data( $seller_id, $search = '' ) {
			global $wkmarketplace;
			$shop_followers = array();
			$seller_id      = empty( $seller_id ) ? 0 : intval( $seller_id );

			if ( empty( $seller_id ) ) {
				return $shop_followers;
			}

			$shop_follower_ids = $this->db_obj->wkmp_get_seller_follower_ids( $seller_id );

			if ( ! empty( $search ) ) {
				$shop_follower_ids = get_users(
					array(
						'include'        => $shop_follower_ids,
						'number'         => -1,
						'search'         => "*{$search}*",
						'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
						'fields'         => 'ID',
					)
				);
			}

			foreach ( $shop_follower_ids as $follower_id ) {
				$follower = get_user_by( 'ID', $follower_id );

				if ( $follower instanceof \WP_User ) {
					$shop_followers[ $follower_id ] = array(
						'follower_id'    => $follower_id,
						'follower_name'  => $wkmarketplace->wkmp_get_user_display_name( $follower_id ),
						'follower_email' => $follower->user_email,
					);
				}
			}

			return apply_filters( 'wkmp_get_seller_followers_data', $shop_followers, $seller_id );
		}

		/**
		 * Display shop follower.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_display_shop_follower( $seller_id ) {
			$this->seller_id = empty( $this->seller_id ) ? $seller_id : $this->seller_id;

			$nonce = \WK_Caching::wk_get_request_data( 'wkmp-delete-followers-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-delete-followers-nonce-action' ) ) {
				$selected = empty( $_POST['selected'] ) ? array() : wc_clean( wp_unslash( $_POST['selected'] ) );

				if ( ! empty( $selected ) ) {
					$this->wkmp_delete_followers( $selected );
				}
			}

			$nonce = \WK_Caching::wk_get_request_data( 'wkmp-sendmail-followers-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-sendmail-followers-nonce-action' ) ) {
				$customer_ids = empty( $_POST['customer_ids'] ) ? array() : wc_clean( wp_unslash( $_POST['customer_ids'] ) );

				$subject  = empty( $_POST['subject'] ) ? '' : wc_clean( wp_unslash( $_POST['subject'] ) );
				$feedback = empty( $_POST['message'] ) ? '' : wc_clean( wp_unslash( $_POST['message'] ) );

				foreach ( $customer_ids as $user_id ) {
					$user_info = get_userdata( $user_id );
					$to        = $user_info->user_email;
					do_action( 'wkmp_seller_to_shop_followers', $to, $subject, $feedback );
				}
				wc_print_notice( esc_html__( 'Notification mail has been send successfully', 'wk-marketplace' ), 'success' );
			}

			$followers = $this->wkmp_get_seller_followers_data( $this->seller_id );
			?>
			<div class="wkmp-table-action-wrap">
				<div class="wkmp-action-section left wkmp-text-left">

					<button type="button" class="button wkmp-bulk-delete" data-form_id="#wkmp-followers-list" title="<?php esc_attr_e( 'Delete Follower', 'wk-marketplace' ); ?>">
						<span class="dashicons dashicons-trash"></span></button>&nbsp;&nbsp;

					<button type="button" class="button wkmp-send-notification" id="wkmp-send-notification"><?php esc_html_e( 'Send Notification', 'wk-marketplace' ); ?></button>
				</div>
			</div>

			<form action="" method="post" enctype="multipart/form-data" id="wkmp-followers-list" style="margin-bottom:unset;">
				<div class="wkmp-table-responsive">
					<table class="table table-bordered table-hover wkmp-shop-follower-table">
						<thead>
						<tr>
							<td><input type="checkbox" id="wkmp-checked-all"></td>
							<td><?php esc_html_e( 'Customer Name', 'wk-marketplace' ); ?></td>
							<td><?php esc_html_e( 'Customer Email', 'wk-marketplace' ); ?></td>
							<td><?php esc_html_e( 'Action', 'wk-marketplace' ); ?></td>
						</tr>
						</thead>
						<tbody>
						<?php if ( count( $followers ) > 0 ) { ?>
							<?php
							foreach ( $followers as $follower_id => $follower ) {
								$follower_name = trim( $follower['follower_name'] );
								?>
								<tr>
									<td><input type="checkbox" name="selected[]" value="<?php echo esc_attr( $follower_id ); ?>"/></td>
									<td><?php echo empty( $follower_name ) ? esc_html__( 'NA', 'wk-marketplace' ) : esc_html( $follower_name ); ?></td>
									<td><?php echo esc_html( $follower['follower_email'] ); ?></td>
									<td>
										<a href="javascript:void(0)" class="button wkmp-trash-shop-follower"><span class="dashicons dashicons-trash"></span></a>
									</td>
								</tr>
							<?php } ?>
						<?php } else { ?>
							<tr>
								<td colspan="4" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<?php wp_nonce_field( 'wkmp-delete-followers-nonce-action', 'wkmp-delete-followers-nonce' ); ?>
			</form>

			<div id="wkmp-seller-send-notification" class="wkmp-popup-modal">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"><?php esc_html_e( 'Confirmation', 'wk-marketplace' ); ?></h4>
					</div>
					<div class="modal-body wkmp-form-wrap">
						<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-sendmail-form">
							<div class="form-group">
								<label for="wkmp-subject"><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
								<input class="form-control" type="text" name="subject" placeholder="Subject" id="wkmp-subject" value="">
								<div id="wkmp-subject-error" class="wkmp-text-danger"></div>
							</div>
							<div class="form-group">
								<label for="wkmp-message"><?php esc_html_e( 'Message', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
								<textarea rows="4" name="message" id="wkmp-message" placeholder="Message"></textarea>
								<div id="wkmp-message-error" class="wkmp-text-danger"></div>
							</div>
							<?php wp_nonce_field( 'wkmp-sendmail-followers-nonce-action', 'wkmp-sendmail-followers-nonce' ); ?>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
						<button id="wkmp-submit-ask-form" type="submit" form="wkmp-seller-sendmail-form" class="button"><?php esc_html_e( 'Send Mail', 'wk-marketplace' ); ?></button>
					</div>
				</div>
			</div>

			<?php
		}
	}
}
