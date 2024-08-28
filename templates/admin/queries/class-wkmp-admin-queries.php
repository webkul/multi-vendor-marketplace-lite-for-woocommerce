<?php
/**
 * Seller Order List In Admin Dashboard.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Queries;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WK_Caching;
use WkMarketplace\Helper\Common;
use WkMarketplace\Templates\Admin as AdminTemplates;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Queries' ) ) {
	/**
	 * Seller List Class.
	 *
	 * Class WKMP_Admin_Queries
	 *
	 * @package WkMarketplace\Templates\Admin\Queries
	 */
	class WKMP_Admin_Queries extends \WP_List_Table {
		/**
		 * Query DB Object.
		 *
		 * @var Common\WKMP_Seller_Ask_Queries
		 */
		private $query_db_object;

		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Queries constructor.
		 */
		public function __construct() {
			$this->query_db_object = Common\WKMP_Seller_Ask_Queries::get_instance();

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Queries', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Queries', 'wk-marketplace' ),
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
		 * Add thickbox.
		 *
		 * @param string $query Query.
		 */
		public function thickbox_content( $query ) {
			?>
			<div id="meta-box-<?php echo esc_attr( $query['id'] ); ?>" class="meta-bx wkmp-hide">
				<h2><?php esc_html_e( 'Reply to ', 'wk-marketplace' ); ?><?php echo esc_html( $query['seller_name'] ); ?> </h2>
				<table class="wkmp-ask-query-reply-modal">
					<tr>
						<td><label><h4><b> <?php esc_html_e( 'Subject', 'wk-marketplace' ); ?> </b></h4></label></td>
						<td colspan="2"><span> <?php echo esc_html( $query['subject'] ); ?> </span></td>
					</tr>
					<tr>
						<td><label><h4><b> <?php esc_html_e( 'Query', 'wk-marketplace' ); ?> </b></h4></label></td>
						<td colspan="2"><span> <?php echo esc_html( $query['message'] ); ?> </span></td>
					</tr>
				</table>
				<div class="reply-mes">
					<label><h3> <?php esc_html_e( 'Reply Message', 'wk-marketplace' ); ?> </h3></label>
					<textarea name="reply" class="wkmp-admin_msg_to_seller" rows="5" cols="60"></textarea>
				</div>
				<button class="button-primary seller-query-revert" data-qid="<?php echo esc_attr( intval( $query['id'] ) ); ?>"><?php esc_html_e( 'Send', 'wk-marketplace' ); ?></button>
			</div>
			<?php
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			global $wkmarketplace;

			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'wkmp_seller_per_page', 20 );
			$current_page = $this->get_pagenum();

			$orderby    = \WK_Caching::wk_get_request_data( 'orderby', array( 'default' => 'seller' ) );
			$sort_order = \WK_Caching::wk_get_request_data( 'order', array( 'default' => 'desc' ) );

			$filter_data = apply_filters(
				'wkmp_admin_queries_filter_data',
				array(
					'offset'  => ( $current_page - 1 ) * $per_page,
					'limit'   => $per_page,
					'orderby' => $orderby,
					'order'   => $sort_order,
				)
			);

			$data    = array();
			$queries = $this->query_db_object->wkmp_get_all_seller_queries( $filter_data );

			foreach ( $queries as $query ) {
				$seller_id = empty( $query['seller_id'] ) ? 0 : intval( $query['seller_id'] );

				if ( empty( $seller_id ) ) {
					continue;
				}

				$query['seller_name'] = $wkmarketplace->wkmp_get_user_display_name( $seller_id );

				if ( $this->query_db_object->wkmp_check_seller_replied_by_admin( $query['id'] ) ) {
					$action = '<span><b>' . esc_html__( 'Replied', 'wk-marketplace' ) . '<b></span>';
				} else {
					add_thickbox();
					$this->thickbox_content( $query );
					$action = '<a href="#TB_inline?width=600&height=400&inlineId=meta-box-' . $query['id'] . '" title="' . __( 'Reply', 'wk-marketplace' ) . '" class="thickbox button button-primary">' . __( 'Reply', 'wk-marketplace' ) . '</a>';
				}

				$data[] = array(
					'id'           => $query['id'],
					'seller'       => $query['seller_name'],
					'date_created' => $query['create_date'],
					'subject'      => $query['subject'],
					'message'      => $query['message'],
					'action'       => $action,
				);
			}

			$total_items = $this->query_db_object->wkmp_get_total_seller_queries( $filter_data );

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
		 * @return array, the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'           => '<input type="checkbox" />',
				'seller'       => esc_html__( 'Seller', 'wk-marketplace' ),
				'date_created' => esc_html__( 'Date Created', 'wk-marketplace' ),
				'subject'      => esc_html__( 'Subject', 'wk-marketplace' ),
				'message'      => esc_html__( 'Message', 'wk-marketplace' ),
				'action'       => esc_html__( 'Action', 'wk-marketplace' ),
			);
		}

		/**
		 * Column default.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'seller':
				case 'date_created':
				case 'subject':
				case 'message':
				case 'action':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'date_created' => array( 'date_created', true ),
				'subject'      => array( 'subject', true ),
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
		 * @param array|object $item Items.
		 *
		 * @return string|void
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="customer_%d" name="ids[]" value="%d" />', $item['id'], $item['id'] );
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => esc_html__( 'Delete', 'wk-marketplace' ),
			);

			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === esc_attr( 'delete' ) ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );
				$ids     = \WK_Caching::wk_get_request_data( 'ids', array( 'flag' => 'array' ) );
				$success = 404;

				if ( ! empty( $ids ) && is_iterable( $ids ) ) {
					foreach ( $ids as $id ) {
						$this->query_db_object->wkmp_delete_seller_query( $id );
					}
					$success = 1;
				}
				$transient = get_transient( 'wkmp_deleted_queries_transient' );

				if ( empty( $transient ) ) {
					set_transient( 'wkmp_deleted_queries_transient', array( 'success' => $success ), 8 );
				}

				$page_name = \WK_Caching::wk_get_request_data( 'page' );
				$url       = 'admin.php?page=' . $page_name;

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

			$placeholder = esc_attr__( 'Search By Subject', 'wk-marketplace' );

			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$template_functions->wkmp_show_restricted_search_box( $text, $input_id, $placeholder );
		}
	}
}
