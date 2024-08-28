<?php
/**
 * Admin End Functions
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WK_Caching;
use WkMarketplace\Helper\Admin as Helper;
use WkMarketplace\Helper\Front;
use WkMarketplace\Templates\Front as FrontTemplates;
use WkMarketplace\Templates\Admin as AdminTemplate;
use WkMarketplace\Includes\Front as IncludeFront;

if ( ! class_exists( 'WKMP_Admin_Functions' ) ) {
	/**
	 * Admin hooks class
	 */
	class WKMP_Admin_Functions {
		/**
		 * Template handler
		 *
		 * @var object
		 */
		protected $template_handler;

		/**
		 * Seller class object.
		 *
		 * @var Helper\WKMP_Seller_Data
		 */
		protected $seller_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Functions constructor.
		 *
		 * @param null $template_handler Template handler.
		 */
		public function __construct( $template_handler = null ) {
			$this->template_handler = $template_handler;
			$this->seller_obj       = Helper\WKMP_Seller_Data::get_instance();
		}

		/**
		 * Prevent seller admin access.
		 *
		 * @return void
		 */
		public function wkmp_prevent_seller_admin_access() {
			if ( wp_doing_ajax() ) {
				return;
			}

			if ( is_user_logged_in() ) {
				$user         = wp_get_current_user();
				$current_dash = get_user_meta( $user->ID, 'wkmp_seller_backend_dashboard', true );
				if ( in_array( 'wk_marketplace_seller', $user->roles, true ) && empty( $current_dash ) ) {
					$redirect = esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/' . get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ) );
					wp_safe_redirect( $redirect );
					exit;
				}
			}
		}

		/**
		 * Register Options
		 *
		 * @return void
		 */
		public function wkmp_register_marketplace_options() {
			register_setting( 'wkmp-general-settings-group', '_wkmp_default_commission', array( $this, 'wkmp_validate_seller_commission_update' ) );

			register_setting( 'wkmp-general-settings-group', '_wkmp_seller_delete' );
			register_setting( 'wkmp-general-settings-group', 'wkmp_shop_name_visibility' );
			register_setting( 'wkmp-general-settings-group', 'wkmp_shop_url_visibility' );
			register_setting( 'wkmp-general-settings-group', 'wkmp_select_seller_page', array( $this, 'wkmp_update_selected_seller_page' ) );

			register_setting( 'wkmp-product-settings-group', '_wkmp_wcml_allow_product_translate' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_seller_allowed_product_types' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_seller_allowed_categories' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_enable_minimum_order_amount' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_minimum_order_amount' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_seller_min_amount_admin_default' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_enable_product_qty_limit' );
			register_setting( 'wkmp-product-settings-group', '_wkmp_max_product_qty_limit' );

			register_setting( 'wkmp-google-analytics-settings-group', '_wkmp_google_map_api_key' );

			$assets_settings = apply_filters( 'wkmp_admin_assets_settings_fields', array( '_wkmp_is_seller_email_visible', '_wkmp_is_seller_contact_visible', '_wkmp_is_seller_address_visible', '_wkmp_is_seller_social_links_visible' ) );

			foreach ( $assets_settings as $field ) {
				register_setting( 'wkmp-assets-settings-group', $field );
			}

			$template_functions = AdminTemplate\WKMP_Admin_Template_Functions::get_instance();

			$template_functions->wkmp_maybe_disable_sellers();
		}

		/**
		 * Updating seller commission with validation between 0 to 100.
		 *
		 * @param float $commission Commission.
		 *
		 * @return float|string
		 */
		public function wkmp_validate_seller_commission_update( $commission ) {
			$commission = is_null( $commission ) ? '' : wc_format_decimal( trim( stripslashes( $commission ) ) );

			if ( is_numeric( $commission ) && $commission >= 0 && $commission <= 100 ) {
				return $commission;
			} else {
				add_settings_error( '_wkmp_default_commission', 'commission-error', ( /* translators: %s Commission */ sprintf( esc_html__( 'Invalid default commission value %s. Must be between 0 & 100.', 'wk-marketplace' ), esc_attr( $commission ) ) ), 'error' );

				return '';
			}
		}

		/**
		 * Updating selected seller page along with managing shortcode content on new page.
		 *
		 * @param int $new_seller_page_id New seller page id.
		 *
		 * @return int
		 */
		public function wkmp_update_selected_seller_page( $new_seller_page_id ) {
			$seller_page_id = get_option( 'wkmp_seller_page_id' );

			if ( intval( $seller_page_id ) !== intval( $new_seller_page_id ) ) {
				$new_seller_page = get_post( $new_seller_page_id );
				$new_seller_slug = isset( $new_seller_page->post_name ) ? $new_seller_page->post_name : '';
				if ( empty( $new_seller_slug ) ) {
					return $seller_page_id;
				}
				update_option( 'wkmp_seller_page_id', $new_seller_page_id );
				update_option( 'wkmp_seller_page_slug', $new_seller_slug );

				// Updating marketplace shortcode in new page content.
				$new_content = array(
					'ID'           => $new_seller_page_id,
					'post_content' => '[marketplace]',
				);

				// Update the post into the database.
				wp_update_post( $new_content );

				// Remove marketplace shortcode from previous seller assigned page.
				$old_content = array(
					'ID'           => $seller_page_id,
					'post_content' => '',
				);
				wp_update_post( $old_content );

				flush_rewrite_rules( false );
			}

			return $new_seller_page_id;
		}

		/**
		 * Dashboard Menus for Marketplace
		 *
		 * @return void
		 */
		public function wkmp_create_dashboard_menu() {
			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_marketplace' );

			$allowed_roles = array( 'administrator' );
			if ( 'edit_posts' === $capability ) {
				$allowed_roles[] = 'editor';
			}

			$c_user_id = get_current_user_id();
			$user      = get_user_by( 'id', $c_user_id );
			$c_roles   = ( $user instanceof \WP_User ) ? $user->roles : array();

			if ( empty( array_intersect( $allowed_roles, $c_roles ) ) ) {
				return;
			}

			add_menu_page(
				esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Marketplace', 'wk-marketplace' ),
				$capability,
				'wk-marketplace',
				null,
				'dashicons-store',
				55
			);

			$sellers = add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Marketplace', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Sellers', 'wk-marketplace' ),
				$capability,
				'wk-marketplace',
				array(
					$this->template_handler,
					'wkmp_marketplace_sellers',
				)
			);

			$products = add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Products', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Products', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-products',
				array(
					$this->template_handler,
					'wkmp_marketplace_products',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Notifications', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Notifications', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-notifications',
				array(
					$this->template_handler,
					'wkmp_marketplace_notifications',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Feedback', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Reviews & Rating', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-feedback',
				array(
					$this->template_handler,
					'wkmp_marketplace_feedback',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Queries', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Queries', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-queries',
				array(
					$this->template_handler,
					'wkmp_marketplace_queries',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Settings', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Settings', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-settings',
				array(
					$this->template_handler,
					'wkmp_marketplace_settings',
				)
			);

			do_action( 'wkmp_admin_menu_action' );

			add_action( "load-{$sellers}", array( $this, 'wkmp_seller_list_screen_option' ) );
			add_action( "load-{$products}", array( $this, 'wkmp_seller_product_list_screen_option' ) );
		}

		/**
		 * Create submenu pages.
		 *
		 * @return void
		 */
		public function wkmp_create_submenu_menu() {
			$capability = apply_filters( 'wkmp_dashboard_menu_capability', 'manage_marketplace' );

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Extensions', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Extensions', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-extensions',
				array(
					$this->template_handler,
					'wkmp_marketplace_extensions',
				)
			);

			add_submenu_page(
				'wk-marketplace',
				esc_html__( 'Support & Services', 'wk-marketplace' ) . ' | ' . esc_html__( 'Marketplace', 'wk-marketplace' ),
				esc_html__( 'Support & Services', 'wk-marketplace' ),
				$capability,
				'wk-marketplace-support-services',
				array(
					$this->template_handler,
					'wkmp_marketplace_support_services',
				)
			);
		}

		/**
		 * Seller List Screen Options
		 *
		 * @return void
		 */
		public function wkmp_seller_list_screen_option() {
			$option = 'per_page';
			$args   = array(
				'label'   => esc_html__( 'Data Per Page', 'wk-marketplace' ),
				'default' => 10,
				'option'  => 'product_per_page',
			);

			add_screen_option( $option, $args );
		}

		/**
		 * Seller Product List Screen Options
		 *
		 * @return void
		 */
		public function wkmp_seller_product_list_screen_option() {
			$option = 'per_page';
			$args   = array(
				'label'   => esc_html__( 'Product Per Page', 'wk-marketplace' ),
				'default' => 10,
				'option'  => 'product_per_page',
			);

			add_screen_option( $option, $args );
		}

		/**
		 * Screen
		 *
		 * @param string  $status Status.
		 * @param string  $option Option Name.
		 * @param integer $value Option Value.
		 *
		 * @return $value
		 */
		public function wkmp_set_screen( $status, $option, $value ) {
			$options = array( 'wkmp_seller_per_page', 'wkmp_product_per_page' );
			if ( in_array( $option, $options, true ) ) {
				return $value;
			}

			return $status;
		}

		/**
		 * Set screen ids
		 *
		 * @param array $ids IDs.
		 *
		 * @return array
		 */
		public function wkmp_set_wc_screen_ids( $ids ) {
			array_push( $ids, 'toplevel_page_wk-marketplace', 'marketplace_page_wk-marketplace-settings', 'marketplace_page_wk-marketplace' );

			return $ids;
		}

		/**
		 * Admin footer text.
		 *
		 * @param string $text footer text.
		 */
		public function wkmp_admin_footer_text( $text ) {
			$wk_page = \WK_Caching::wk_get_request_data( 'page' );

			if ( ! empty( $wk_page ) && 0 === stripos( $wk_page, 'wk-marketplace' ) ) {
				$text = wp_sprintf( __( 'If you like <strong>Marketplace</strong> please leave us a <a href="https://wordpress.org/support/plugin/multi-vendor-marketplace-lite-for-woocommerce/reviews/?filter=5#new-post" target="_blank" class="wc-rating-link" data-rated="Thanks :)">★★★★★</a> rating. A lot of thanks in advance!', 'wk-marketplace' ) );
			}

			return $text;
		}

		/**
		 * Admin end scripts
		 *
		 * @return void
		 */
		public function wkmp_admin_scripts() {
			$suffix     = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? '' : '.min';
			$asset_path = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? 'build' : 'dist';

			wp_enqueue_style( 'wkmp-admin-style', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/admin/css/admin' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION );
			wp_enqueue_style( 'wkmp-admin-wc-style', plugins_url() . '/woocommerce/assets/client/admin/admin-layout/style.css', array(), WC_VERSION );

			wp_enqueue_script( 'wkmp-admin-script', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/admin/js/admin' . $suffix . '.js', array( 'select2' ), WKMP_LITE_SCRIPT_VERSION, true );

			$ajax_obj = array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce' => wp_create_nonce( 'wkmp-admin-nonce' ),
			);

			wp_localize_script(
				'wkmp-admin-script',
				'wkmpObj',
				array(
					'ajax'                 => $ajax_obj,
					'text_required'        => esc_html__( 'This field is required', 'wk-marketplace' ),
					'text_unique'          => esc_html__( 'This field must be unique', 'wk-marketplace' ),
					'pay_confirm'          => esc_html__( 'Are you sure you want to pay?', 'wk-marketplace' ),
					'order_status_confirm' => esc_html__( 'Are you sure you want to change status?', 'wk-marketplace' ),
					'already_paid'         => esc_html__( 'Payment has already been done for order id: ', 'wk-marketplace' ),
					'shop_name'            => esc_html__( 'Please fill shop name.', 'wk-marketplace' ),
					'failed_btn'           => esc_html__( 'Failed', 'wk-marketplace' ),
				)
			);

			$wk_page = \WK_Caching::wk_get_request_data( 'page' );

			if ( 'wk-marketplace-support-services' === $wk_page ) {
				wp_enqueue_script( 'wkmp-admin-suport-services', 'https://webkul.com/common/modules/wksas.bundle.js', array(), WKMP_LITE_SCRIPT_VERSION, true );
			}
			if ( 'wk-marketplace-extensions' === $wk_page ) {
				wp_enqueue_script( 'wkmp-admin-extensions', 'https://wpdemo.webkul.com/wk-extensions/client/wk.ext.js', array(), WKMP_LITE_SCRIPT_VERSION, true );
			}
		}

		/**
		 * Menu invoice page.
		 *
		 * @return void
		 */
		public function wkmp_virtual_menu_invoice_page() {
			$hook = add_submenu_page(
				'',
				esc_html__( 'Invoice', 'wk-marketplace' ),
				esc_html__( 'Invoice', 'wk-marketplace' ),
				'edit_posts',
				'invoice',
				function () {
				}
			);
			add_action(
				'load-' . $hook,
				function () {
					if ( is_user_logged_in() && is_admin() ) {
						$suffix     = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? '' : '.min';
						$asset_path = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? 'build' : 'dist';

						$wk_page       = \WK_Caching::wk_get_request_data( 'page' );
						$order_id_hash = \WK_Caching::wk_get_request_data( 'order_id' );

						if ( 'invoice' === $wk_page && ! empty( $order_id_hash ) ) {
							wp_enqueue_style( 'wkmp-invoice-stype', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/admin/css/invoice-style' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION, 'all' );
						}

						$order_id = \WK_Caching::wk_get_request_data( 'order_id' );
						$order_id = base64_decode( $order_id ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
						$this->wkmp_admin_end_invoice( $order_id );
					} else {
						wp_die( '<h1>' . esc_html__( 'Cheatin’ uh?', 'wk-marketplace' ) . '</h1><p>' . esc_html__( 'Sorry, you are not allowed to access invoice.', 'wk-marketplace' ) . '</p>' );
					}
					exit;
				}
			);
		}

		/**
		 * Order invoice button.
		 *
		 * @param \WC_Order $order order object.
		 */
		public function wkmp_order_invoice_button( $order ) {
			if ( 'trash' === $order->get_status() ) {
				return;
			}
			$listing_actions = array(
				'invoice' => array(
					'name' => esc_html__( 'Invoice', 'wk-marketplace' ),
					'alt'  => esc_html__( 'Invoice', 'wk-marketplace' ),
					'url'  => wp_nonce_url( admin_url( 'edit.php?page=invoice&order_id=' . base64_encode( $order->get_id() ) ), 'generate_invoice', 'invoice_nonce' ),
				),
			);

			foreach ( $listing_actions as $action => $data ) {
				?>
				<a href="<?php echo esc_url( $data['url'] ); ?>" class="<?php echo esc_attr( $action ); ?>" target="_blank" title="<?php echo esc_attr( $data['alt'] ); ?>"><span class="dashicons dashicons-media-default"></span></a>
				<?php
			}
		}

		/**
		 * Admin side invoice.
		 *
		 * @param int $order_id order id.
		 */
		public function wkmp_admin_end_invoice( $order_id ) {
			$admin_order = wc_get_order( $order_id );
			require_once WKMP_LITE_PLUGIN_FILE . 'templates/admin/wkmp-admin-order-invoice.php';
		}

		/**
		 * Admin Notice.
		 *
		 * @return void
		 */
		public function wkmp_show_notice_on_seller_paid_by_admin() {
			$order_ids = \WK_Caching::wk_get_request_data(
				'oid',
				array(
					'method' => 'post',
					'flag'   => 'array',
				)
			);

			if ( ! empty( $order_ids ) ) {
				$message = esc_html__( 'Payment has been successfully done.', 'wk-marketplace' );
				\WK_Caching::wk_show_notice_on_admin(
					$message,
					'success',
					array(
						'paragraph_wrap' => true,
						'is-dismissible' => true,
					)
				);
			}
		}

		/**
		 * Extra user profile.
		 *
		 * @param object $user User object.
		 */
		public function wkmp_extra_user_profile_fields( $user ) {
			$show_fields = false;

			if ( ! $user instanceof \WP_User && 'add-new-user' === $user ) {
				$show_fields = true;
			}

			require_once WKMP_LITE_PLUGIN_FILE . 'templates/admin/user/wkmp-user-profile.php';
		}

		/**
		 * Add Seller meta-box.
		 *
		 * @return void
		 */
		public function wkmp_add_seller_metabox() {
			global $current_user;
			if ( ! in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				add_meta_box( 'seller-meta-box', esc_html__( 'Seller', 'wk-marketplace' ), array( $this, 'wkmp_seller_metabox' ), 'product', 'side', 'low', null );
			}
		}

		/**
		 * Seller meta-box.
		 *
		 * @return void
		 */
		public function wkmp_seller_metabox() {
			global $post, $wkmarketplace;

			$seller_ids = $this->seller_obj->wkmp_get_sellers(
				array(
					'fields'     => 'mp.user_id',
					'role'       => 'seller',
					'single_col' => true,
				)
			);

			$seller_ids = array_map( 'intval', $seller_ids );

			wp_nonce_field( 'wkmp_save_meta_box_seller', 'wkmp_seller_meta_box_nonce' );
			?>
			<div class="wkmp-product-assigned-seller">
				<select name="seller_id">
					<option value="<?php echo esc_attr( get_current_user_id() ); ?>">--<?php esc_html_e( 'Select Seller', 'wk-marketplace' ); ?>---</option>
					<?php
					foreach ( $seller_ids as $user_id ) {
						$display_name = $wkmarketplace->wkmp_get_user_display_name( $user_id );
						?>
						<option value="<?php echo esc_attr( $user_id ); ?>" <?php echo ( intval( $post->post_author ) === intval( $user_id ) ) ? 'selected' : ''; ?>><?php echo esc_html( $display_name ); ?></option>
						<?php
					}
					?>
				</select>
			</div>
			<?php
		}

		/**
		 * Functions changing the order status.
		 *
		 * @param int $order_id order id.
		 * @param int $old_status order old status.
		 * @param int $new_status order new status.
		 */
		public function wkmp_order_status_changed_action( $order_id, $old_status, $new_status ) {
			$obj = Helper\WKMP_Seller_Order_Data::get_instance();
			$obj->wkmp_update_order_status_on_changed( $order_id, $new_status );
		}

		/**
		 * Deleting seller from MP table on deletion of the seller from WP users screen.
		 *
		 * @param int      $user_id User id.
		 * @param int      $reassign Userid to re-assign.
		 * @param \WP_User $user User object.
		 */
		public function wkmp_delete_seller_on_user_delete( $user_id, $reassign, $user ) {
			if ( $user instanceof \WP_User && in_array( 'wk_marketplace_seller', $user->roles, true ) ) {
				$this->seller_obj->wkmp_delete_seller( $user_id );
				wkmp_wc_log( "Seller deleted on deleted wp user from users screen: $user_id, Reassign user: $reassign" );
			}
		}

		/**
		 * Adding max qty filed.
		 *
		 * @hooked woocommerce_product_options_inventory_product_data
		 */
		public function wkmp_add_max_qty_field() {
			global $product_object;
			if ( 'grouped' !== $product_object->get_type() ) {
				woocommerce_wp_text_input(
					array(
						'id'                => '_wkmp_max_product_qty_limit',
						'value'             => $product_object->get_meta( '_wkmp_max_product_qty_limit' ),
						'label'             => __( 'Maximum Purchasable Quantity', 'wk-marketplace' ),
						'placeholder'       => __( 'Enter Maximum Purchasable Quantity', 'wk-marketplace' ),
						'desc_tip'          => true,
						'custom_attributes' => array( 'min' => 0 ),
						'description'       => __( 'Customer can add only this max quantity in their carts.', 'wk-marketplace' ),
					)
				);
			}
		}

		/**
		 * Removing seller's shipping classes from Admin product edit page.
		 *
		 * @param array $args Get terms args.
		 * @param array $taxonomies Get term taxonomies.
		 *
		 * @hooked 'get_terms_args' filter hook.
		 *
		 * @return array.
		 */
		public function wkmp_remove_sellers_shipping_classes( $args, $taxonomies ) {
			global $current_user;

			if ( ! in_array( 'product_shipping_class', $taxonomies, true ) || ! is_admin() ) {
				return $args;
			}

			if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$user_shipping_classes = get_user_meta( $current_user->ID, 'shipping-classes', true );
				$user_shipping_classes = empty( $user_shipping_classes ) ? array() : maybe_unserialize( $user_shipping_classes );
				$args['include']       = $user_shipping_classes;
			}

			return $args;
		}

		/**
		 * Removing restricted categories for seller product category filter in backend product listing.
		 *
		 * @param array $filters Product filters.
		 *
		 * @return array
		 */
		public function wkmp_remove_restricted_cats( $filters ) {
			global $wkmarketplace;
			$seller_id = get_current_user_id();
			if ( $wkmarketplace->wkmp_user_is_seller( $seller_id ) && ! empty( $filters['product_category'] ) ) {
				$filters['product_category'] = array( $this, 'wkmp_filtered_product_category' );
			}
			return $filters;
		}

		/**
		 * Filtered product category for admin listing.
		 *
		 * @return void
		 */
		public function wkmp_filtered_product_category() {
			$categories_count = (int) wp_count_terms( 'product_cat' );

			if ( $categories_count <= apply_filters( 'woocommerce_product_category_filter_threshold', 100 ) ) {
				$seller_id    = get_current_user_id();
				$allowed_cats = get_user_meta( $seller_id, 'wkmp_seller_allowed_categories', true );

				if ( empty( $allowed_cats ) ) {
					$allowed_cats = get_option( '_wkmp_seller_allowed_categories', array() );
				}

				$allowed_cat_ids = array();

				if ( ! empty( $allowed_cats ) ) {
					foreach ( $allowed_cats as $allowed_cat ) {
						$cat               = get_term_by( 'slug', $allowed_cat, 'product_cat' );
						$allowed_cat_ids[] = $cat->term_id;
					}
				}

				$categories_ids = get_terms(
					array(
						'taxonomy' => 'product_cat',
						'fields'   => 'ids',
					)
				);

				$allowed_ids = array_diff( $categories_ids, $allowed_cat_ids );

				$args = array(
					'option_select_text' => __( 'Filter by category', 'wk-marketplace' ),
					'hide_empty'         => 0,
					'show_count'         => 0,
				);

				if ( ! empty( $allowed_ids ) ) {
					$args['exclude'] = $allowed_ids;
				}

				wc_product_dropdown_categories( $args );
			} else {
				$current_category_slug = \WK_Caching::wk_get_request_data( 'product_cat' );
				$current_category      = $current_category_slug ? get_term_by( 'slug', $current_category_slug, 'product_cat' ) : false;
				?>
			<select class="wc-category-search" name="product_cat" data-placeholder="<?php esc_attr_e( 'Filter by category', 'wk-marketplace' ); ?>" data-allow_clear="true">
				<?php if ( $current_category_slug && $current_category ) : ?>
					<option value="<?php echo esc_attr( $current_category_slug ); ?>" selected="selected"><?php echo esc_html( htmlspecialchars( wp_kses_post( $current_category->name ) ) ); ?></option>
				<?php endif; ?>
			</select>
				<?php
			}
		}

		/**
		 * Updating seller order mapping on creating new order from admin.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked 'woocommerce_new_order' action hook.
		 *
		 * @return void
		 */
		public function wkmp_update_seller_order_mapping( $order_id ) {
			wkmp_wc_log( "WKMP admin created new order: $order_id" );

			if ( $order_id > 0 ) {
				$wc_order = wc_get_order( $order_id );

				if ( $wc_order instanceof \WC_Order ) {
					$items        = $wc_order->get_items();
					$author_array = array();

					foreach ( $items as $item ) {
						$assigned_seller = wc_get_order_item_meta( $item->get_id(), 'assigned_seller', true );
						$assigned_seller = empty( $assigned_seller ) ? get_post_field( 'post_author', $item->get_product_id() ) : $assigned_seller;

						if ( ! in_array( $assigned_seller, $author_array, true ) ) {
							$author_array[] = $assigned_seller;
						}
					}

					$author_array = array_unique( $author_array );
					$db_obj_order = Front\WKMP_Order_Queries::get_instance();

					$db_obj_order->wkmp_update_seller_orders( $author_array, $order_id );

					$template_handler = FrontTemplates\WKMP_Front_Template_Functions::get_instance();
					$function_handler = new IncludeFront\WKMP_Front_Functions( $template_handler );

					$function_handler->wkmp_add_order_commission_data( $order_id );
				}
			}
		}

		/**
		 * Updating sold by order item meta on creating order from admin.
		 *
		 * @param int    $item_id Item id.
		 * @param object $item WC_Order_Item_Product object.
		 * @param int    $order_id Order id.
		 *
		 * @hooked 'woocommerce_new_order_item' action hook.
		 *
		 * @return void
		 */
		public function wkmp_update_soldby_to_admin_order( $item_id, $item, $order_id ) {
			wkmp_wc_log( "WKMP admin creating new order item id: $item_id, Order id: $order_id" );

			if ( $item instanceof \WC_Order_Item_Product ) {
				$prod_id = $item->get_product_id();
				wkmp_wc_log( "WKMP admin creating new order item product id: $prod_id" );

				if ( $prod_id > 0 ) {
					$author_id = get_post_field( 'post_author', $prod_id );
					wkmp_wc_log( "WKMP admin creating new order item product author id: $author_id" );
					$item->update_meta_data( 'Sold By', 'wkmp_seller_id=' . $author_id );
					$item->save_meta_data();
				}
			}
		}

		/**
		 * Show setting links.
		 *
		 * @param array $links Setting links.
		 *
		 * @return array
		 */
		public function wkmp_add_plugin_setting_links( $links ) {
			global $wkmarketplace;

			$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();

			$links   = is_array( $links ) ? $links : array();
			$links[] = '<a href="' . esc_url( admin_url( '/admin.php?page=wk-marketplace-settings' ) ) . '">' . esc_html__( 'Settings', 'wk-marketplace' ) . '</a>';

			if ( $pro_disabled ) {
				$links[] = sprintf( '<a href="%1$s" target="_blank" class="wkmp-get_pro">%2$s</a>', WKMP_PRO_MODULE_URL, esc_html__( 'Get Pro', 'wk-marketplace' ) );
			}

			return $links;
		}

		/**
		 * Plugin row data.
		 *
		 * @param string $links Links.
		 * @param string $file Filepath.
		 *
		 * @hooked 'plugin_row_meta' filter hook.
		 *
		 * @return array
		 */
		public function wkmp_plugin_show_row_meta( $links, $file ) {
			if ( plugin_basename( WKMP_LITE_FILE ) === $file ) {
				$row_meta = array(
					'docs'    => '<a target="_blank" href="' . esc_url( 'https://webkul.com/blog/marketplace-for-woocommerce-lite/' ) . '" aria-label="' . esc_attr__( 'View Marketplace documentation', 'wk-marketplace' ) . '">' . esc_html__( 'Docs', 'wk-marketplace' ) . '</a>',
					'support' => '<a target="_blank" href="' . esc_url( 'https://webkul.uvdesk.com/' ) . '" aria-label="' . esc_attr__( 'Visit customer support', 'wk-marketplace' ) . '">' . esc_html__( 'Support', 'wk-marketplace' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Show admin notices for different purposes.
		 *
		 * @return void
		 */
		public function wkmp_maybe_show_notices_on_admin() {
			global $wkmarketplace;

			if ( 'no' === get_option( 'woocommerce_enable_myaccount_registration', 'no' ) ) { // For my-account registration disabled.
				$account_url = admin_url( 'admin.php?page=wc-settings&tab=account' );
				$message     = wp_sprintf( /* translators: %s Settings test, %s: Setting page link */ esc_html__( 'To allow seller registration %1$s setting must be checked from %2$s ', 'wk-marketplace' ), '<b>' . esc_html__( 'Allow customers to create an account on the My account page', 'wk-marketplace' ) . '</b>', '<a href="' . esc_url( $account_url ) . '">' . esc_html__( 'WooCommerce Account Settings', 'wk-marketplace' ) . '</a>' );
				WK_Caching::wk_show_notice_on_admin( $message, 'error' );
			}

			$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();

			if ( $pro_disabled ) {
				$seller_db_obj = Helper\WKMP_Seller_Data::get_instance();

				$lite_count  = $seller_db_obj->wkmp_get_lite_allowed_sellers();
				$total_count = $seller_db_obj->wkmp_get_total_sellers( array( 'verified' => false ) );

				if ( absint( $total_count ) >= absint( $lite_count ) ) {
					$message = wp_sprintf( /* translators: %s Pro module link. */ esc_html__( 'Your have reached the limit to register sellers. To allow further registration kindly consider Upgrade to Pro version of %s', 'wk-marketplace' ), '<b><a target="_blank" href="' . esc_url( WKMP_PRO_MODULE_URL ) . '">' . esc_html__( 'Marketplace for WooCommerce', 'wk-marketplace' ) . '</a></b>' );
					\WK_Caching::wk_show_notice_on_admin( $message, 'error' );
				}
				$wk_page = \WK_Caching::wk_get_request_data( 'page' );

				if ( ! empty( $wk_page ) && 0 === stripos( $wk_page, 'wk-marketplace' ) ) {
					$first_admin_id = $wkmarketplace->wkmp_get_first_admin_user_id();
					$display_name   = $wkmarketplace->wkmp_get_user_display_name( $first_admin_id );
					?>
				<div data-admin_id="<?php echo esc_attr( get_current_user_id() ); ?>" class="wkmp-upgrade-pro-banner-notice notice notice-info is-dismissible wkmp-hide">

					<section class="upgrade-banner-design">
						<div class="upgrade-banner-wrap">
							<div class="upgrade-banner">
								<img src="<?php echo esc_url( WKMP_LITE_PLUGIN_URL . '/assets/images/wkmp-pro-banner.png' ); ?>" alt="banner">
							</div>
							<div class="upgrade-banner-content">
								<img src="<?php echo esc_url( WKMP_LITE_PLUGIN_URL . 'assets/images/wk-logo.png' ); ?>" alt="watermark" class="upgrade-banner-watermark">
								<h2 class="upgrade-title"><?php echo wp_sprintf( /* Translators: %s: Display Name. */ esc_html__( 'Hi %s,', 'wk-marketplace' ), esc_attr( $display_name ) ); ?></h2>
								<p class="upgrade-sub-content"><?php esc_html_e( 'Are you ready to take your Multi-Vendor Marketplace to new heights?', 'wk-marketplace' ); ?></p>
								<p class="upgrade-content"><?php esc_html_e( 'Unlock a world of possibilities with our premium "Pro" package designed to supercharge your business and maximize your potential.', 'wk-marketplace' ); ?></p>

								<div class="upgrade-banner-box">
									<ul class="upgrade-checkbox">
										<li class="upgrade-tittle"><?php esc_html_e( 'Allow Separate Seller Dashboard', 'wk-marketplace' ); ?></li>
										<li class="upgrade-tittle"><?php esc_html_e( 'Allow Customer to Become a Seller', 'wk-marketplace' ); ?></li>
										<li class="upgrade-tittle"><?php esc_html_e( 'Add ', 'wk-marketplace' ); ?><a target="_blank" href="<?php echo esc_url( 'https://wpml.org/plugin/marketplace-for-woocommerce/' ); ?>"><?php esc_html_e( 'WPML Compatiblity', 'wk-marketplace' ); ?></a></li>
										<li class="upgrade-tittle"><?php esc_html_e( 'Apply Seller wise Commission', 'wk-marketplace' ); ?></li>
										<li class="upgrade-tittle"><?php esc_html_e( 'Allow Seller Shipping Methods', 'wk-marketplace' ); ?></li>
										<li class="upgrade-tittle"><?php esc_html_e( 'Unlock to use ', 'wk-marketplace' ); ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=wk-marketplace-extensions&ext_tab=3' ) ); ?>"><?php esc_html_e( '50+ Addons', 'wk-marketplace' ); ?></a></li>
									</ul>
								</div>
								<a target="_blank" href="<?php echo esc_url( WKMP_PRO_MODULE_URL ); ?>" class="upgr-btn"><?php esc_html_e( 'Upgrade To Pro', 'wk-marketplace' ); ?></a>
							</div>
						</div>
					</section>
				</div>

				<div class="wkmp-toast-notice upgrade-to-pro notice notice-info is-dismissible wkmp-hide">
					<section class="toast">
						<div class="upgrade-toast-wrap">
							<div class="upgrade-toast-content">
								<div class="upgrade-toast-box">
									<span class="upgrade-toast-icon"></span>
									<p class="upgrade-toast-content"><?php esc_html_e( 'You are currently using lite version of Multi-Vendor Marketplace, to unlock more advanced features.', 'wk-marketplace' ); ?></p>
								</div>
								<a target="_blank" href="<?php echo esc_url( WKMP_PRO_MODULE_URL ); ?>" class="upgr-toast-btn"><?php esc_html_e( 'Upgrade to Pro Now', 'wk-marketplace' ); ?></a>
							</div>
						</div>
					</section>
				</div>
					<?php
				}
			}
		}

		/**
		 * Hide other seller's post comments.
		 *
		 * @param array $args Comment args.
		 *
		 * @hooked 'comments_list_table_query_args' filter hook.
		 *
		 * @return array
		 */
		public function wkmp_hide_other_comments_on_seller_dashboard( $args ) {
			global $pagenow;
			if ( 'edit-comments.php' === $pagenow ) {
				$user  = wp_get_current_user();
				$value = ! empty( $args['author__in'] ) ? $args['author__in'] : array();

				if ( is_array( $value ) && ! in_array( $user->ID, $value, true ) && in_array( 'wk_marketplace_seller', (array) $user->roles, true ) ) {
					$value[] = $user->ID;
				}

				$args['author__in'] = $value;
			}

			return $args;
		}

		/**
		 * Remove marketplace seller role from change role to Dropdown on WP Users table.
		 *
		 * @param array $editable_roles Editable roles.
		 *
		 * @hooked 'editable_roles' filter hook.
		 *
		 * @return array
		 */
		public function wkmp_remove_seller_from_change_role_to( $editable_roles ) {
			global $pagenow, $wkmarketplace;

			$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();

			if ( array_key_exists( 'wk_marketplace_seller', $editable_roles ) && ( ( 'users.php' === $pagenow ) || ( $pro_disabled && 'user-new.php' === $pagenow ) ) ) {
				unset( $editable_roles['wk_marketplace_seller'] );
			}

			if ( 'user-edit.php' === $pagenow && array_key_exists( 'wk_marketplace_seller', $editable_roles ) ) {
				$user_id   = \WK_Caching::wk_get_request_data( 'user_id' );
				$user_data = get_user_by( 'id', $user_id );

				if ( $user_data instanceof \WP_User && ! in_array( 'wk_marketplace_seller', $user_data->roles, true ) ) {
					unset( $editable_roles['wk_marketplace_seller'] );
				}
			}

			return $editable_roles;
		}
	}
}
