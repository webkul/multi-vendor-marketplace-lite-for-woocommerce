<?php
/**
 * Seller Order List In Admin Dashboard.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Feedback;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Common;
use WkMarketplace\Templates\Admin as AdminTemplates;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Feedback' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Admin_Feedback extends \WP_List_Table {
		/**
		 * Feedback DB Object.
		 *
		 * @var Common\WKMP_Seller_Feedback
		 */
		private $feedback_db_obj;

		/**
		 * Marketplace class Object.
		 *
		 * @var $marketplace \Marketplace
		 */
		private $marketplace;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Feedback constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->feedback_db_obj = Common\WKMP_Seller_Feedback::get_instance();
			$this->marketplace     = $wkmarketplace;

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Feedback', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Feedback', 'wk-marketplace' ),
					'ajax'     => false,
				)
			);
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
		 * Prepare items.
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'wkmp_seller_per_page', 20 );
			$current_page = $this->get_pagenum();

			$orderby    = \WK_Caching::wk_get_request_data( 'orderby', array( 'default' => 'review_time' ) );
			$sort_order = \WK_Caching::wk_get_request_data( 'order', array( 'default' => 'desc' ) );

			$filter_data = apply_filters(
				'wkmp_feedback_rating_filter_data',
				array(
					'start'   => ( $current_page - 1 ) * $per_page,
					'limit'   => $per_page,
					'orderby' => $orderby,
					'order'   => $sort_order,
				)
			);

			$feedbacks = $this->feedback_db_obj->wkmp_get_seller_feedbacks( $filter_data );
			$data      = array();

			foreach ( $feedbacks as $feedback ) {
				$seller_info = $this->marketplace->wkmp_get_seller_info( $feedback->seller_id );
				$shop_name   = empty( $seller_info->shop_name ) ? esc_html__( '(Deleted Shop)', 'wk-marketplace' ) : $seller_info->shop_name;

				$url       = 'admin.php?page=wk-marketplace&tab-action=manage&seller-id=' . $feedback->seller_id;
				$shop_name = sprintf( '<a href="%s"><strong>%s(#%d)</strong></a>', esc_url( admin_url( $url ) ), $shop_name, $feedback->seller_id );

				$status = empty( $feedback->status ) ? esc_html__( 'Pending', 'wk-marketplace' ) : esc_html__( 'Approved', 'wk-marketplace' );
				$status = ( 2 === intval( $feedback->status ) ) ? esc_html__( 'Disapproved', 'wk-marketplace' ) : $status;

				$given_by = $this->marketplace->wkmp_get_user_display_name( $feedback->user_id );

				$data[] = array(
					'id'             => $feedback->ID,
					'shop_name'      => $shop_name,
					'value_rating'   => $feedback->value_r . '/5',
					'price_rating'   => $feedback->price_r . '/5',
					'quality_rating' => $feedback->quality_r . '/5',
					'summary'        => $feedback->review_summary,
					'description'    => $feedback->review_desc,
					'status'         => $status,
					'given_by'       => $given_by,
					'review_time'    => $feedback->review_time,
				);
			}

			$total_items = $this->feedback_db_obj->wkmp_get_seller_total_feedbacks( $filter_data );

			$total_pages = ceil( $total_items / $per_page );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$this->items = $data;
		}

		/**
		 * Define the columns that are going to be used in the table
		 *
		 * @return array the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'             => '<input type="checkbox" />',
				'shop_name'      => esc_html__( 'Shop Name', 'wk-marketplace' ),
				'given_by'       => esc_html__( 'Given By', 'wk-marketplace' ),
				'value_rating'   => esc_html__( 'Value Rating', 'wk-marketplace' ),
				'price_rating'   => esc_html__( 'Price Rating', 'wk-marketplace' ),
				'quality_rating' => esc_html__( 'Quality Rating', 'wk-marketplace' ),
				'summary'        => esc_html__( 'Summary', 'wk-marketplace' ),
				'description'    => esc_html__( 'Description', 'wk-marketplace' ),
				'status'         => esc_html__( 'Status', 'wk-marketplace' ),
				'review_time'    => esc_html__( 'Date Created', 'wk-marketplace' ),
			);
		}

		/**
		 * Default columns values.
		 *
		 * @param array|object $item Item.
		 * @param string       $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'shop_name':
				case 'given_by':
				case 'value_rating':
				case 'price_rating':
				case 'quality_rating':
				case 'summary':
				case 'description':
				case 'status':
				case 'review_time':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'status'      => array( 'status', true ),
				'review_time' => array( 'review_time', true ),
			);
		}

		/**
		 * Get hidden columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Column callback.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="feedback_%d" name="ids[]" value="%d" />', $item['id'], $item['id'] );
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			return array(
				'approve'    => esc_html__( 'Approve', 'wk-marketplace' ),
				'disapprove' => esc_html__( 'Disapprove', 'wk-marketplace' ),
				'delete'     => esc_html__( 'Delete', 'wk-marketplace' ),
			);
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				$delete  = false;
				$success = 404;

				$ids = \WK_Caching::wk_get_request_data( 'ids', array( 'flag' => 'array' ) );

				if ( ! empty( $ids ) && is_iterable( $ids ) ) {
					if ( $this->current_action() === esc_attr( 'approve' ) ) {
						$status  = 1;
						$success = 1;
					} elseif ( $this->current_action() === esc_attr( 'disapprove' ) ) {
						$status  = 2;
						$success = 2;
					} elseif ( $this->current_action() === esc_attr( 'delete' ) ) {
						$delete  = true;
						$success = 3;
					}

					foreach ( $ids as $id ) {
						if ( $delete ) {
							$this->feedback_db_obj->wkmp_delete_seller_feedback( $id );
						} else {
							$this->feedback_db_obj->wkmp_update_feedback_status( $id, $status );
						}
					}
				}

				$page_name = \WK_Caching::wk_get_request_data( 'page' );
				$url       = 'admin.php?page=' . $page_name . '&success=' . $success;

				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
		}

		/**
		 * Displays restricted search box to allow pro feature.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			$search = \WK_Caching::wk_get_request_data( 's' );

			if ( empty( $search ) && ! $this->has_items() ) {
				return;
			}
			$placeholder = esc_attr__( 'Search by Summary', 'wk-marketplace' );

			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$template_functions->wkmp_show_restricted_search_box( $text, $input_id, $placeholder );
		}
	}
}
