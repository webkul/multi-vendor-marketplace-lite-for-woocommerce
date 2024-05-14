<?php
/**
 * Seller Order List In Admin Dashboard.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Product;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper\Admin;
use WkMarketplace\Templates\Admin as AdminTemplates;
use WkMarketplace\Includes\Common;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Product' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Admin_Product extends \WP_List_Table {
		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Product DB Object.
		 *
		 * @var Admin\WKMP_Seller_Product_Data
		 */
		private $product_db_obj;

		/**
		 * Seller DB Object.
		 *
		 * @var Admin\WKMP_Seller_Data
		 */
		private $seller_db_obj;

		/**
		 * Marketplace.
		 *
		 * @var $marketplace \Marketplace
		 */
		private $marketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Product constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->product_db_obj = Admin\WKMP_Seller_Product_Data::get_instance();
			$this->seller_db_obj  = Admin\WKMP_Seller_Data::get_instance();
			$this->marketplace    = $wkmarketplace;

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Product', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Product', 'wk-marketplace' ),
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
		 * Extra Table navigation
		 *
		 * @param [type] $which screen.
		 *
		 * @return void
		 */
		public function extra_tablenav( $which ) {
			$nonce = wp_create_nonce();

			$seller_ids = $this->seller_db_obj->wkmp_get_sellers(
				array(
					'fields'     => 'mp.user_id',
					'single_col' => true,
				)
			);

			$sellers        = array();
			$first_admin_id = $this->marketplace->wkmp_get_first_admin_user_id();

			$sellers[ $first_admin_id ] = $this->marketplace->wkmp_get_user_display_name( $first_admin_id );

			foreach ( $seller_ids as $seller_id ) {
				$seller_info = $this->marketplace->wkmp_get_seller_info( $seller_id );

				if ( ! empty( $seller_info->ID ) ) {
					$sellers[ $seller_id ] = $seller_info->user_login;
				}
			}

			if ( 'top' === $which ) {
				$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
				$button_attrs       = apply_filters( 'wkmp_seller_save_button_attributes', array( 'disabled' => true ) );
				?>
				<div class="alignleft actions bulkactions">
					<select name="wkmp_change_seller" class="regular-text wkmp-select">
						<option value=""><?php esc_html_e( 'Select Seller', 'wk-marketplace' ); ?></option>
						<?php foreach ( $sellers as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php } ?>
					</select>
					<input type="hidden" name="wkmp-product-assign_nonce" value="<?php echo esc_attr( $nonce ); ?>">
					<?php
					submit_button( 'Assign', 'button', 'mp-assign-product-seller', false, $button_attrs );
					empty( $button_attrs['disabled'] ) ? '' : $template_functions->wkmp_show_upgrade_lock_icon();
					?>
				</div>

				<div class="alignleft actions bulkactions">
					<?php
					$wkmp_assigned_option = \WK_Caching::wk_get_request_data( 'wkmp_filter_assign_seller_option' );
					$seller_id            = \WK_Caching::wk_get_request_data( 'wkmp_filter_by_seller', array( 'filter' => 'int' ) );
					?>
					<select name="wkmp_filter_by_seller" class="wkmp_filter_by_seller">
						<option value=""><?php esc_html_e( 'Filter by Seller', 'wk-marketplace' ); ?></option>
						<?php foreach ( $sellers as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( intval( $seller_id ), intval( $key ) ); ?>><?php echo esc_html( $value ); ?></option>
						<?php } ?>
					</select>

					<select name="wkmp_filter_assign_seller_option" class="wkmp_filter_by_seller_options">
						<option value="assigned"<?php selected( 'assign', $wkmp_assigned_option ); ?>><?php esc_html_e( 'Assigned', 'wk-marketplace' ); ?></option>
						<option value="un-assigned" <?php selected( 'un-assigned', $wkmp_assigned_option ); ?>><?php esc_html_e( 'UnAssigned', 'wk-marketplace' ); ?></option>
					</select>

					<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">
					<?php
					submit_button( 'Filter', 'button', 'wkmp_filter_assign_seller_button', false, $button_attrs );
					empty( $button_attrs['disabled'] ) ? '' : $template_functions->wkmp_show_upgrade_lock_icon();
					?>
				</div>
				<?php
			}
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

			$per_page     = $this->get_items_per_page( 'product_per_page', 20 );
			$current_page = $this->get_pagenum();

			do_action( 'wkmp_pro_assign_product_to_seller' );

			$filter_data = apply_filters( 'wkmp_pro_show_filtered_sellers_products', array( 'total' => true ) );

			$total_items = $this->product_db_obj->wkmp_get_products( $filter_data );

			$filter_data['start'] = ( $current_page - 1 ) * $per_page;
			$filter_data['limit'] = $per_page;
			$filter_data['total'] = false;

			$product_data = $this->product_db_obj->wkmp_get_products( $filter_data );
			$data         = $this->wkmp_get_table_data( $product_data );

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
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'           => '<input type="checkbox" />',
				'image'        => esc_html__( 'Image', 'wk-marketplace' ),
				'product'      => esc_html__( 'Product', 'wk-marketplace' ),
				'sku'          => esc_html__( 'SKU', 'wk-marketplace' ),
				'stock'        => esc_html__( 'Stock', 'wk-marketplace' ),
				'price'        => esc_html__( 'Price', 'wk-marketplace' ),
				'categories'   => esc_html__( 'Categories', 'wk-marketplace' ),
				'tags'         => esc_html__( 'Tags', 'wk-marketplace' ),
				'featured'     => esc_html__( 'Featured', 'wk-marketplace' ),
				'type'         => esc_html__( 'Type', 'wk-marketplace' ),
				'date_created' => esc_html__( 'Date', 'wk-marketplace' ),
				'seller'       => esc_html__( 'Seller', 'wk-marketplace' ),
			);
		}

		/**
		 * Column default.
		 *
		 * @param array|object $item Item.
		 * @param string       $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'image':
				case 'product':
				case 'sku':
				case 'stock':
				case 'price':
				case 'categories':
				case 'tags':
				case 'featured':
				case 'type':
				case 'date_created':
				case 'seller':
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
				'product'      => array( 'product', true ),
				'price'        => array( 'price', true ),
				'date_created' => array( 'date', true ),
			);
		}

		/**
		 * Column actions.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_product( $item ) {
			$actions = array(
				'edit'   => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', get_edit_post_link( $item['id'] ), esc_html__( 'Edit', 'wk-marketplace' ) ),
				'manage' => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', get_the_permalink( $item['id'] ), esc_html__( 'View', 'wk-marketplace' ) ),
			);

			return sprintf( '%1$s %2$s', $item['product'], $this->row_actions( apply_filters( 'wkmp_seller_product_list_line_actions', $actions ) ) );
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
		 * @param array|object $item Item.
		 *
		 * @return string|void
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
			$actions = array(
				'trash' => esc_html__( 'Trash', 'wk-marketplace' ),
			);

			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === esc_attr( 'trash' ) ) {
				$ids = \WK_Caching::wk_get_request_data( 'ids', array( 'flag' => 'array' ) );

				$success = 404;

				if ( ! empty( $ids ) && is_iterable( $ids ) ) {
					foreach ( $ids as $id ) {
						$product_trashed = array(
							'ID'          => $id,
							'post_status' => 'trash',
						);
						wp_update_post( $product_trashed );
					}
					$success = 1;
				}

				$page_name = \WK_Caching::wk_get_request_data( 'page' );

				$url = 'admin.php?page=' . $page_name . '&success=' . $success;
				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
		}

		/**
		 * Get table data.
		 *
		 * @param array $product_data Product Data.
		 *
		 * @return array
		 */
		public function wkmp_get_table_data( $product_data ) {
			$data             = array();
			$common_functions = Common\WKMP_Common_Functions::get_instance();

			foreach ( $product_data as $product_id => $post_author ) {
				$product_info = wc_get_product( $product_id );
				$image        = wc_placeholder_img_src();

				if ( $product_info->get_image_id() ) {
					$image = wp_get_attachment_image_src( $product_info->get_image_id() )[0];
				}

				$image = '<img class="attachment-shop_thumbnail wp-post-image" width="50" height="50" alt="" src="' . esc_url( $image ) . '">';

				if ( $product_info->is_type( 'simple' ) ) {
					$price = '<span class="amount">' . wc_price( $product_info->get_price() ) . '</span>';
				} elseif ( $product_info->is_type( 'variable' ) ) {
					$price = '<span class="price"><span class="amount">' . wc_price( $product_info->get_variation_prices()['price'] ? min( $product_info->get_variation_prices()['price'] ) : 0 ) . '</span>&ndash;<span class="amount">' . wc_price( $product_info->get_variation_prices()['price'] ? max( $product_info->get_variation_prices()['price'] ) : 0 ) . '</span></span>';
				} elseif ( $product_info->is_type( 'external' ) ) {
					$price = '<span class="amount">' . wc_price( $product_info->get_price() ) . '</span>';
				} elseif ( $product_info->is_type( 'grouped' ) ) {
					$price = '<span class="amount">-</span>';
				} else {
					$price = '<span class="amount">' . wc_price( $product_info->get_price() ) . '</span>';
				}

				$product_cats = get_the_terms( $product_id, 'product_cat' );
				$product_tags = get_the_terms( $product_id, 'product_tag' );

				$category = array();
				if ( ! empty( $product_cats ) ) {
					foreach ( $product_cats as $cat ) {
						$category[] = $cat->name;
					}
				}

				$tags = array();
				if ( ! empty( $product_tags ) ) {
					foreach ( $product_tags as $tag ) {
						$tags[] = '<a href="' . esc_url( admin_url( 'edit.php?product_tag=' . $tag->slug . '&post_type=product' ) ) . ' ">' . esc_html( $tag->name ) . '</a>';
					}
				}

				$date_created = $product_info->get_date_created();
				if ( empty( $date_created ) ) {
					$date_created = $product_info->get_date_modified();
				}

				$created_date = gmdate( 'Y-n-j', strtotime( $date_created ) );
				$seller_name  = esc_html__( 'Admin', 'wk-marketplace' );

				$seller_name = $this->marketplace->wkmp_get_user_display_name( $post_author, '', 'full', $seller_name );

				$data[] = array(
					'id'           => $product_id,
					'image'        => $image,
					'product'      => $product_info->get_name(),
					'sku'          => $common_functions->wkmp_get_sku( $product_info ),
					'stock'        => '<mark class="instock">' . ucfirst( $product_info->get_stock_status() ) . '</mark>',
					'price'        => $price,
					'categories'   => implode( ',', $category ),
					'tags'         => empty( $tags ) ? '<span class="na">&ndash;</span>' : implode( ', ', $tags ),
					'featured'     => $product_info->is_featured() ? 'Yes' : 'No',
					'type'         => ucfirst( $product_info->get_type() ),
					'date_created' => $created_date . '</br>' . ucfirst( $product_info->get_status() ),
					'seller'       => $seller_name,
				);
			}

			return $data;
		}

		/**
		 * Displays restricted search box to allow pro feature.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			$search = \WK_Caching::wk_get_request_data( 's' );

			if ( empty( $search ) && ! $this->has_items() ) { // WPCS: input var okay, CSRF ok.
				return;
			}

			$placeholder = esc_attr__( 'Search by Product Title', 'wk-marketplace' );

			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$template_functions->wkmp_show_restricted_search_box( $text, $input_id, $placeholder );
		}
	}
}
