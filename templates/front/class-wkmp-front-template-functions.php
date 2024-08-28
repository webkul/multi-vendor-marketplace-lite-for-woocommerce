<?php
/**
 * Front template Functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Templates\Front\Customer;
use WkMarketplace\Helper;

if ( ! class_exists( 'WKMP_Front_Template_Functions' ) ) {
	/**
	 * Front template functions class.
	 *
	 * Class WKMP_Front_Template_Functions
	 *
	 * @package WkMarketplace\Templates\Front
	 */
	class WKMP_Front_Template_Functions {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Front_Template_Functions constructor.
		 */
		public function __construct() {
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
		 * Seller registration fields in form
		 *
		 * @return void
		 */
		public function wkmp_seller_registration_fields() {
			global $wkmarketplace;
			if ( $wkmarketplace->wkmp_allow_seller_registration_fields() ) {
				require __DIR__ . '/wkmp-registration-fields.php';
			}
		}

		/**
		 * Call back method for display seller details on product page.
		 *
		 * @hooked 'woocommerce_single_product_summary' action hook.
		 *
		 * @return void
		 */
		public function wkmp_product_by() {
			global $wkmarketplace;

			$seller_id    = get_the_author_meta( 'ID' );
			$rating       = '';
			$seller_label = apply_filters( 'wkmp_front_seller_label', esc_html__( 'Seller: ', 'wk-marketplace' ) );

			if ( $wkmarketplace->wkmp_user_is_seller( $seller_id ) ) {
				$customer_id = 0;
				$fav_class   = 'wkmp_hide';

				if ( is_user_logged_in() && intval( $seller_id ) !== get_current_user_id() ) {
					$customer_id = get_current_user_id();
					$fav_class   = '';
				}

				$db_obj              = Helper\WKMP_General_Queries::get_instance();
				$favorite_seller_ids = $db_obj->wkmp_get_customer_favorite_seller_ids( $customer_id );

				$heart_icon_class = '';
				if ( in_array( $seller_id, $favorite_seller_ids, true ) ) {
					$heart_icon_class = 'wkmp_active_heart';
				}

				$url = $wkmarketplace->wkmp_get_seller_store_url( $seller_id );
				$url = apply_filters( 'wkmp_single_product_seller_profile_url', $url, $seller_id );

				?>
				<p data-wkmp_seller_id="<?php echo esc_attr( $seller_id ); ?>" class="wkmp-product-author-shop"><?php echo esc_html( $seller_label ); ?><a href="<?php echo esc_url( $url ); ?>"> <?php echo esc_html( ucfirst( get_user_meta( $seller_id, 'shop_name', true ) ) ); ?> </a> <?php echo wp_kses_post( $rating ); ?>
					<span class="<?php echo esc_attr( $fav_class ); ?>" id="wkmp-add-seller-as-favourite" title="<?php esc_attr_e( 'Add As Favourite Seller', 'wk-marketplace' ); ?>">
						<input type="hidden" name="wkmp_seller_id" value="<?php echo esc_attr( $seller_id ); ?>"/>
						<input type="hidden" name="wkmp_customer_id" value="<?php echo esc_attr( $customer_id ); ?>"/>
						<span class="dashicons dashicons-heart <?php echo esc_attr( $heart_icon_class ); ?>"></span>
					</span>
					<span class="wkmp-loader-wrapper"><img class="wp-spin wkmp-spin-loader wkmp_hide" src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>"></span>
				</p>
				<div class="wkmp-confirmation-msg wkmp_hide"></div>
				<?php
			} else {
				echo '<p> ' . esc_html( $seller_label ) . esc_html( ucfirst( get_the_author() ) ) . '</p>';
			}
		}

		/**
		 * Callback method for Add new query var.
		 *
		 * @param array $vars Variables.
		 *
		 * @hooked 'query_vars' action hook.
		 *
		 * @return array
		 */
		public function wkmp_add_query_vars( $vars ) {
			$favorite_seller = get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' );

			$vars[] = $favorite_seller;
			$vars[] = 'main_page';
			$vars[] = 'pagenum';
			$vars[] = 'pagename';
			$vars[] = 'order_id';

			return $vars;
		}

		/**
		 * Insert custom query rules
		 *
		 * @param array $rules Rules.
		 *
		 * @return array
		 */
		public function wkmp_insert_custom_rules( $rules ) {
			global $wkmarketplace;
			$page_name = $wkmarketplace->seller_page_slug;

			$new_rules = array(
				'(.+)/invoice/(.+)/?'          => 'index.php?pagename=$matches[1]&main_page=invoice&order_id=$matches[2]',
				$page_name . '/invoice/(.+)/?' => 'index.php?pagename=' . $page_name . '&main_page=invoice&order_id=$matches[1]',
				$page_name . '/(.+)/?'         => 'index.php?pagename=' . $page_name . '&main_page=$matches[1]',
			);

			$rules = array_merge( $new_rules, $rules );

			return $rules;
		}

		/**
		 * Callback method for Insert new endpoints into the My Account menu.
		 *
		 * @param array $items menu items.
		 *
		 * @hooked 'woocommerce_account_menu_items' filter hook.
		 *
		 * @return array
		 */
		public function wkmp_new_menu_items( $items ) {
			$items  = is_array( $items ) ? $items : array();
			$logout = '';

			if ( isset( $items['customer-logout'] ) ) {
				// Remove the logout menu item.
				$logout = $items['customer-logout'];
				unset( $items['customer-logout'] );
			}

			// Insert your custom endpoint 'favorite-seller'.
			$favorite_seller           = get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' );
			$favorite_title            = get_option( '_wkmp_favorite_seller_endpoint_name', esc_html__( 'My Favorite Sellers', 'wk-marketplace' ) );
			$items[ $favorite_seller ] = $favorite_title;

			if ( ! empty( $logout ) ) {
				// Insert back the logout item.
				$items['customer-logout'] = $logout;
			}

			return $items;
		}

		/**
		 * Callback method for Set endpoint title
		 *
		 * @param string $title Title.
		 *
		 * @return string $title Endpoint title.
		 */
		public function wkmp_endpoint_title( $title ) {
			global $wp_query, $wkmarketplace;

			$customer_id            = get_current_user_id();
			$favorite_seller        = get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' );
			$is_fav_seller_endpoint = isset( $wp_query->query_vars[ $favorite_seller ] );

			if ( $is_fav_seller_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				$title = get_option( '_wkmp_favorite_seller_endpoint_name', esc_html__( 'My Favorite Sellers', 'wk-marketplace' ) );
				remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

				return $title;
			}

			// Seller endpoints title.
			if ( in_the_loop() && $wkmarketplace->wkmp_user_is_seller( $customer_id ) && ! is_admin() && is_main_query() && is_account_page() ) {
				$seller_endpoint = get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_dashboard_endpoint_name', esc_html__( 'Marketplace', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_product_list_endpoint', 'seller-products' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_product_list_endpoint_name', esc_html__( 'Products', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_add_product_endpoint', 'seller-add-product' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_add_product_endpoint_name', esc_html__( 'Add Product', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_edit_product_endpoint_name', esc_html__( 'Edit Product', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );
				}

				$seller_endpoint = get_option( '_wkmp_order_history_endpoint', 'order-history' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_order_history_endpoint_name', esc_html__( 'Order History', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_transaction_endpoint', 'seller-transactions' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_transaction_endpoint_name', esc_html__( 'Transactions', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_profile_endpoint', 'seller-profile' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_profile_endpoint_name', esc_html__( 'My Profile', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_notification_endpoint', 'seller-notifications' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_notification_endpoint_name', esc_html__( 'Notifications', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_shop_follower_endpoint', 'shop-followers' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_shop_follower_endpoint_name', esc_html__( 'Shop Followers', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}

				$seller_endpoint = get_option( '_wkmp_asktoadmin_endpoint', 'seller-ask-admin' );

				if ( isset( $wp_query->query_vars[ $seller_endpoint ] ) ) {
					$title = get_option( '_wkmp_asktoadmin_endpoint_name', esc_html__( 'Ask Admin', 'wk-marketplace' ) );
					remove_filter( 'the_title', array( $this, 'wkmp_endpoint_title' ) );

					return $title;
				}
			}

			return $title;
		}

		/**
		 * Callback method for display Customer favorite seller list.
		 *
		 * @return void
		 */
		public function wkmp_favorite_endpoint_content() {
			new Customer\WKMP_Customer_Favourite_Seller( get_current_user_id() );
		}

		/**
		 * Adding sold by cart item data.
		 *
		 * @param array $item_data Item data.
		 * @param array $cart_item_data Cart item data.
		 *
		 * @hooked 'woocommerce_get_item_data' filter hook.
		 *
		 * @return array
		 */
		public function wkmp_add_sold_by_cart_data( $item_data, $cart_item_data ) {
			global $wkmarketplace;
			$prod_id = isset( $cart_item_data['product_id'] ) ? $cart_item_data['product_id'] : 0;
			if ( $prod_id > 0 ) {
				$author_id    = get_post_field( 'post_author', $prod_id );
				$display_name = get_user_meta( $author_id, 'shop_name', true );

				$display_name = empty( $display_name ) ? get_bloginfo( 'name' ) : $display_name;

				$seller_shop_address = get_user_meta( $author_id, 'shop_address', true );
				$shop_url            = '#';

				if ( empty( $seller_shop_address ) ) {
					$shop_page_id = wc_get_page_id( 'shop' );
					$shop_page    = get_post( $shop_page_id );
					$shop_url     = get_permalink( $shop_page );
				} else {
					$shop_url = $wkmarketplace->wkmp_get_seller_store_url( $author_id );
				}

				if ( ! empty( $author_id ) ) {
					$shop_link   = sprintf( /* translators: %1$s: Shop link, %2$s: Shop Name, %3$s: Closing anchor.  */ esc_html__( '%1$s %2$s %3$s', 'wk-marketplace' ), '<a target="_blank" href="' . esc_url( $shop_url ) . '">', esc_html( $display_name ), '</a>' );
					$item_data[] = array(
						'key'   => esc_html__( 'Sold By ', 'wk-marketplace' ),
						'value' => $shop_link,
					);
				}
			}

			return $item_data;
		}

		/**
		 * Setting dynamic sku on product single page.
		 *
		 * @throws \WC_Data_Exception Throwing exception.
		 */
		public function wkmp_add_seller_prefix_to_sku() {
			global $product;
			if ( $product instanceof \WC_Product ) {
				$author_id = get_post_field( 'post_author', $product->get_id() );
				$author    = get_user_by( 'ID', $author_id );

				if ( $author instanceof \WP_User && in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
					$dynamic_sku_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_dynamic_sku', true );
					$dynamic_sku_prefix  = get_user_meta( $author_id, '_wkmp_dynamic_sku_prefix', true );

					if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
						$product_sku = $product->get_sku();
						$prod_sku    = empty( $product_sku ) ? $product->get_id() : $product_sku;
						$product_sku = $dynamic_sku_prefix . $prod_sku;
						$product->set_sku( $product_sku );
					}
				}
			}
		}

		/**
		 * Resetting sku on product single page.
		 *
		 * @throws \WC_Data_Exception Throwing exception.
		 */
		public function wkmp_remove_seller_prefix_to_sku() {
			global $product;
			if ( $product instanceof \WC_Product ) {
				$author_id = get_post_field( 'post_author', $product->get_id() );
				$author    = get_user_by( 'ID', $author_id );

				if ( $author instanceof \WP_User && in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
					$dynamic_sku_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_dynamic_sku', true );
					$dynamic_sku_prefix  = get_user_meta( $author_id, '_wkmp_dynamic_sku_prefix', true );

					if ( $dynamic_sku_enabled && ! empty( $dynamic_sku_prefix ) ) {
						$product_sku = $product->get_sku();
						if ( ! empty( $product_sku ) && 0 === strpos( $product_sku, $dynamic_sku_prefix ) ) {
							$product_sku = str_replace( $dynamic_sku_prefix, '', $product_sku );
							$product->set_sku( $product_sku );
						}
					}
				}
			}
		}

		/**
		 * Adding 'Sold By' title on 'Add to Cart' button on archive pages.
		 *
		 * @param array             $args Attribute arguments.
		 * @param object|WC_Product $product Product object.
		 *
		 * @return array
		 */
		public function wkmp_add_soldby_on_archive_add_to_cart_button( $args, $product ) {
			if ( ! empty( $args['attributes'] ) && is_array( $args['attributes'] ) && empty( $args['attributes']['title'] ) ) {
				$prod_id   = $product->get_id();
				$seller_id = get_post_field( 'post_author', $prod_id );
				$shop_name = get_user_meta( $seller_id, 'shop_name', true );
				$shop_name = empty( $shop_name ) ? get_bloginfo( 'name' ) : $shop_name;

				$args['attributes']['title'] = wp_sprintf( /* Translators: %s: Shop name.*/ esc_html__( 'Sold By: %s', 'wk-marketplace' ), $shop_name );
			}

			return $args;
		}

		/**
		 * Templates to use in js.
		 *
		 * @hooked 'wp_footer' action action hook.
		 *
		 * @return void
		 */
		public function wkmp_front_footer_templates() {
			?>
			<script id="tmpl-wkmp_field_empty" type="text/html">
				<div class="wkmp-error">
					<p><?php esc_html_e( 'This is required data.', 'wk-marketplace' ); ?></p>
				</div>
			</script>
			<?php
			$show_info = \WK_Caching::wk_get_request_data( 'wkmodule_info', array( 'filter' => 'int' ) );
			$show_info = empty( $show_info ) ? 0 : intval( $show_info );

			if ( 200 === $show_info ) {
				?>
			<input type="hidden" data-lwdt="202408231200" multi-vendor-marketplace-lite-for-woocommerce="<?php echo esc_attr( get_file_data( WKMP_LITE_FILE, array( 'Version' => 'Version' ), false )['Version'] ); ?>">
				<?php
			}
		}
	}
}
