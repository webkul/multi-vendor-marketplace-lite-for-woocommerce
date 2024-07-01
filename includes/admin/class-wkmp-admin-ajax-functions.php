<?php
/**
 * Admin End Hooks
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

use WkMarketplace\Helper\Admin;
use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Admin_Ajax_Functions' ) ) {
	/**
	 * Admin Functions class
	 */
	class WKMP_Admin_Ajax_Functions {
		/**
		 * Order DB class object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data
		 */
		private $order_db_obj;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Admin function constructor.
		 *
		 * WKMP_Admin_Ajax_Functions constructor.
		 */
		public function __construct() {
			$this->order_db_obj = Admin\WKMP_Seller_Order_Data::get_instance();
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
		 * Replied to seller.
		 */
		public function wkmp_admin_replied_to_seller() {
			$json = array();

			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_marketplace' );

			if ( ! check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) || ! current_user_can( $capability ) ) {
				$json['error']   = true;
				$json['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $json );
				die();
			}

			$reply_message = empty( $_POST['reply_message'] ) ? '' : wc_clean( wp_unslash( $_POST['reply_message'] ) );
			$query_id      = empty( $_POST['qid'] ) ? '' : wc_clean( wp_unslash( $_POST['qid'] ) );

			if ( ! empty( $reply_message ) && ! empty( $query_id ) ) {
				$query_db_obj = Common\WKMP_Seller_Ask_Queries::get_instance();
				$query_info   = $query_db_obj->wkmp_get_query_info_by_id( $query_id );
				$seller_email = get_userdata( $query_info->seller_id )->user_email;

				$query_data = array(
					'query_info'    => $query_info,
					'reply_message' => $reply_message,
				);

				if ( $seller_email && ! empty( $reply_message ) ) {
					do_action( 'wkmp_seller_query_replied', $seller_email, $query_id, $query_data );
					$query_db_obj->wkmp_update_seller_reply_status( $query_id );
					$json['success'] = true;
					$json['message'] = esc_html__( 'Replied mail sent to the seller.', 'wk-marketplace' );
				} else {
					$json['success'] = false;
					$json['message'] = esc_html__( 'Oops, Unable to send mail to the seller.', 'wk-marketplace' );
				}
			}

			wp_send_json( $json );
			die();
		}

		/**
		 * Check my shop values.
		 */
		public function wkmp_check_slug_for_seller_shop() {
			if ( check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) && current_user_can( 'manage_options' ) ) {
				$url_slug = empty( $_POST['shop_slug'] ) ? '' : wc_clean( wp_unslash( $_POST['shop_slug'] ) );
				$check    = false;
				$user     = get_user_by( 'slug', $url_slug );

				if ( empty( $url_slug ) ) {
					$check = false;
				} elseif ( preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $url_slug ) ) {
					$check = false;
				} elseif ( ctype_space( $url_slug ) ) {
					$check = false;
				} elseif ( $user instanceof \WP_User ) {
					$check = 2;
				} else {
					$check = true;
				}
				echo esc_html( $check );
				die;
			}
		}

		/**
		 * Change seller dashboard settings.
		 */
		public function wkmp_change_seller_to_frontend_dashboard() {
			if ( check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) && current_user_can( 'wk_marketplace_seller' ) ) {
				$data      = array();
				$change_to = empty( $_POST['change_to'] ) ? '' : wc_clean( wp_unslash( $_POST['change_to'] ) );

				if ( ! empty( $change_to ) ) {
					$c_user_id    = get_current_user_id();
					$current_dash = get_user_meta( $c_user_id, 'wkmp_seller_backend_dashboard', true );
					if ( 'front_dashboard' === $change_to ) {
						if ( $current_dash ) {
							update_user_meta( $c_user_id, 'wkmp_seller_backend_dashboard', null );
							$data['redirect'] = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/' . get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' );
						}
					} elseif ( 'backend_dashboard' === $change_to ) {
						update_user_meta( $c_user_id, 'wkmp_seller_backend_dashboard', true );
						$data['redirect'] = admin_url( 'admin.php?page=seller' );
					}
				}
				wp_send_json( $data );
			}
		}

		/**
		 * Updating order status.
		 */
		public function wkmp_seller_order_status_update() {
			$result = array(
				'success' => false,
				'message' => esc_html__( 'There is some error!! Please try again later!!', 'wk-marketplace' ),
			);

			if ( ! check_ajax_referer( 'wkmp-admin-nonce', 'wkmp_nonce', false ) || ! current_user_can( 'manage_options' ) ) {
				$result['error']   = true;
				$result['message'] = esc_html__( 'Security check failed!', 'wk-marketplace' );
				wp_send_json( $result );
				die();
			}

			$action_ids = empty( $_POST['action_data'] ) ? '' : wc_clean( wp_unslash( $_POST['action_data'] ) );

			if ( ! empty( $action_ids ) ) {
				$ids           = explode( '-', $action_ids );
				$order_id      = $ids[0];
				$seller_id     = $ids[1];
				$action        = count( $ids ) > 2 ? $ids[2] : 'pay'; // If Approve/Disapprove option was selected from the dropdown.
				$seller_amount = count( $ids ) > 3 ? $ids[3] : 0; // If pay option was selected from the dropdown.

				if ( $order_id && $seller_id ) {
					$result['seller_id']     = $seller_id;
					$result['order_id']      = $order_id;
					$result['action']        = $action;
					$result['seller_amount'] = $seller_amount;

					$paid_status = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

					$this->order_db_obj->wkmp_update_order_status_on_seller( $order_id, $seller_id, $action );

					$action_text     = ( 'pay' === $action ) ? esc_html__( 'Paid', 'wk-marketplace' ) : ( 'approve' === $action ? esc_html__( 'Approved', 'wk-marketplace' ) : esc_html__( 'Disapproved', 'wk-marketplace' ) );
					$message         = ( 'pay' === $action ) ? sprintf( /* Translators: %d Order id. */ esc_html__( 'Payment has been successfully done for order id: %d', 'wk-marketplace' ), esc_html( $order_id ) ) : sprintf( /* Translators: %d Order id. */ esc_html__( 'Order status for order id: %d has been successfully updated to disapproved.', 'wk-marketplace' ), esc_attr( $order_id ) );
					$new_action_html = '<button class="button button-primary" class="admin-order-pay" disabled>' . esc_html( $action_text ) . '</button>';

					if ( 'approve' === $action && $seller_amount > 0 ) {
						$new_action_html = '<a href="javascript:void(0)" data-id="' . esc_attr( $order_id ) . '-' . esc_attr( $seller_id ) . '" class="page-title-action admin-order-pay">' . __( 'Pay', 'wk-marketplace' ) . '</a>';
						$message         = sprintf( /* Translators: %d Order id. */ esc_html__( 'Order status for order id: %d has been successfully updated to approved.', 'wk-marketplace' ), esc_attr( $order_id ) );
					}

					$result['success']         = true;
					$result['message']         = $message;
					$result['new_action_html'] = $new_action_html;

					if ( 'approve' === $action || ( 'pay' === $action && 'approved' !== $paid_status ) ) {
						do_action( 'wkmp_seller_order_paid', $result );
					}
				}
			}

			wp_send_json( $result );
		}
	}
}
