<?php
/**
 * Seller ask queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Transaction' ) ) {

	/**
	 * Seller ask query related queries class
	 */
	class WKMP_Transaction {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
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
		 * * Generate Transaction.
		 *
		 * @param int    $seller_id Seller ID.
		 * @param int    $order_id Order ID.
		 * @param int    $amount Order Item ID.
		 * @param string $transaction_id Transaction id.
		 *
		 * @return bool|int|string
		 */
		public function wkmp_generate_transaction( $seller_id, $order_id, $amount, $transaction_id = '' ) {
			$response = '';

			if ( empty( $transaction_id ) ) {
				$order          = wc_get_order( $order_id );
				$order_password = $order->get_order_key();
				$replace        = 'tr-' . esc_attr( $seller_id );
				if ( ! empty( $order_password ) ) {
					$transaction_id = str_replace( 'order_', $replace, $order_password );
				}
			}

			if ( ! empty( $transaction_id ) ) {
				$response = $this->wpdb->insert(
					"{$this->wpdb->prefix}seller_transaction",
					array(
						'transaction_id'   => $transaction_id,
						'order_id'         => maybe_serialize( $order_id ),
						'seller_id'        => $seller_id,
						'amount'           => $amount,
						'type'             => 'manual',
						'method'           => 'manual',
						'transaction_date' => gmdate( 'Y-m-d H:i:s' ),
					),
					array( '%s', '%d', '%d', '%f', '%s', '%s', '%s' )
				);
			}

			return $response;
		}

		/**
		 * Get Seller Transaction.
		 *
		 * @param int $data Filter data.
		 * @param int $seller_id Seller ID.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_transactions( $data, $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}seller_transaction WHERE seller_id=%d", $seller_id );

			if ( ! empty( $data['filter_transaction_id'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_id LIKE %s', '%' . esc_sql( $data['filter_transaction_id'] ) . '%' );
			}

			$orderby = empty( $data['orderby'] ) ? 'transaction_id' : $data['orderby']; // If no sort, default to date.
			$order   = empty( $data['order'] ) ? 'desc' : $data['order']; // If no order, default to asc.

			$orderby = ( 'subject' === $orderby ) ? $orderby : 'transaction_date';

			$sql .= $wpdb_obj->prepare( ' ORDER BY %1s %2s', esc_sql( $orderby ), esc_sql( $order ) );

			if ( ! empty( $data['limit'] ) ) {
				$offset = empty( $data['offset'] ) ? 0 : intval( $data['offset'] );
				$sql   .= $wpdb_obj->prepare( ' LIMIT %d, %d', $offset, $data['limit'] );
			}

			$transactions = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_seller_transactions', $transactions, $seller_id );
		}

		/**
		 * Get Seller Transaction.
		 *
		 * @param array $data Filter data.
		 * @param int   $seller_id Seller id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_seller_total_transactions( $data, $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$sql      = $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$this->wpdb->prefix}seller_transaction WHERE seller_id =%d", $seller_id );

			if ( ! empty( $data['filter_transaction_id'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND transaction_id LIKE %s', '%' . esc_sql( $data['filter_transaction_id'] ) . '%' );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_seller_transactions', $total, $seller_id );
		}

		/**
		 * Get Transaction Detail.
		 *
		 * @param int $id Id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_transaction_details_by_id( $id ) {
			$wpdb_obj = $this->wpdb;
			$result   = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}seller_transaction WHERE id = %d", esc_attr( $id ) ) );

			return apply_filters( 'wkmp_get_transaction_details_by_id', $result, $id );
		}
	}
}
