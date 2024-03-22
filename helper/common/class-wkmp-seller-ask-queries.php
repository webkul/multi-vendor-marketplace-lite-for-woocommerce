<?php
/**
 * Seller ask queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Ask_Queries' ) ) {
	/**
	 * Seller ask query related queries class
	 */
	class WKMP_Seller_Ask_Queries {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * DB Variable
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
		 * Save seller ask query info into database.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data Query Data.
		 *
		 * @return int Ask query id
		 */
		public function wkmp_seller_ask_query_save( $seller_id, $data ) {
			$wpdb_obj = $this->wpdb;

			$insert_query = $wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpseller_asktoadmin',
				array(
					'seller_id'   => (int) $seller_id,
					'subject'     => $data['subject'],
					'message'     => $data['message'],
					'create_date' => gmdate( 'Y-m-d H:i:s' ),
				),
				array( '%d', '%s', '%s', '%s' )
			);

			$insert_id   = $wpdb_obj->insert_id;
			$seller_info = get_userdata( $seller_id );

			do_action( 'wpmp_save_front_value', $seller_id, $data, $insert_id );
			do_action( 'wkmp_ask_to_admin', $seller_info->user_email, $data['subject'], $data['message'] );

			return $insert_query ? $wpdb_obj->insert_id : false;
		}

		/**
		 * Get all seller ask queries
		 *
		 * @param array $data Filter data.
		 *
		 * @return array $queries seller queries.
		 */
		public function wkmp_get_all_seller_queries( $data = array() ) {
			$wpdb_obj  = $this->wpdb;
			$sql_query = "SELECT * FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE id >= 1";

			if ( ! empty( $data['search'] ) ) {
				$sql_query .= $wpdb_obj->prepare( ' AND subject LIKE %s', '%' . esc_sql( $data['search'] ) . '%' );
			}

			if ( ! empty( $data['seller_id'] ) ) {
				$sql_query .= $wpdb_obj->prepare( ' AND seller_id = %d', esc_sql( $data['seller_id'] ) );
			}

			$orderby = empty( $data['orderby'] ) ? 'subject' : $data['orderby']; // If no sort, default to date.
			$order   = empty( $data['order'] ) ? 'desc' : $data['order']; // If no order, default to asc.
			$orderby = ( 'subject' === $orderby ) ? $orderby : 'create_date';

			$sql_query .= $wpdb_obj->prepare( ' ORDER BY %1s %2s', esc_sql( $orderby ), esc_sql( $order ) );

			if ( ! empty( $data['limit'] ) ) {
				$offset     = empty( $data['offset'] ) ? 0 : intval( $data['offset'] );
				$sql_query .= $wpdb_obj->prepare( ' LIMIT %d, %d', $offset, $data['limit'] );
			}

			$queries = $wpdb_obj->get_results( $sql_query, ARRAY_A );

			return apply_filters( 'wkmp_get_all_seller_queries', $queries );
		}

		/**
		 * Get total count seller ask queries
		 *
		 * @param array $data Filter data.
		 *
		 * @return int $total seller queries.
		 */
		public function wkmp_get_total_seller_queries( $data = array() ) {
			$wpdb_obj = $this->wpdb;

			$sql_query = "SELECT COUNT(*) FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE id >= 1";

			if ( ! empty( $data['search'] ) ) {
				$sql_query .= $wpdb_obj->prepare( ' AND subject LIKE %s', '%' . esc_sql( $data['search'] ) . '%' );
			}

			if ( ! empty( $data['seller_id'] ) ) {
				$sql_query .= $wpdb_obj->prepare( ' AND seller_id = %d', esc_sql( $data['seller_id'] ) );
			}

			$total = $wpdb_obj->get_var( $sql_query );

			return apply_filters( 'wkmp_get_total_seller_queries', $total );
		}

		/**
		 * Get all seller ask queries.
		 *
		 * @param int $id Query Id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_query_info_by_id( $id ) {
			$wpdb_obj = $this->wpdb;
			$query    = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE id = %d", esc_attr( $id ) ) );

			return apply_filters( 'wkmp_get_query_info_by_id', $query );
		}

		/**
		 * Delete seller.
		 *
		 * @param int $id Id.
		 */
		public function wkmp_delete_seller_query( $id ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->delete(
				"{$wpdb_obj->prefix}mpseller_asktoadmin",
				array(
					'id' => esc_attr( $id ),
				),
				array( '%d' )
			);
		}

		/**
		 * Update seller.
		 *
		 * @param int $id Id.
		 */
		public function wkmp_update_seller_reply_status( $id ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpseller_asktoadmin_meta',
				array(
					'id'         => esc_attr( $id ),
					'meta_key'   => 'reply_status',
					'meta_value' => 'replied',
				)
			);
		}

		/**
		 * Check seller reply.
		 *
		 * @param Int $id Id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_check_seller_replied_by_admin( $id ) {
			$wpdb_obj = $this->wpdb;
			$query    = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mpseller_asktoadmin_meta WHERE meta_key = %s AND id = %d", esc_attr( 'reply_status' ), esc_attr( $id ) ) );
			$return   = 'replied' === $query ? $query : false;

			return apply_filters( 'wkmp_check_seller_replied_by_admin', $return );
		}
	}
}
