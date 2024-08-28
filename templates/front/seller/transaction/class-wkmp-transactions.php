<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Transaction;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Transaction' ) ) {
	/**
	 * Seller products class.
	 */
	class WKMP_Transactions {
		/**
		 * Transaction DB Object.
		 *
		 * @var Common\WKMP_Transaction
		 */
		private $transaction_db_obj;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Transactions constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->transaction_db_obj = Common\WKMP_Transaction::get_instance();
			$this->seller_id          = $seller_id;
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
		 * Marketplace Seller transaction view.
		 *
		 * @param int $id Transaction id.
		 * @param int $seller_id Seller id.
		 */
		public function wkmp_transaction_view( $id, $seller_id ) {
			$commission       = Common\WKMP_Commission::get_instance();
			$transaction_info = $this->transaction_db_obj->wkmp_get_transaction_details_by_id( $id );
			$this->seller_id  = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			if ( ! empty( $transaction_info ) ) {
				if ( intval( $transaction_info->seller_id ) === intval( $this->seller_id ) ) {
					$order_info = wc_get_order( $transaction_info->order_id );

					if ( $order_info instanceof \WC_Order ) {
						$currency = $order_info->get_currency();
					} else {
						$currency = get_woocommerce_currency();
						wc_print_notice( esc_html__( 'Associated order has been deleted.', 'wk-marketplace' ), 'notice' );
					}

					$columns = apply_filters(
						'wkmp_account_transactions_columns',
						array(
							'order_id'     => esc_html__( 'Order Id', 'wk-marketplace' ),
							'product_name' => esc_html__( 'Product Name', 'wk-marketplace' ),
							'quantity'     => esc_html__( 'Quantity', 'wk-marketplace' ),
							'price'        => esc_html__( 'Total Price', 'wk-marketplace' ),
							'commission'   => esc_html__( 'Commission', 'wk-marketplace' ),
							'subtotal'     => esc_html__( 'Subtotal', 'wk-marketplace' ),
						)
					);

					$seller_order_info = $commission->wkmp_get_seller_final_order_info( $transaction_info->order_id, $this->seller_id );
					$product_name      = '';

					foreach ( $seller_order_info['product'] as $pro_nme ) {
						if ( ! empty( $product_name ) ) {
							$product_name .= ' + ';
						}
						$product_name .= $pro_nme['title'];
					}

					require_once __DIR__ . '/wkmp-transaction-view.php';
				} else {
					?>
				<h1><?php esc_html_e( 'Cheating huh ???', 'wk-marketplace' ); ?></h1>
				<p><?php esc_html_e( 'Sorry, You are not allowed to access this transaction info.', 'wk-marketplace' ); ?></p>
					<?php
				}
			} else {
				?>
				<h1><?php esc_html_e( 'No transactions.', 'wk-marketplace' ); ?></h1>
				<p><?php esc_html_e( 'Sorry, there is no transaction info for this transaction id.', 'wk-marketplace' ); ?></p>
					<?php
			}
		}

		/**
		 * Marketplace Seller transaction list.
		 *
		 * @param int $seller_id Seller id.
		 * @param int $transact_id Transaction id.
		 * @param int $page_no Page no.
		 *
		 * @return void
		 */
		public function wkmp_transaction_list( $seller_id, $transact_id = 0, $page_no = 1 ) {
			global $wkmarketplace;

			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			if ( empty( $transact_id ) ) {
				$filter_name = '';
				$nonce       = \WK_Caching::wk_get_request_data( 'wkmp_transaction_search_nonce' );

				// Filter transactions.
				if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp_transaction_search_nonce_action' ) ) {
					$filter_name = \WK_Caching::wk_get_request_data( 'wkmp_search' );
				}

				$limit       = apply_filters( 'wkmp_front_per_page_transactions', 20 );
				$filter_data = array(
					'offset'                => ( $page_no - 1 ) * $limit,
					'limit'                 => $limit,
					'filter_transaction_id' => $filter_name,
				);

				$transact_info = $this->transaction_db_obj->wkmp_get_seller_transactions( $filter_data, $this->seller_id );
				$total         = $this->transaction_db_obj->wkmp_get_seller_total_transactions( $filter_data, $this->seller_id );

				$transactions = array();

				foreach ( $transact_info as $value ) {
					$order_info = wc_get_order( $value->order_id );
					$currency   = ( $order_info instanceof \WC_Order ) ? $order_info->get_currency() : get_woocommerce_currency();

					$transactions[] = array(
						'id'             => $value->id,
						'transaction_id' => $value->transaction_id,
						'order_id'       => $value->order_id,
						'amount'         => wc_price( apply_filters( 'wkmp_add_order_fee_to_total', $value->amount, $value->order_id ), array( 'currency' => $currency ) ),
						'type'           => ucfirst( $value->type ),
						'method'         => ucfirst( $value->method ),
						'created_on'     => $value->transaction_date,
						'view'           => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_transaction_endpoint', 'sellers-orders' ) . '/' . intval( $value->id ),
					);
				}

				$url        = get_permalink() . get_option( '_wkmp_transaction_endpoint', 'seller-transactions' );
				$pagination = $wkmarketplace->wkmp_get_pagination( $total, $page_no, $limit, $url );

				require_once __DIR__ . '/wkmp-transaction-list.php';
			} else {
				$this->wkmp_transaction_view( $transact_id, $this->seller_id );
			}
		}
	}
}
