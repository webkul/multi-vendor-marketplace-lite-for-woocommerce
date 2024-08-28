<?php
/**
 * Front functions template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Helper;
use WkMarketplace\Helper\Front;
use WkMarketplace\Helper\Common;
use WkMarketplace\Templates\Front\Seller;

if ( ! class_exists( 'WKMP_Front_Functions' ) ) {
	/**
	 * Front functions class
	 */
	class WKMP_Front_Functions {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Front template handler variable
		 *
		 * @var object
		 */
		protected $template_handler;

		/**
		 * General query helper variable
		 *
		 * @var object
		 */
		protected $query_handler;

		/**
		 * Order query helper variable
		 *
		 * @var object
		 */
		protected $db_obj_order;

		/**
		 * Seller template handler
		 *
		 * @var object
		 */
		protected $seller_template;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Front_Functions constructor.
		 *
		 * @param object $template_handler Front template handler.
		 */
		public function __construct( $template_handler ) {
			global $wpdb;
			$this->wpdb             = $wpdb;
			$this->template_handler = $template_handler;
			$this->query_handler    = Helper\WKMP_General_Queries::get_instance();
			$this->db_obj_order     = Front\WKMP_Order_Queries::get_instance();
			$this->seller_template  = Seller\WKMP_Seller_Template_Functions::get_instance();
		}

		/**
		 * Front Scripts Enqueue
		 *
		 * @hooked 'wp_enqueue_scripts' Action hook.
		 *
		 * @return void
		 */
		public function wkmp_front_scripts() {
			global $wkmarketplace, $wp;

			$suffix     = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? '' : '.min';
			$asset_path = ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) ? 'build' : 'dist';

			$locale        = localeconv();
			$decimal_point = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
			$decimal       = ( ! empty( wc_get_price_decimal_separator() ) ) ? wc_get_price_decimal_separator() : $decimal_point;

			$mkt_tr_arr = array(
				'mkt1'               => esc_html__( 'Please select customer from the list', 'wk-marketplace' ),
				'mkt2'               => esc_html__( 'This field could not be left blank', 'wk-marketplace' ),
				'mkt3'               => esc_html__( 'Please enter a valid product sku, it should be equal or larger than 3 characters', 'wk-marketplace' ),
				'mkt4'               => esc_html__( 'Please Enter SKU', 'wk-marketplace' ),
				'mkt5'               => esc_html__( 'Sale Price must be less than Regular Price.', 'wk-marketplace' ),
				'mkt6'               => esc_html__( 'Invalid Price.', 'wk-marketplace' ),
				'mkt7'               => esc_html__( 'Invalid input.', 'wk-marketplace' ),
				'mkt8'               => esc_html__( 'Please Enter Product Name!!!', 'wk-marketplace' ),
				'mkt9'               => esc_html__( 'First name is not valid', 'wk-marketplace' ),
				'mkt10'              => esc_html__( 'Last name is not valid', 'wk-marketplace' ),
				'mkt11'              => esc_html__( 'E-mail is not valid', 'wk-marketplace' ),
				'mkt12'              => esc_html__( 'Shop name is not valid', 'wk-marketplace' ),
				'mkt13'              => esc_html__( 'Phone number length must not exceed 10.', 'wk-marketplace' ),
				'mkt14'              => esc_html__( 'Phone number not valid.', 'wk-marketplace' ),
				'mkt15'              => esc_html__( 'Field left blank!!!', 'wk-marketplace' ),
				'mkt16'              => esc_html__( 'Seller User Name is not valid', 'wk-marketplace' ),
				'mkt17'              => esc_html__( 'user name available', 'wk-marketplace' ),
				'mkt18'              => esc_html__( 'User Name Already Taken', 'wk-marketplace' ),
				'mkt19'              => esc_html__( 'Cannot Leave Field Blank', 'wk-marketplace' ),
				'mkt20'              => esc_html__( 'Email Id Already Registered', 'wk-marketplace' ),
				'mkt21'              => esc_html__( 'Email address is not valid', 'wk-marketplace' ),
				'mkt22'              => esc_html__( 'select seller option', 'wk-marketplace' ),
				'mkt23'              => esc_html__( 'Seller store name is too short,contain white space or empty', 'wk-marketplace' ),
				'mkt24'              => esc_html__( 'Address is too short or empty', 'wk-marketplace' ),
				'mkt25'              => esc_html__( 'Subject field can not be blank.', 'wk-marketplace' ),
				'mkt26'              => esc_html__( 'Subject not valid.', 'wk-marketplace' ),
				'mkt27'              => esc_html__( 'Ask Your Question (Message length should be less than 500).', 'wk-marketplace' ),
				'mkt28'              => esc_html__( 'Online', 'wk-marketplace' ),
				'mkt29'              => esc_html__( 'Attribute name', 'wk-marketplace' ),
				'mkt30'              => esc_html__( 'Use “|” to separate different options. Enter options for customers to choose from, f.e. “Blue” or “Large”.', 'wk-marketplace' ),
				'mkt31'              => esc_html__( 'Attribute Value eg. a|b|c', 'wk-marketplace' ),
				'mkt32'              => esc_html__( 'Remove', 'wk-marketplace' ),
				'mkt33'              => esc_html__( 'Visible on the product page', 'wk-marketplace' ),
				'mkt34'              => esc_html__( 'Used for variations', 'wk-marketplace' ),
				'mkt35'              => esc_html__( 'Price, Value, Quality rating cannot be empty.', 'wk-marketplace' ),
				'mkt36'              => esc_html__( 'Required field.', 'wk-marketplace' ),
				'mkt37'              => esc_html__( 'Please enter username or email address.', 'wk-marketplace' ),
				'mkt38'              => esc_html__( 'Please enter password.', 'wk-marketplace' ),
				'mkt39'              => esc_html__( 'Please enter username', 'wk-marketplace' ),
				'mkt40'              => esc_html__( 'Warning : Accept only alphanumeric, undescores(-) and hyphens (_) from 3 to 50 characters.', 'wk-marketplace' ),
				'mkt41'              => esc_html__( 'Warning : Message should be 5 to 255 character', 'wk-marketplace' ),
				'mkt42'              => esc_html__( 'Enter a valid numeric amount greater than 0.', 'wk-marketplace' ),
				'mkt43'              => esc_html__( 'Enter minimum amount.', 'wk-marketplace' ),
				'mkt44'              => esc_html__( 'Clear', 'wk-marketplace' ),
				'mkt45'              => esc_html__( 'No Restrictions.', 'wk-marketplace' ),
				'mkt46'              => esc_html__( 'Enable', 'wk-marketplace' ),
				'mkt47'              => esc_html__( 'Enter a positive integer value.', 'wk-marketplace' ),
				'mkt48'              => esc_html__( 'Enter maximum purchasable product quantity.', 'wk-marketplace' ),
				'fajax0'             => esc_html__( 'Are You sure you want to delete this Product..?', 'wk-marketplace' ),
				'fajax1'             => esc_html__( 'Are You sure you want to delete this Seller..?', 'wk-marketplace' ),
				'fajax2'             => esc_html__( 'Are You sure you want to delete this Customer..?', 'wk-marketplace' ),
				'fajax3'             => esc_html__( 'No Sellers Available.', 'wk-marketplace' ),
				'fajax4'             => esc_html__( 'No Followers Available.', 'wk-marketplace' ),
				'fajax5'             => esc_html__( 'There was some issue in process. Please try again.!', 'wk-marketplace' ),
				'fajax6'             => esc_html__( 'Are You sure you want to delete customer(s) from list..?', 'wk-marketplace' ),
				'fajax7'             => esc_html__( 'Select customers to delete from list.', 'wk-marketplace' ),
				'fajax8'             => esc_html__( 'Subject field cannot be empty.', 'wk-marketplace' ),
				'fajax9'             => esc_html__( 'Message field cannot be empty.', 'wk-marketplace' ),
				'fajax10'            => esc_html__( 'Mail Sent Successfully', 'wk-marketplace' ),
				'fajax11'            => esc_html__( 'Error Sending Mail.', 'wk-marketplace' ),
				'fajax12'            => esc_html__( 'Not Available', 'wk-marketplace' ),
				'fajax13'            => esc_html__( 'Already Exists', 'wk-marketplace' ),
				'fajax14'            => esc_html__( 'Available', 'wk-marketplace' ),
				'fajax15'            => esc_html__( 'No Group found', 'wk-marketplace' ),
				'fajax16'            => esc_html__( 'Refund Cancel', 'wk-marketplace' ),
				'fajax17'            => esc_html__( 'Refund', 'wk-marketplace' ),
				'ship2'              => esc_html__( 'Shipping Class Name', 'wk-marketplace' ),
				'ship3'              => esc_html__( 'Cancel changes', 'wk-marketplace' ),
				'ship4'              => esc_html__( 'Slug', 'wk-marketplace' ),
				'ship5'              => esc_html__( 'Description for your reference', 'wk-marketplace' ),
				'ship6'              => esc_html__( 'Are you sure you want to delete this zone?', 'wk-marketplace' ),
				'decimal_separator'  => get_option( 'woocommerce_price_decimal_sep', '.' ),
				'i18n_decimal_error' => sprintf( /* translators: %s: decimal */ __( 'Please enter with one decimal point (%s) without thousand separators.', 'wk-marketplace' ), $decimal ),
				'separate_dashboard' => get_option( '_wkmp_admin_dashboard_endpoint', 'separate-dashboard' ),
			);

			$ajax_obj = array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'ajaxNonce' => wp_create_nonce( 'wkmp-front-nonce' ),
			);

			wp_enqueue_script( 'wkmp-front-script', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/front/js/front' . $suffix . '.js', array( 'select2', 'wp-util' ), WKMP_LITE_SCRIPT_VERSION, true );
			wp_enqueue_script( 'select2-js', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js', array(), WKMP_LITE_SCRIPT_VERSION, true );

			wp_localize_script(
				'wkmp-front-script',
				'wkmpObj',
				array(
					'ajax'                    => $ajax_obj,
					'delete_product_alert'    => esc_html__( 'Are you sure you want to delete these entries?', 'wk-marketplace' ),
					'none_selected'           => esc_html__( 'Please select some data to proceed.', 'wk-marketplace' ),
					'delete_fav_seller_alert' => esc_html__( 'Are you sure you want to delete favorite seller(s)?', 'wk-marketplace' ),
					'mkt_tr'                  => $mkt_tr_arr,
					'wkmp_authorize_error'    => esc_html__( 'You are not authorized to perform this action.', 'wk-marketplace' ),
					'not_purchasable'         => esc_html__( 'Not Purchasable', 'wk-marketplace' ),
				)
			);

			$query_vars = $wp->query_vars;
			$dashboard  = get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' );

			if ( $dashboard && array_key_exists( $dashboard, $query_vars ) ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'mp_chart_script', WKMP_LITE_PLUGIN_URL . 'assets/dist/common/js/chart.umd.js', array(), WKMP_LITE_SCRIPT_VERSION, false );
				wp_enqueue_script( 'mp_graph_loader_script', WKMP_LITE_PLUGIN_URL . 'assets/dist/common/js/loader.js', array(), WKMP_LITE_SCRIPT_VERSION, false );
			}

			wp_dequeue_style( 'bootstrap-css' );
			wp_enqueue_style( 'dashicons' );

			if ( $wkmarketplace->wkmp_is_woocommerce_page() || $wkmarketplace->wkmp_is_seller_page( $query_vars ) ) {
				wp_enqueue_style( 'wkmp-front-style-css', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/front/css/style' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION );

				if ( $wkmarketplace->wkmp_is_seller_page( $query_vars ) ) {
					wp_enqueue_style( 'wkmp-front-style', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/front/css/front' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION );
					wp_enqueue_style( 'select2-css', plugins_url() . '/woocommerce/assets/css/select2.css', array(), WKMP_LITE_SCRIPT_VERSION );
				}
			}

			if ( is_account_page() ) {
				wp_enqueue_style( 'wkmp-account-page-stype', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/front/css/myaccount-style' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION, 'all' );
			}

			$url_data = wp_parse_url( home_url( $wp->request ) );
			$keyword  = $wkmarketplace->seller_page_slug . '/invoice';

			if ( ! empty( $url_data['path'] ) && strpos( $url_data['path'], $keyword ) > 0 ) {
				wp_enqueue_style( 'wkmp-invoice-stype', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/admin/css/invoice-style' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION, 'all' );
			}

			// Theme compatibility CSS.
			if ( in_array( get_template(), array( 'flatsome', 'woodmart' ), true ) ) {
				$rtl = is_rtl() ? '-rtl' : '';
				wp_enqueue_style( 'wkmp-compatibility', WKMP_LITE_PLUGIN_URL . 'assets/' . $asset_path . '/front/css/wkmp-theme-compatibility' . $suffix . '.css', array(), WKMP_LITE_SCRIPT_VERSION );
				if ( 'woodmart' === get_template() ) {
					wp_enqueue_style( 'wkmp-page-my-account', get_template_directory_uri() . '/css/parts/woo-page-my-account' . $rtl . '.min.css', array(), '6.0.3' );
				}
			}
		}

		/**
		 * Seller endpoints.
		 *
		 * @hooked 'init' action Hook.
		 *
		 * @return void
		 */
		public function wkmp_create_wc_seller_endpoints() {
			$endpoints = array(
				get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ),
				get_option( '_wkmp_product_list_endpoint', 'seller-dashboard' ),
				get_option( '_wkmp_add_product_endpoint', 'seller-add-product' ),
				get_option( '_wkmp_edit_product_endpoint', 'seller-edit-product' ),
				get_option( '_wkmp_order_history_endpoint', 'seller-orders' ),
				get_option( '_wkmp_transaction_endpoint', 'seller-transactions' ),
				get_option( '_wkmp_profile_endpoint', 'seller-profile' ),
				get_option( '_wkmp_notification_endpoint', 'seller-notifications' ),
				get_option( '_wkmp_shop_follower_endpoint', 'seller-notifications' ),
				get_option( '_wkmp_asktoadmin_endpoint', 'seller-ask-admin' ),
				get_option( '_wkmp_store_endpoint', 'seller-store' ),
				get_option( '_wkmp_seller_product_endpoint', 'seller-products' ),
				get_option( '_wkmp_feedbacks_endpoint', 'seller-feedbacks' ),
				get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' ),
				get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' ),

			);

			foreach ( $endpoints as $endpoint ) {
				add_rewrite_endpoint( $endpoint, EP_ROOT | EP_PAGES );
			}

			flush_rewrite_rules( false );
		}

		/**
		 * Seller related fields in registration fields.
		 *
		 * @hooked 'woocommerce_register_form' action hook.
		 *
		 * @return void
		 */
		public function wkmp_show_seller_registration_fields() {
			global $wkmarketplace;
			if ( $wkmarketplace->wkmp_allow_seller_registration_fields() ) {
				$this->template_handler->wkmp_seller_registration_fields();
			}
		}

		/**
		 * Validates seller registration form fields.
		 *
		 * @param \WP_Error $error Error.
		 *
		 * @hooked 'woocommerce_process_registration_errors' and 'registration_errors' filter hooks.
		 *
		 * @return \WP_Error
		 */
		public function wkmp_seller_registration_errors( $error ) {
			global $wkmarketplace;

			if ( ! $this->query_handler->wkmp_validate_seller_registration() ) {
				return new \WP_Error( 'max-seller-error', esc_html__( 'New Seller registration is now allowed. Contact administrator to upgrade the Marketplace plugin to pro version.', 'wk-marketplace' ) );
			}

			$args = array( 'method' => 'post' );
			$role = \WK_Caching::wk_get_request_data( 'role', $args );

			if ( 'seller' === $role ) {
				$first_name = \WK_Caching::wk_get_request_data( 'wkmp_firstname', $args );
				$last_name  = \WK_Caching::wk_get_request_data( 'wkmp_lastname', $args );
				$shop_name  = \WK_Caching::wk_get_request_data( 'wkmp_shopname', $args );
				$shop_url   = \WK_Caching::wk_get_request_data( 'wkmp_shopurl', $args );
				$shop_phone = \WK_Caching::wk_get_request_data( 'wkmp_shopphone', $args );

				$term_accepted = \WK_Caching::wk_get_request_data( 'wkmp_seller_signup_term_accept', $args );

				$user                = $wkmarketplace->wkmp_get_seller_id_by_shop_address( $shop_url );
				$seller_term_page_id = get_option( 'wkmp_seller_terms_page_id', 0 );

				if ( empty( $first_name ) ) {
					return new \WP_Error( 'firstname-error', esc_html__( 'Please enter your first name.', 'wk-marketplace' ) );
				}

				if ( empty( $last_name ) ) {
					return new \WP_Error( 'lastname-error', esc_html__( 'Please enter your last name.', 'wk-marketplace' ) );
				}

				if ( $seller_term_page_id > 0 && 'yes' !== $term_accepted ) {
					return new \WP_Error( 'terms-error', esc_html__( 'Please accept sign up terms and conditions.', 'wk-marketplace' ) );
				}

				$shopname_visibility = get_option( 'wkmp_shop_name_visibility', 'required' );

				if ( ! $shop_name && 'required' === $shopname_visibility ) {
					return new \WP_Error( 'shopname-error', esc_html__( 'Please enter your shop name.', 'wk-marketplace' ) );
				}

				$shopurl_visibility = get_option( 'wkmp_shop_url_visibility', 'required' );

				if ( 'remove' !== $shopurl_visibility ) {
					if ( empty( $shop_url ) && 'required' === $shopurl_visibility ) {
						return new \WP_Error( 'shopurl-error', esc_html__( 'Please enter valid shop URL.', 'wk-marketplace' ) );
					} elseif ( ! empty( $shop_url ) && preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $shop_url ) ) {
						return new \WP_Error( 'shopurl-error', esc_html__( 'You can not use special characters in shop url except HYPHEN(-).', 'wk-marketplace' ) );
					} elseif ( ! empty( $shop_url ) && ctype_space( $shop_url ) ) {
						return new \WP_Error( 'shopurl-error', esc_html__( 'White space(s) aren\'t allowed in shop url.', 'wk-marketplace' ) );
					} elseif ( $user ) {
						return new \WP_Error( 'shopurl-error', esc_html__( 'This shop URl already EXISTS, please try different shop url.', 'wk-marketplace' ) );
					}
				}

				if ( empty( $shop_phone ) || ! \WC_Validation::is_phone( $shop_phone ) || strlen( $shop_phone ) < 4 || strlen( $shop_phone ) > 15 ) {
					return new \WP_Error( 'phone-error', esc_html__( 'Please enter a valid phone number of 4 to 15 character', 'wk-marketplace' ) );
				} elseif ( ! preg_match( '/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$/', $shop_phone ) ) {
					return new \WP_Error( 'phone-error', esc_html__( 'Please enter valid phone number.', 'wk-marketplace' ) );
				}
			}

			return $error;
		}

		/**
		 * Inject seller data into WooCommerce registration form data.
		 *
		 * @param array $data Data.
		 *
		 * @hooked 'woocommerce_new_customer_data' filter hook.
		 *
		 * @return $data
		 */
		public function wkmp_new_user_data( $data ) {
			$nonce_value = \WK_Caching::wk_get_request_data( 'woocommerce-register-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_value ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
				$allowed_roles = array( 'customer', 'seller' );

				$role = empty( $_POST['role'] ) ? '' : wc_clean( wp_unslash( $_POST['role'] ) );
				$role = ( ! empty( $role ) && in_array( $role, $allowed_roles, true ) ) ? $role : 'customer';

				if ( 'seller' === $role && $this->query_handler->wkmp_validate_seller_registration() ) {
					$first_name = empty( $_POST['wkmp_firstname'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_firstname'] ) );
					$last_name  = empty( $_POST['wkmp_lastname'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_lastname'] ) );
					$shop_phone = empty( $_POST['wkmp_shopphone'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_shopphone'] ) );
					$register   = empty( $_POST['register'] ) ? '' : wc_clean( wp_unslash( $_POST['register'] ) );
					$shop_url   = empty( $_POST['wkmp_shopurl'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_shopurl'] ) );

					if ( empty( $shop_url ) ) {
						$shop_url = explode( '@', $data['user_login'] );
						$shop_url = preg_replace( '/[^a-zA-Z0-9]+/', '', $shop_url[0] );
					}

					$shop_name = empty( $_POST['wkmp_shopname'] ) ? '' : wc_clean( wp_unslash( $_POST['wkmp_shopname'] ) );

					if ( empty( $shop_name ) && ( ! empty( $first_name ) || ! empty( $last_name ) ) ) {
						$shop_name = trim( $first_name . ' ' . $last_name );
					}

					$data['role']      = $role;
					$data['firstname'] = $first_name;
					$data['lastname']  = $last_name;
					$data['nicename']  = $shop_url;
					$data['storename'] = $shop_name;
					$data['phone']     = $shop_phone;
					$data['register']  = $register;
				}
			}
			return $data;
		}

		/**
		 * Process seller Registration.
		 *
		 * @param int   $user_id New User ID.
		 * @param array $data Data Array.
		 *
		 * @hooked 'woocommerce_created_customer' Action hook.
		 *
		 * @return void
		 *
		 * @throws \Exception Success Message.
		 */
		public function wkmp_process_registration( $user_id, $data ) {
			$nonce_value = \WK_Caching::wk_get_request_data( 'woocommerce-register-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce_value ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) && isset( $data['register'] ) && $this->query_handler->wkmp_validate_seller_registration() ) {
				if ( isset( $data['user_login'] ) && isset( $data['firstname'] ) && isset( $data['lastname'] ) && isset( $data['user_login'] ) && isset( $data['nicename'] ) && isset( $data['storename'] ) ) {
					$user_login   = $data['user_login'];
					$first_name   = $data['firstname'];
					$last_name    = $data['lastname'];
					$user_email   = $data['user_email'];
					$role         = $data['role'];
					$shop_name    = $data['storename'];
					$store_url    = $data['nicename'];
					$sel_phone    = $data['phone'];
					$auto_approve = get_option( '_wkmp_auto_approve_seller', true );

					$data['auto_approve'] = $auto_approve;

					$store_url = empty( $store_url ) ? $user_login : $store_url;

					if ( email_exists( $user_email ) ) {
						$user_data = array(
							'user_nicename' => $store_url,
							'display_name'  => $user_login,
						);
						wp_update_user( $user_data );

						update_user_meta( $user_id, 'first_name', $first_name );
						update_user_meta( $user_id, 'last_name', $last_name );

						if ( ! empty( $role ) && 'customer' !== $role ) {
							$shop_name = empty( $shop_name ) ? $first_name . ' ' . $last_name : $shop_name;
							update_user_meta( $user_id, 'shop_name', $shop_name );
							update_user_meta( $user_id, 'shop_address', $store_url );
							update_user_meta( $user_id, 'billing_phone', $sel_phone );

							$commission_helper = Common\WKMP_Commission::get_instance();

							$this->query_handler->wkmp_set_seller_meta( $user_id );
							$commission_helper->wkmp_set_seller_default_commission( $user_id );
						}
						update_user_meta( $user_id, 'wkmp_show_register_notice', esc_html__( 'Registration complete check your mail for password!', 'wk-marketplace' ) );
					}

					do_action( 'wkmp_registration_details_to_seller', $data );

					do_action(
						'wkmp_new_seller_registration_to_admin',
						array(
							'user_email' => $user_email,
							'user_name'  => $user_login,
							'shop_url'   => $store_url,
						)
					);
				}
			}
		}

		/**
		 * Redirect the user to seller page if logged in user is seller.
		 *
		 * @param string   $redirect Redirect URL.
		 * @param \WP_User $user Logged in user object.
		 *
		 * @hooked 'woocommerce_login_redirect' filter hook.
		 *
		 * @return $redirect
		 */
		public function wkmp_seller_login_redirect( $redirect, $user ) {
			$add_feedback = get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' );
			if ( user_can( $user, 'wk_marketplace_seller' ) && stripos( $redirect, $add_feedback ) < 1 ) {
				$redirect = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' );
			}

			return $redirect;
		}

		/**
		 *  Add seller menu items in my account menu
		 *
		 * @param array $items items array.
		 *
		 * @hooked 'woocommerce_account_menu_items' filter hook.
		 *
		 * @return array $new_items Items array with seller options if seller.
		 */
		public function wkmp_seller_menu_items_my_account( $items ) {
			global $wkmarketplace;
			$user_id   = get_current_user_id();
			$new_items = array();

			if ( $user_id > 0 && $wkmarketplace->wkmp_user_is_seller( $user_id ) ) {
				$new_items[ get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ) ]      = esc_html( get_option( '_wkmp_dashboard_endpoint_name', esc_html__( 'Marketplace', 'wk-marketplace' ) ) );
				$new_items[ get_option( '_wkmp_product_list_endpoint', 'seller-products' ) ]    = esc_html( get_option( '_wkmp_product_list_endpoint_name', esc_html__( 'Products', 'wk-marketplace' ) ) );
				$new_items[ get_option( '_wkmp_order_history_endpoint', 'sellers-orders' ) ]    = esc_html( get_option( '_wkmp_order_history_endpoint_name', esc_html__( 'Order History', 'wk-marketplace' ) ) );
				$new_items[ get_option( '_wkmp_transaction_endpoint', 'seller-transactions' ) ] = esc_html( get_option( '_wkmp_transaction_endpoint_name', esc_html__( 'Transactions', 'wk-marketplace' ) ) );

				$new_items = apply_filters( 'wkmp_pro_woocommerce_account_menu_options', $new_items );

				$new_items[ get_option( '_wkmp_profile_endpoint', 'seller-profile' ) ]            = esc_html( get_option( '_wkmp_profile_endpoint_name', esc_html__( 'My Profile', 'wk-marketplace' ) ) );
				$new_items[ get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) ] = esc_html( get_option( '_wkmp_notification_endpoint_name', esc_html__( 'Notifications', 'wk-marketplace' ) ) );
				$new_items[ get_option( '_wkmp_shop_follower_endpoint', 'shop-followers' ) ]      = esc_html( get_option( '_wkmp_shop_follower_endpoint_name', esc_html__( 'Shop Followers', 'wk-marketplace' ) ) );

				$new_items = apply_filters( 'mp_woocommerce_account_menu_options', $new_items );
				$new_items[ get_option( '_wkmp_asktoadmin_endpoint', 'seller-ask-admin' ) ] = esc_html( get_option( '_wkmp_asktoadmin_endpoint_name', esc_html__( 'Ask Admin', 'wk-marketplace' ) ) );

				$new_items = apply_filters( 'wkmp_pro_wc_account_menu_items', $new_items );

				$new_items += $items;

				return $new_items;
			}

			return $items;
		}

		/**
		 * My account menu icons for seller Dashboard.
		 *
		 * @hooked 'wp_head' Action hook.
		 *
		 * @return mixed
		 */
		public function wkmp_add_dynamic_wc_endpoints_icons() {
			global $wkmarketplace;

			$user_id     = get_current_user_id();
			$seller_info = $wkmarketplace->wkmp_get_seller_info( $user_id );

			if ( $seller_info ) {
				$obj_notification = Common\WKMP_Seller_Notification::get_instance();
				$total_count      = $obj_notification->wkmp_seller_panel_notification_count( get_current_user_id() );
				?>
				<style type="text/css" media="screen">
					<?php
					if ( empty( get_option( '_wkmp_separate_seller_dashboard', false ) ) ) {
						?>
						/** Margin after ask admin if Separate admin dashboard disabled. */
						.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_asktoadmin_endpoint', 'seller-ask-admin' ) ); ?> {
							margin-bottom: 40px;
							border-bottom: 1px solid #ccc !important;
						}
						<?php
					}
					?>
					/**Ask to admin */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_asktoadmin_endpoint', 'seller-asktoadmin' ) ); ?> a:before {
						content: "\e928";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/** Shipping menu */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_shipping_endpoint', 'seller-shippings' ) ); ?> a:before {
						content: "\e95a";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/** Notification menu count */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) ); ?> a:after {
						content: "<?php echo esc_attr( $total_count ); ?>";
						display: inline-block;
						margin-left: 5px;
						color: #fff;
						padding: 0 6px;
						border-radius: 3px;
						line-height: normal;
						vertical-align: middle;
					}

					/**Notification */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_notification_endpoint', 'seller-notifications' ) ); ?> a:before {
						content: "\e90c";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Dashboard */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_dashboard_endpoint', 'seller-dashboard' ) ); ?> a:before {
						content: "\e94e";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Product list */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_product_list_endpoint', 'seller-products' ) ); ?> a:before {
						content: "\e947";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Shop follower */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_shop_follower_endpoint', 'seller-shop-followers' ) ); ?> a:before {
						content: "\e953";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Order history */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_order_history_endpoint', 'sellers-orders' ) ); ?> a:before {
						content: "\e92b";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Transaction */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_transaction_endpoint', 'seller-transactions' ) ); ?> a:before {
						content: "\e925";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}

					/**Profile Edit */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_profile_endpoint', 'seller-profile' ) ); ?> a:before {
						content: "\e960";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
						text-align: center;
					}
				</style>
				<?php
			}

			if ( $user_id > 0 ) {
				?>
				<style>
					/**Favorite Sellers */
					.woocommerce-account .woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--<?php echo esc_attr( get_option( '_wkmp_favorite_seller_endpoint', 'favorite-sellers' ) ); ?> a:before {
						content: "\e932";
						font-family: 'Webkul Rango';
						font-size: 20px;
						font-weight: normal;
					}
				</style>
				<?php
			}
		}

		/**
		 * My account menu for seller pages
		 */
		public function wkmp_return_wc_account_menu() {
			wc_print_notices();
			?>
			<nav class="woocommerce-MyAccount-navigation">
				<ul class="wkmp-account-nav wkmp-nav-vertical">
					<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
						<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
							<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php
		}

		/**
		 * Call seller sub pages in seller page shortcode.
		 *
		 * @hooked 'wp' action hook.
		 *
		 * @return void
		 */
		public function wkmp_call_seller_pages() {
			global $wkmarketplace;

			$seller_id    = get_current_user_id();
			$seller_info  = $wkmarketplace->wkmp_get_seller_info( $seller_id );
			$store        = get_option( '_wkmp_store_endpoint', 'seller-store' );
			$products     = get_option( '_wkmp_seller_product_endpoint', 'seller-products' );
			$feedbacks    = get_option( '_wkmp_feedbacks_endpoint', 'seller-feedbacks' );
			$add_feedback = get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' );

			$main_page = get_query_var( 'main_page' );

			if ( ! $seller_info && ( false !== strpos( $main_page, $store ) || false !== strpos( $main_page, $products ) || false !== strpos( $main_page, $feedbacks ) || false !== strpos( $main_page, $add_feedback ) ) ) {
				$query_vars = explode( '/', $main_page );
				$seller_id  = ( is_array( $query_vars ) && count( $query_vars ) > 1 ) ? $query_vars[1] : 0;
				if ( ! is_numeric( $seller_id ) || ( is_numeric( $seller_id ) && empty( get_user_by( 'ID', $seller_id ) ) ) ) {
					$seller_id = $wkmarketplace->wkmp_get_seller_id_by_shop_address( $seller_id );
				}
				$seller_info = $wkmarketplace->wkmp_get_seller_info( $seller_id );
			}

			$page_name = get_query_var( 'pagename' );

			if ( ! empty( $page_name ) && $seller_info && $page_name === $wkmarketplace->seller_page_slug ) {
				$template_seller_id = $this->seller_template->get_seller_id();
				if ( $template_seller_id !== $seller_id ) {
					$this->seller_template->set_seller_id( $seller_id );
				}

				if ( 'invoice' === $main_page ) {
					$this->seller_template->wkmp_seller_order_invoice( $seller_id );
				} elseif ( false !== strpos( $main_page, $store ) ) {
					add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_store_info' ) );
				} elseif ( false !== strpos( $main_page, $products ) ) {
					add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_products_info' ) );
				} elseif ( false !== strpos( $main_page, $add_feedback ) ) {
					add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_add_feedback' ) );
				} elseif ( false !== strpos( $main_page, $feedbacks ) ) {
					add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_all_feedback' ) );
				} else {
					add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_profile_info' ) );
				}
			} elseif ( ! empty( $page_name ) && $page_name === $wkmarketplace->seller_page_slug ) {
				add_shortcode( 'marketplace', array( $this->seller_template, 'wkmp_seller_profile_info' ) );
			}
		}

		/**
		 * Clearing shipping packages.
		 */
		public function wkmp_clear_shipping_session() {
			if ( ! empty( WC()->session ) ) {
				WC()->session->__unset( 'shipping_for_package_0' );
			}
		}

		/**
		 * New Order map seller.
		 *
		 * @param int       $order_id Order id.
		 * @param array     $posted_data Posted data.
		 * @param \WC_Order $order Order object.
		 *
		 * @hooked woocommerce_checkout_order_processed.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_new_order_map_seller( $order_id, $posted_data = array(), $order = '' ) {

			if ( ! $order instanceof \WC_Order && is_numeric( $order_id ) ) {
				$order = wc_get_order( $order_id );
			}

			wkmp_wc_log( "Checkout order processed for order id: $order_id" );

			$items        = $order->get_items();
			$author_array = array();

			foreach ( $items as $item ) {
				$assigned_seller = wc_get_order_item_meta( $item->get_id(), 'assigned_seller', true );
				$assigned_seller = empty( $assigned_seller ) ? get_post_field( 'post_author', $item->get_product_id() ) : $assigned_seller;

				if ( ! in_array( $assigned_seller, $author_array, true ) ) {
					$author_array[] = $assigned_seller;
				}
			}

			$author_array = array_unique( $author_array );

			if ( ! $this->db_obj_order ) {
				$this->db_obj_order = Front\WKMP_Order_Queries::get_instance();
			}

			$this->db_obj_order->wkmp_update_seller_orders( $author_array, $order_id );

			$this->wkmp_add_order_commission_data( $order_id );
		}

		/**
		 * Add order commission data.
		 *
		 * @param int $order_id Order id.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_add_order_commission_data( $order_id ) {
			if ( ! $this->db_obj_order->wkmp_check_seller_order_exists( $order_id ) ) {
				$order         = wc_get_order( $order_id );
				$items         = $order->get_items();
				$mp_commission = Common\WKMP_Commission::get_instance();

				foreach ( $items as $item ) {
					$item_id         = $item->get_id();
					$assigned_seller = wc_get_order_item_meta( $item_id, 'assigned_seller', true );

					$tax_total   = 0;
					$amount      = floatval( $item['line_total'] );
					$product_qty = intval( $item['quantity'] );

					$product_id      = empty( $item['variation_id'] ) ? $item['product_id'] : $item['variation_id'];
					$commission_data = $mp_commission->wkmp_calculate_product_commission( $product_id, $product_qty, $amount, $assigned_seller, $tax_total );

					$seller_id        = $commission_data['seller_id'];
					$discount_applied = number_format( (float) ( $item->get_subtotal() - $item->get_total() ), 2, '.', '' );
					$admin_amount     = $commission_data['admin_commission'];
					$seller_amount    = $commission_data['seller_amount'];
					$comm_applied     = $commission_data['commission_applied'];
					$comm_type        = $commission_data['commission_type'];

					$data = apply_filters(
						'wkmp_calculate_seller_product_commission',
						array(
							'order_id'           => $order_id,
							'product_id'         => $product_id,
							'seller_id'          => $seller_id,
							'amount'             => number_format( $amount, 2, '.', '' ),
							'admin_amount'       => number_format( (float) $admin_amount, 2, '.', '' ),
							'seller_amount'      => number_format( (float) $seller_amount, 2, '.', '' ),
							'quantity'           => $product_qty,
							'commission_applied' => number_format( (float) $comm_applied, 2, '.', '' ),
							'discount_applied'   => $discount_applied,
							'commission_type'    => $comm_type,
						)
					);

					$this->db_obj_order->wkmp_insert_mporders_data( $data );
				}

				$chosen_ship_method = '';
				$ship_sess          = '';

				if ( ! empty( WC()->session ) ) {
					// Shipping calculation.
					$chosen_ship_method = WC()->session->get( 'chosen_shipping_methods', array() );
					$ship_sess          = WC()->session->get( 'shipping_sess_cost', array() );
				}

				$shipping_cost_list = $ship_sess;

				if ( ! empty( WC()->session ) && ! empty( WC()->session->get( 'shipping_cost_list' ) ) ) {
					$shipping_cost_list = WC()->session->get( 'shipping_cost_list' );
				}

				$ship_sess = ( ! empty( $chosen_ship_method ) && is_iterable( $chosen_ship_method ) && count( $chosen_ship_method ) > 0 && isset( $shipping_cost_list[ $chosen_ship_method[0] ] ) ) ? $shipping_cost_list[ $chosen_ship_method[0] ] : '';
				$ship_sess = apply_filters( 'wk_mp_modify_shipping_session', $ship_sess, $order_id );

				if ( ! empty( WC()->session ) ) {
					WC()->session->__unset( 'shipping_sess_cost' );
					WC()->session->__unset( 'shipping_cost_list' );
				}

				$ship_cost = 0;

				if ( ! empty( $ship_sess ) ) {
					foreach ( $ship_sess as $sel_id => $sel_detail ) {
						$sel_id     = apply_filters( 'wkmp_shipping_session_seller_id', $sel_id );
						$ship_title = empty( $sel_detail['title'] ) ? '' : $sel_detail['title'];

						if ( in_array( $ship_title, $chosen_ship_method, true ) ) {
							$shiping_cost = empty( $sel_detail['cost'] ) ? 0 : $sel_detail['cost'];
							$shiping_cost = number_format( (float) $shiping_cost, 2, '.', '' );
							$ship_cost    = $ship_cost + $shiping_cost;

							$push_arr = array(
								'shipping_method_id' => $ship_title,
								'shipping_cost'      => $shiping_cost,
							);

							foreach ( $push_arr as $key => $value ) {
								$insert = array(
									'seller_id'  => $sel_id,
									'order_id'   => $order_id,
									'meta_key'   => $key,
									'meta_value' => $value,
								);
								$this->db_obj_order->wkmp_insert_mporders_meta_data( $insert );
							}
						}
					}
				}

				$coupon_detail = empty( WC()->cart ) ? array() : WC()->cart->get_coupons();

				if ( ! empty( $coupon_detail ) ) {
					foreach ( $coupon_detail as $coupon_code => $coupon_data ) {
						if ( $coupon_data instanceof \WC_Coupon ) {
							$coupon_author = get_post_field( 'post_author', $coupon_data->id );

							$insert = array(
								'seller_id'  => $coupon_author,
								'order_id'   => $order_id,
								'meta_key'   => 'discount_code',
								'meta_value' => $coupon_code,
							);

							$this->db_obj_order->wkmp_insert_mporders_meta_data( $insert );
						}
					}
				}

				if ( 'yes' !== $order->get_meta( '_wkmpsplit_order', true ) ) {
					$mp_commission = Common\WKMP_Commission::get_instance();
					$mp_commission->wkmp_update_seller_order_info( $order_id );
				}
			}
		}

		/**
		 * Seller collection pagination
		 *
		 * @param int $max_num_pages max page count.
		 */
		public function wkmp_seller_collection_pagination( $max_num_pages ) {
			if ( $max_num_pages > 1 ) {
				$store_paged = get_query_var( 'pagenum' );

				if ( empty( $store_paged ) ) {
					$main_page   = get_query_var( 'main_page' );
					$query_vars  = explode( '/', $main_page );
					$store_paged = ( is_array( $query_vars ) && count( $query_vars ) > 3 && 'page' === $query_vars[2] ) ? $query_vars[3] : 1;
				}
				?>
				<nav class="woocommerce-pagination">
				<?php
				echo wp_kses_post(
					paginate_links(
						apply_filters(
							'woocommerce_pagination_args',
							array(
								'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
								'format'    => '',
								'add_args'  => false,
								'current'   => max( 1, $store_paged ),
								'total'     => $max_num_pages,
								'prev_text' => '&larr;',
								'next_text' => '&rarr;',
								'type'      => 'list',
								'end_size'  => 3,
								'mid_size'  => 3,
							)
						)
					)
				);
				?>
				</nav>
					<?php
			}
		}

		/**
		 * Function to redirect seller.
		 */
		public function wkmp_redirect_seller_tofront() {
			global $wkmarketplace;

			$current_user = wp_get_current_user();
			$role_name    = $current_user->roles;
			$sep_dash     = get_user_meta( $current_user->ID, 'wkmp_seller_backend_dashboard', true );

			$page_name     = $wkmarketplace->seller_page_slug;
			$allowed_pages = array(
				get_option( '_wkmp_store_endpoint', 'seller-store' ),
				get_option( '_wkmp_profile_endpoint', 'seller-profile' ),
				get_option( '_wkmp_feedbacks_endpoint', 'seller-feedbacks' ),
				get_option( '_wkmp_add_feedback_endpoint', 'add-feedback' ),
				get_option( '_wkmp_seller_product_endpoint', 'seller-products' ),
			);

			if ( get_option( '_wkmp_separate_seller_dashboard', false ) && ! empty( $sep_dash ) && in_array( 'wk_marketplace_seller', $role_name, true ) && ( get_query_var( 'pagename' ) === $page_name ) && ! in_array( get_query_var( 'main_page' ), $allowed_pages, true ) ) {
				if ( ! is_admin() ) {
					$wkmarketplace->wkmp_add_role_cap( $current_user->ID );
					wp_safe_redirect( esc_url( admin_url( 'admin.php?page=seller' ) ) );
					exit;
				}
			} elseif ( empty( get_option( '_wkmp_separate_seller_dashboard', false ) ) || empty( $sep_dash ) && ! in_array( get_query_var( 'main_page' ), $allowed_pages, true ) ) {
				$wkmarketplace->wkmp_remove_role_cap( $current_user->ID );

				$php_self = isset( $_SERVER['PHP_SELF'] ) ? wc_clean( $_SERVER['PHP_SELF'] ) : '';

				if ( defined( 'DOING_AJAX' ) || '/wp-admin/async-upload.php' === $php_self ) {
					return;
				}

				if ( in_array( 'wk_marketplace_seller', $role_name, true ) && is_admin() ) {
					wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
					exit;
				}
			}
		}

		/**
		 * Showing success notice on completing seller registration.
		 *
		 * @hooked 'woocommerce_account_navigation' Action hook
		 */
		public function wkmp_show_register_success_notice() {
			global $wkmarketplace;
			$user_id     = get_current_user_id();
			$wkmp_notice = get_user_meta( $user_id, 'wkmp_show_register_notice', true );
			if ( ! empty( $wkmp_notice ) ) {
				wc_print_notice( esc_html( $wkmp_notice ) );
				delete_user_meta( $user_id, 'wkmp_show_register_notice' );
			}

			if ( empty( $wkmp_notice ) ) {
				$is_pending_seller = $wkmarketplace->wkmp_user_is_pending_seller( $user_id );

				if ( $is_pending_seller ) {
					$wkmp_notice = esc_html__( 'Your seller account is under review and will be approved by the admin.', 'wk-marketplace' );
					wc_print_notice( $wkmp_notice, 'notice' );
				}
			}
		}

		/**
		 * Adding seller profile link with each order item.
		 *
		 * @param \WC_Order_Item_Product $item Order item object.
		 * @param string                 $cart_item_key Cart Item key.
		 * @param array                  $values array of values.
		 * @param \WC_Order              $order Order object.
		 *
		 * @hooked 'woocommerce_checkout_create_order_line_item' Action link.
		 */
		public function wkmp_add_sold_by_order_item_meta( $item, $cart_item_key, $values, $order ) {
			$prod_id = isset( $values['product_id'] ) ? $values['product_id'] : 0;
			if ( $prod_id > 0 ) {
				$author_id = get_post_field( 'post_author', $prod_id );
				$item->update_meta_data( 'Sold By', 'wkmp_seller_id=' . $author_id );
			}
		}

		/**
		 * Validate and show notice for minimum order amount on cart.
		 *
		 * @hooked woocommerce_checkout_process
		 */
		public function wkmp_validate_minimum_order_amount() {
			$threshold_notes = $this->is_threshold_reached();
			$qty_notes       = $this->is_qty_allowed();

			if ( count( $threshold_notes ) > 0 ) {
				$this->show_invalid_order_total_notice( $threshold_notes );
			}

			if ( count( $qty_notes ) > 0 ) {
				$this->show_invalid_qty_notice( $qty_notes );
			}

			if ( count( $threshold_notes ) > 0 || count( $qty_notes ) > 0 ) {
				remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
			}
		}

		/**
		 * Validating order total on checkout.
		 *
		 * @hooked woocommerce_checkout_update_order_review
		 */
		public function wkmp_validate_minimum_order_amount_checkout() {
			$threshold_notes = $this->is_threshold_reached();
			$qty_notes       = $this->is_qty_allowed();

			if ( count( $threshold_notes ) > 0 ) {
				$this->show_invalid_order_total_notice( $threshold_notes );
			}

			if ( count( $qty_notes ) > 0 ) {
				$this->show_invalid_qty_notice( $qty_notes );
			}
		}

		/**
		 * Removing 'Place Order' button from checkout if order total doesn't exceed threshold amount.
		 *
		 * @hooked woocommerce_order_button_html
		 *
		 * @param string $order_button Order button.
		 *
		 * @return mixed|string
		 */
		public function wkmp_remove_place_order_button( $order_button ) {
			if ( count( $this->is_threshold_reached() ) > 0 || count( $this->is_qty_allowed() ) > 0 ) {
				$order_button = '';
			}

			return $order_button;
		}

		/**
		 * Validate minimum order total for products.
		 *
		 * @param object $cart Cart Object.
		 *
		 * @return array|false
		 */
		public function is_threshold_reached( $cart = '' ) {
			$minimum_enabled = get_option( '_wkmp_enable_minimum_order_amount', 0 );
			$threshold_notes = array();

			if ( ! $minimum_enabled ) {
				return $threshold_notes;
			}

			$seller_totals = array();
			$cart          = empty( $cart ) ? WC()->cart->get_cart() : $cart;

			foreach ( $cart as $item ) {
				$sell_product_id = isset( $item['product_id'] ) ? $item['product_id'] : 0;
				$sell_product_id = ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) ? $item['variation_id'] : $sell_product_id;

				if ( $sell_product_id > 0 ) {
					$author_id  = get_post_field( 'post_author', $sell_product_id );
					$author     = get_user_by( 'ID', $author_id );
					$item_total = $item['line_subtotal'] + $item['line_tax'];
					if ( $author instanceof \WP_User && in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
						if ( isset( $seller_totals[ $author_id ] ) ) {
							$seller_totals[ $author_id ] += $item_total;
						} else {
							$seller_totals[ $author_id ] = $item_total;
						}
					} else {
						$seller_totals['admin']  = isset( $seller_totals['admin'] ) ? $seller_totals['admin'] : 0;
						$seller_totals['admin'] += $item_total;
					}
				}
			}

			$minimum_amount                  = get_option( '_wkmp_minimum_order_amount', 0 );
			$seller_min_amount_admin_default = get_option( '_wkmp_seller_min_amount_admin_default', false );

			foreach ( $seller_totals as $seller_id => $seller_total ) {
				if ( 'admin' === $seller_id ) {
					if ( $seller_totals['admin'] < $minimum_amount ) {
						$threshold_notes['admin'] = array(
							'min_amount'    => $minimum_amount,
							'current_total' => $seller_totals['admin'],
						);
					}
				} else {
					$minimum_seller_amount = get_user_meta( $seller_id, '_wkmp_minimum_order_amount', true );
					$minimum_sell_amount   = empty( $minimum_seller_amount ) ? ( $seller_min_amount_admin_default ? $minimum_amount : 0 ) : $minimum_seller_amount;
					if ( $seller_totals[ $seller_id ] < $minimum_sell_amount ) {
						$threshold_notes[ $seller_id ] = array(
							'min_amount'    => $minimum_sell_amount,
							'current_total' => $seller_totals[ $seller_id ],
						);
					}
				}
			}

			return $threshold_notes;
		}

		/**
		 * Check if products quantities are allowed to purchased.
		 *
		 * @param object $cart Cart Items object.
		 *
		 * @return array
		 */
		public function is_qty_allowed( $cart = '' ) {
			$max_qty_enabled = get_option( '_wkmp_enable_product_qty_limit', 0 );
			$qty_notes       = array();

			if ( ! $max_qty_enabled ) {
				return $qty_notes;
			}

			$cart = empty( $cart ) ? WC()->cart->get_cart() : $cart;

			foreach ( $cart as $item ) {
				$sell_product_id = isset( $item['product_id'] ) ? $item['product_id'] : 0;

				if ( $sell_product_id > 0 ) {
					$sell_product_obj = wc_get_product( $sell_product_id );

					if ( $sell_product_obj->get_sold_individually() ) {
						continue;
					}

					$qty_limit   = get_post_meta( $sell_product_id, '_wkmp_max_product_qty_limit', true );
					$product_qty = isset( $item['quantity'] ) ? $item['quantity'] : 0;

					if ( empty( $qty_limit ) ) {
						$author_id = get_post_field( 'post_author', $sell_product_id );
						$author    = get_user_by( 'ID', $author_id );
						if ( $author instanceof \WP_User && in_array( 'wk_marketplace_seller', $author->roles, true ) ) {
							$qty_limit = get_user_meta( $author_id, '_wkmp_max_product_qty_limit', true );
						}
						$qty_limit = empty( $qty_limit ) ? get_option( '_wkmp_max_product_qty_limit', true ) : $qty_limit;
					}

					if ( $qty_limit > 0 && $product_qty > $qty_limit ) {
						$qty_notes[ $sell_product_id ] = $qty_limit;
					}
				}
			}

			return $qty_notes;
		}

		/**
		 * Showing notices when order total is less than threshold value.
		 *
		 * @param array  $notes Seller notes.
		 * @param string $type Notice type, show or return.
		 */
		public function show_invalid_order_total_notice( $notes, $type = '' ) {
			$messages = array();

			foreach ( $notes as $seller_id => $min_data ) {
				$minimum_amount = isset( $min_data['min_amount'] ) ? $min_data['min_amount'] : 0;
				$current_total  = isset( $min_data['current_total'] ) ? $min_data['current_total'] : 0;
				$seller_name    = ( 'admin' === $seller_id ) ? 'Admin' : get_user_meta( $seller_id, 'shop_name', true );

				$message = wp_sprintf( /* translators: %1$s: Shop name, %2$s: Minimum amount, %3$s: Current total. */ esc_html__( 'Minimum products total for %1$s Shop product(s) should be %2$s. Current total (inclusive tax) is: %3$s.', 'wk-marketplace' ), '<strong>' . $seller_name . '</strong>', wc_price( $minimum_amount ), wc_price( $current_total ) );

				if ( 'get' === $type ) {
					$messages[ $seller_name ] = $message;
				} elseif ( is_cart() ) {
					wc_print_notice( $message, 'error' );
				} else {
					wc_add_notice( $message, 'error' );
				}
			}

			if ( 'get' === $type ) {
				return $messages;
			}
		}

		/**
		 * Showing notices when product quantity is greater than threshold value.
		 *
		 * @param array  $notes Qty notes.
		 * @param string $type Notice type print or return.
		 *
		 * @return array
		 */
		public function show_invalid_qty_notice( $notes, $type = '' ) {
			$messages = array();

			foreach ( $notes as $prod_id => $max_allowed_qty ) {
				$cart_product = wc_get_product( $prod_id );
				$message      = wp_sprintf( /* translators: %1$s: Shop name, %2$s: Minimum amount. */ esc_html__( 'Sorry, but you can only add maximum %1$s quantity of %2$s in this cart.', 'wk-marketplace' ), '<strong>' . $max_allowed_qty . '</strong>', '<strong>' . $cart_product->get_title() . '</strong>' );

				if ( 'get' === $type ) {
					$messages[ 'product_id_' . $prod_id ] = $message;
				} elseif ( is_cart() ) {
					wc_print_notice( $message, 'error' );
				} else {
					wc_add_notice( $message, 'error' );
				}
			}

			if ( 'get' === $type ) {
				return $messages;
			}
		}

		/**
		 * Adding woocommerce-account class on seller pages.
		 *
		 * @param array $classes Body classes.
		 *
		 * @hooked body_class.
		 *
		 * @return array|mixed
		 */
		public function wkmp_add_body_class( $classes ) {
			global $wkmarketplace;
			if ( $wkmarketplace->wkmp_is_seller_page() ) {
				$user_id = get_current_user_id();
				if ( $user_id > 0 && $wkmarketplace->wkmp_user_is_seller( $user_id ) ) {
					$classes   = is_array( $classes ) ? $classes : array();
					$classes[] = 'woocommerce-account wkmp-seller-endpoints';
				}
			}

			return $classes;
		}

		/**
		 * WC Active menu class.
		 *
		 * @param array  $classes Menu classes.
		 * @param string $endpoint Endpoint.
		 *
		 * @return array
		 */
		public function wkmp_wc_menu_active_class( $classes, $endpoint ) {
			global $wp;

			$classes = is_array( $classes ) ? $classes : array();

			// Set current item class.
			$current = isset( $wp->query_vars[ $endpoint ] );

			if ( isset( $wp->query_vars['main_page'] ) && strpos( $endpoint, $wp->query_vars['main_page'] ) > 0 ) {
				$current = true;
			}

			if ( $current ) {
				$classes[] = 'is-active';
			}

			return $classes;
		}

		/**
		 * Remove Shortcode form All in One SEO Plugin's shortcode list to avoid conflicts.
		 *
		 * @param array $shortcodes Shortcode.
		 *
		 * @return array
		 */
		public function wkmp_remove_mp_shortcode_from_aioseo_shortcode_lists( $shortcodes ) {
			$shortcodes = is_array( $shortcodes ) ? $shortcodes : array();

			$shortcodes['Marketplace'] = 'marketplace';

			return $shortcodes;
		}

		/**
		 * Get cart validation error messages.
		 *
		 * @param object $cart Cart item.
		 *
		 * @return array
		 */
		public function wkmp_get_cart_validation_error_messages( $cart ) {
			$cart_itmes = $cart->get_cart_contents();

			$threshold_notes = $this->is_threshold_reached( $cart_itmes );
			$messages        = $this->show_invalid_order_total_notice( $threshold_notes, 'get' );

			$qty_notes = $this->is_qty_allowed( $cart_itmes );
			$qty_msgs  = $this->show_invalid_qty_notice( $qty_notes, 'get' );

			return array_merge( $messages, $qty_msgs );
		}
	}
}
