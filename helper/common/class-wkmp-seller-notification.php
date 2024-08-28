<?php
/**
 * Seller Notification DB class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Notification' ) ) {
	/**
	 * Seller Notification related queries class
	 */
	class WKMP_Seller_Notification {
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
		 * Save notification
		 *
		 * @param array $data Data.
		 */
		public function wkmp_add_new_notification( $data ) {
			$this->wpdb->insert( $this->wpdb->prefix . 'mp_notifications', $data );
		}

		/**
		 * Update notification read status
		 *
		 * @param array $data Data.
		 */
		public function wkmp_update_notification_read_status( $data ) {
			if ( 0 === intval( $data['read_flag'] ) ) {
				$this->wpdb->update(
					$this->wpdb->prefix . 'mp_notifications',
					array(
						'read_flag' => 1,
					),
					array( 'id' => $data['id'] )
				);
			}
		}

		/**
		 * Get notification data
		 *
		 * @param string $type Notification type.
		 * @param string $keyword Keyword.
		 */
		public function wkmp_get_notification_data( $type, $keyword = '' ) {
			global $current_user;
			$wpdb_obj = $this->wpdb;

			$query = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mp_notifications WHERE type=%s", $type );

			if ( in_array( $keyword, array( 'processing', 'complete' ), true ) ) {
				$query .= $wpdb_obj->prepare( ' AND content LIKE %s', '%' . $keyword . '%' );
			}

			if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$query .= $wpdb_obj->prepare( ' AND author_id = %d', $current_user->ID );
			}

			$total_query = $wpdb_obj->get_results( $query, ARRAY_A );
			$total       = empty( $total_query ) ? 0 : count( $total_query );

			$page_no = \WK_Caching::wk_get_request_data(
				'n-page',
				array(
					'default' => 1,
					'filter'  => 'int',
				)
			);

			$page_no        = empty( $page_no ) ? 1 : abs( $page_no );
			$items_per_page = absint( get_option( 'posts_per_page', 10 ) );
			$offset         = ( $page_no * $items_per_page ) - $items_per_page;

			$query .= $wpdb_obj->prepare( ' ORDER BY id DESC LIMIT %d, %d', $offset, $items_per_page );

			$sql        = $wpdb_obj->get_results( $query, ARRAY_A );
			$total_page = intval( $total / $items_per_page );
			$pagination = '';

			if ( $total_page > 1 ) {
				$pagination = '<div class="mp-notification-pagination">' . paginate_links(
					array(
						'base'      => add_query_arg( 'n-page', '%#%' ),
						'format'    => '',
						'prev_text' => __( '&laquo;', 'wk-marketplace' ),
						'next_text' => __( '&raquo;', 'wk-marketplace' ),
						'total'     => $total_page,
						'current'   => $page_no,
					)
				) . '</div>';
			}

			return array(
				'data'       => $sql,
				'pagination' => $pagination,
			);
		}

		/**
		 * Get seller email by product id
		 *
		 * @param int $item_id Item Id.
		 */
		public function wkmp_get_author_email_by_item_id( $item_id ) {
			$author_id = get_post_field( 'post_author', $item_id );
			$author    = empty( $author_id ) ? '' : get_user_by( 'ID', $author_id );
			$email     = ( $author instanceof \WP_User ) ? $author->user_email : '';

			return apply_filters( 'wkmp_get_author_email_by_item_id', $email, $item_id );
		}

		/**
		 * Get notification count
		 *
		 * @param int $seller_id Seller id.
		 */
		public function wkmp_seller_panel_notification_count( $seller_id ) {
			$wpdb_obj    = $this->wpdb;
			$total_count = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(`id`) FROM {$wpdb_obj->prefix}mp_notifications WHERE read_flag = '0' AND author_id = %d", $seller_id ) );

			return apply_filters( 'wkmp_seller_panel_notification_count', $total_count, $seller_id );
		}

		/**
		 * Get seller notifications data
		 *
		 * @param string $type Notification type.
		 * @param int    $offset Offset.
		 * @param int    $limit Limit.
		 *
		 * @return array $data
		 */
		public function wkmp_get_seller_notification_data( $type, $offset, $limit ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = get_current_user_id();

			$data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mp_notifications WHERE type=%s AND author_id=%d ORDER BY id DESC LIMIT %d, %d", $type, $seller_id, $offset, $limit ), ARRAY_A );

			return apply_filters( 'wkmp_get_seller_notification_data', $data, $type );
		}

		/**
		 * Get seller notification count
		 *
		 * @param string $type Notification type.
		 *
		 * @return int $total
		 */
		public function wkmp_get_seller_notification_count( $type ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = get_current_user_id();

			$total = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$this->wpdb->prefix}mp_notifications WHERE type=%s AND author_id=%d", $type, $seller_id ) );

			return apply_filters( 'wkmp_get_seller_notification_count', $total, $type );
		}

		/**
		 * Get formatted notification content.
		 *
		 * @param string $db_content DB Content.
		 *
		 * @return string Actual content.
		 */
		public function wkmp_get_formatted_notification_content( $db_content ) {
			global $wkmarketplace;
			$content = ( 'wkmp_product_approved' === $db_content ) ? esc_html__( 'has been approved', 'wk-marketplace' ) : $db_content;
			$content = ( 'wkmp_out_of_stock' === $content ) ? esc_html__( 'Product is out of stock', 'wk-marketplace' ) : $content;
			$content = ( 'wkmp_order_processing' === $content ) ? esc_html__( 'Order status has been changed to <strong>Processing</strong>', 'wk-marketplace' ) : $content;
			$content = ( 'wkmp_order_complete' === $content ) ? esc_html__( 'Order status has been changed to <strong>Completed</strong>', 'wk-marketplace' ) : $content;
			$content = ( 'wkmp_new_review_received_' === $content ) ? esc_html__( 'A new review was received', 'wk-marketplace' ) : $content;

			if ( 0 === strpos( $content, 'wkmp_new_order_by_customer_id_' ) ) {
				$customer_id  = intval( $content );
				$display_name = $wkmarketplace->wkmp_get_user_display_name( $customer_id );

				$content = sprintf( /* Translators: %s: Author name. */ esc_html__( 'Order has been placed by %s', 'wk-marketplace' ), '<strong>' . esc_html( $display_name ) . '</strong>' );
			}

			if ( 0 === strpos( $content, 'wkmp_new_review_received_' ) ) {
				$content = esc_html__( 'A new review was received', 'wk-marketplace' );
			}

			if ( 0 === strpos( $content, 'wkmp_low_stock_' ) ) {
				$low_stock = intval( $content );
				$content   = sprintf( /* translators: %s stock quantity. */ esc_html__( 'Product is low in stock. There are %s left', 'wk-marketplace' ), esc_html( $low_stock ) );
			}

			return $content;
		}
	}
}
