<?php
/**
 * Seller Feedback DB class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Feedback' ) ) {

	/**
	 * Seller add feedback related queries class
	 */
	class WKMP_Seller_Feedback {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * DB Variable.
		 *
		 * @var object
		 */
		protected $wpdb;

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
		 * Save Seller feedback
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		public function wkmp_insert_seller_feedback( $data ) {
			$wpdb_obj            = $this->wpdb;
			$data['review_time'] = gmdate( 'Y-m-d H:i:s' );

			$wpdb_obj->insert( $wpdb_obj->prefix . 'mpfeedback', $data );

			do_action( 'wkmp_save_seller_review_notification', $data, $wpdb_obj->insert_id );
		}

		/**
		 * Get Seller feedback.
		 *
		 * @param array $filter_data Filter data.
		 *
		 * @return array $feedbacks
		 */
		public function wkmp_get_seller_feedbacks( $filter_data ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT * FROM {$wpdb_obj->prefix}mpfeedback WHERE ID > 0";

			if ( ! empty( $filter_data['search'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND review_summary LIKE %s', '%' . esc_attr( $filter_data['search'] ) . '%' );
			}

			if ( ! empty( $filter_data['filter_seller_id'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND seller_id = %d', esc_attr( $filter_data['filter_seller_id'] ) );
			}

			if ( ! empty( $filter_data['filter_user_id'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND user_id = %d', esc_attr( $filter_data['filter_user_id'] ) );
			}

			if ( ! empty( $filter_data['status'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND status = %d', esc_attr( $filter_data['status'] ) );
			}

			$orderby = empty( $filter_data['orderby'] ) ? 'review_time' : $filter_data['orderby']; // If no sort, default to date.
			$order   = empty( $filter_data['order'] ) ? 'desc' : $filter_data['order']; // If no order, default to asc.

			$sql .= $wpdb_obj->prepare( ' ORDER BY %1s %2s', esc_sql( $orderby ), esc_sql( $order ) );

			if ( isset( $filter_data['start'] ) && isset( $filter_data['limit'] ) ) {
				$sql .= $wpdb_obj->prepare( ' LIMIT %d, %d', esc_attr( $filter_data['start'] ), esc_attr( $filter_data['limit'] ) );
			}

			$feedbacks = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_seller_feedbacks', $feedbacks );
		}

		/**
		 * Get total Seller feedback
		 *
		 * @param array $filter_data Filter data.
		 *
		 * @return int $total
		 */
		public function wkmp_get_seller_total_feedbacks( $filter_data ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT COUNT(*) FROM {$wpdb_obj->prefix}mpfeedback WHERE ID > 0";

			if ( ! empty( $filter_data['search'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND review_summary LIKE %s', '%' . esc_attr( $filter_data['search'] ) . '%' );
			}

			if ( isset( $filter_data['filter_seller_id'] ) && $filter_data['filter_seller_id'] ) {
				$sql .= $wpdb_obj->prepare( ' AND seller_id = %d', esc_attr( $filter_data['filter_seller_id'] ) );
			}

			if ( isset( $filter_data['filter_user_id'] ) && $filter_data['filter_user_id'] ) {
				$sql .= $wpdb_obj->prepare( ' AND user_id = %d', esc_attr( $filter_data['filter_user_id'] ) );
			}

			if ( isset( $filter_data['status'] ) && $filter_data['status'] ) {
				$sql .= $wpdb_obj->prepare( ' AND status = %d', esc_attr( $filter_data['status'] ) );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_seller_total_feedbacks', $total );
		}

		/**
		 * Delete seller feedback
		 *
		 * @param int $id Id.
		 *
		 * @return void
		 */
		public function wkmp_delete_seller_feedback( $id ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->delete(
				"{$wpdb_obj->prefix}mpfeedback",
				array(
					'ID' => esc_attr( $id ),
				),
				array( '%d' )
			);
		}

		/**
		 * Update seller feedback status.
		 *
		 * @param array $id Id.
		 * @param int   $status Status.
		 *
		 * @return void
		 */
		public function wkmp_update_feedback_status( $id, $status ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->update( $wpdb_obj->prefix . 'mpfeedback', array( 'status' => (int) $status ), array( 'ID' => $id ), array( '%d' ), array( '%d' ) );
		}
	}
}
