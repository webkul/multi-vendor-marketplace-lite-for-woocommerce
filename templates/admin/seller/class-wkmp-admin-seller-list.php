<?php
/**
 * Seller List In Admin Dashboard.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Seller;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Admin as AdminHelper;
use WkMarketplace\Templates\Admin as AdminTemplates;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Seller_List' ) ) {
	/**
	 * Seller List Class.
	 *
	 * Class WKMP_Admin_Seller_List
	 *
	 * @package WkMarketplace\Templates\Admin\Seller
	 */
	class WKMP_Admin_Seller_List extends \WP_List_Table {
		/**
		 * Seller DB Object
		 *
		 * @var object
		 */
		protected $seller_db_obj;

		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Seller_List constructor.
		 */
		public function __construct() {
			$this->seller_db_obj = AdminHelper\WKMP_Seller_Data::get_instance();
			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller List', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Sellers List', 'wk-marketplace' ),
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
		 * Prepare Items
		 *
		 * @return void
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'product_per_page', 20 );
			$current_page = $this->get_pagenum();

			$orderby    = \WK_Caching::wk_get_request_data( 'orderby', array( 'default' => 'user_nicename' ) );
			$sort_order = \WK_Caching::wk_get_request_data( 'order', array( 'default' => 'ASC' ) );

			$filter_data = apply_filters(
				'wkmp_admin_seller_list_filter_data',
				array(
					'start'   => ( $current_page - 1 ) * $per_page,
					'limit'   => $per_page,
					'orderby' => $orderby,
					'order'   => $sort_order,
					'fields'  => 'mp.user_id, mp.seller_value, u.user_email, u.user_registered, u.display_name',
				)
			);

			$total_items = $this->seller_db_obj->wkmp_get_total_sellers( $filter_data );
			$sellers     = $this->seller_db_obj->wkmp_get_sellers( $filter_data );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$this->items = $this->wkmp_get_sellers_data( $sellers );
		}

		/**
		 * Get Sellers data.
		 *
		 * @param array $sellers Sellers.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_sellers_data( $sellers ) {
			global $wkmarketplace;
			$data         = array();
			$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();
			$disabled     = ( $pro_disabled ) ? 'disabled' : '';

			foreach ( $sellers as $seller ) {
				$display_name = $wkmarketplace->wkmp_get_user_display_name( $seller->user_id );

				if ( 'seller' === $seller->seller_value ) {
					$status = '<button ' . esc_attr( $disabled ) . ' type="button" class="button wkmp-button-warning wkmp-approve-for-seller" data-seller-id="' . esc_attr( $seller->user_id ) . '">' . esc_html__( 'Disapprove', 'wk-marketplace' ) . '</button>';
				} else {
					$status = '<button type="button" class="button wkmp-button-success wkmp-approve-for-seller" data-seller-id="' . esc_attr( $seller->user_id ) . '">' . esc_html__( 'Approve', 'wk-marketplace' ) . '</button>';
				}

				$data[] = array(
					'sid'             => $seller->user_id,
					'name'            => $display_name,
					'user_nicename'   => $seller->display_name,
					'shop_name'       => get_user_meta( $seller->user_id, 'shop_name', true ),
					'user_email'      => sprintf( '<a href="mailto:%s">%s</a>', $seller->user_email, $seller->user_email ),
					'products'        => $this->seller_db_obj->wkmp_get_seller_product_count( $seller->user_id ),
					'status'          => $status,
					'user_registered' => $seller->user_registered,
				);
			}

			return apply_filters( 'wkmp_admin_seller_list_data', $data );
		}

		/**
		 * Hidden Columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'              => '<input type="checkbox" />',
				'name'            => esc_html__( 'Name', 'wk-marketplace' ),
				'user_nicename'   => esc_html__( 'Username', 'wk-marketplace' ),
				'shop_name'       => esc_html__( 'Shop Name', 'wk-marketplace' ),
				'user_email'      => esc_html__( 'Email', 'wk-marketplace' ),
				'products'        => esc_html__( 'Item Count', 'wk-marketplace' ),
				'status'          => esc_html__( 'Status', 'wk-marketplace' ),
				'user_registered' => esc_html__( 'Date Created', 'wk-marketplace' ),
			);

			return apply_filters( 'wkmp_admin_seller_list_columns', $columns );
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'user_nicename':
				case 'shop_name':
				case 'user_email':
				case 'products':
				case 'user_registered':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'user_nicename'   => array( 'user_nicename', true ),
				'user_email'      => array( 'user_email', true ),
				'user_registered' => array( 'user_registered', true ),
			);

			return apply_filters( 'wkmp_admin_seller_list_sortable_columns', $sortable_columns );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="seller-id[]" value="%s" />', $item['sid'] );
		}

		/**
		 * Column actions.
		 *
		 * @param array $item Items.
		 *
		 * @return string
		 */
		public function column_name( $item ) {
			$click   = "return confirm('" . esc_html__( 'Are You sure you want to delete this Seller..?', 'wk-marketplace' ) . "')";
			$actions = array(
				'edit'   => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', get_edit_user_link( $item['sid'] ), esc_html__( 'Edit', 'wk-marketplace' ) ),
				'manage' => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', admin_url( 'admin.php?page=wk-marketplace&tab-action=manage&seller-id=' . intval( $item['sid'] ) ), esc_html__( 'Manage', 'wk-marketplace' ) ),
				'delete' => sprintf( '<a onclick="' . $click . '" class="wkmp-seller-edit-link" href="%s">%s</a>', admin_url( 'admin.php?page=wk-marketplace&tab-action=delete&seller-id=' . intval( $item['sid'] ) ), esc_html__( 'Delete', 'wk-marketplace' ) ),
			);

			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( apply_filters( 'wkmp_seller_list_line_actions', $actions ) ) );
		}

		/**
		 * Column actions.
		 *
		 * @param array $item Items.
		 *
		 * @return void
		 */
		public function column_status( $item ) {
			global $wkmarketplace;
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$pro_disabled       = $wkmarketplace->wkmp_is_pro_module_disabled();
			echo wp_kses_post( $item['status'] );
			$pro_disabled ? $template_functions->wkmp_show_upgrade_lock_icon() : '';
		}

		/**
		 * Bulk actions
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array(
				'trash' => esc_html__( 'Delete', 'wk-marketplace' ),
			);

			return apply_filters( 'wkmp_admin_seller_list_bulk_actions', $actions );
		}

		/**
		 * Process row and bulk actions
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === esc_attr( 'trash' ) ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				$seller_ids = \WK_Caching::wk_get_request_data( 'seller-id', array( 'flag' => 'array' ) );
				$success    = 404;

				if ( ! empty( $seller_ids ) && is_iterable( $seller_ids ) ) {
					foreach ( $seller_ids as $seller_id ) {
						$this->seller_db_obj->wkmp_delete_seller( $seller_id );
					}
					$success = 1;
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

			$placeholder = esc_attr__( 'Search by Username or Email', 'wk-marketplace' );

			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$template_functions->wkmp_show_restricted_search_box( $text, $input_id, $placeholder );
		}
	}
}
