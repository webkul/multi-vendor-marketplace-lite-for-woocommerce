<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Profile;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Profile_Info' ) ) {
	/**
	 * Seller products class
	 */
	class WKMP_Profile_Info {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		protected $seller_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Profile_Info constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->seller_id = $seller_id;
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
		 * Profile info.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_section( $seller_id ) {
			global $wkmarketplace;

			$this->seller_id = empty( $seller_id ) ? ( empty( $this->seller_id ) ? get_current_user_id() : $this->seller_id ) : $seller_id;

			$wkmp_notice = get_user_meta( $seller_id, 'wkmp_show_register_notice', true );

			if ( ! empty( $wkmp_notice ) ) {
				wc_print_notice( esc_html( $wkmp_notice ) );
				delete_user_meta( $this->seller_id, 'wkmp_show_register_notice' );
			}

			$is_pending_seller = $wkmarketplace->wkmp_user_is_pending_seller( $this->seller_id );

			if ( $is_pending_seller ) {
				$wkmp_notice = esc_html__( 'Your seller account is under review and will be approved by the admin.', 'wk-marketplace' );
				wc_print_notice( $wkmp_notice, 'notice' );
			}

			if ( $this->seller_id < 1 && 1 === intval( get_option( '_wkmp_separate_seller_registration' ) ) ) {
				wp_enqueue_script( 'wc-password-strength-meter' );
				echo do_shortcode( '[woocommerce_my_account]' );
			}

			if ( $this->seller_id > 0 ) {
				$seller_info = $wkmarketplace->wkmp_get_seller_info( $this->seller_id );

				$avatar_image = ! empty( $seller_info->avatar_image ) ? $seller_info->avatar_image : WKMP_LITE_PLUGIN_URL . 'assets/images/generic-male.png';

				$username     = isset( $seller_info->user_login ) ? esc_html( $seller_info->user_login ) : '';
				$user_email   = isset( $seller_info->user_email ) ? esc_html( $seller_info->user_email ) : '';
				$seller_name  = isset( $seller_info->first_name ) ? $seller_info->first_name : '';
				$seller_name .= isset( $seller_info->last_name ) ? ' ' . $seller_info->last_name : '';
				$display_name = isset( $seller_info->display_name ) ? ' ' . $seller_info->display_name : '';

				if ( empty( $seller_info->ID ) ) {
					$customer_user = get_user_by( 'ID', $this->seller_id );

					if ( $customer_user instanceof \WP_User ) {
						$username     = $customer_user->user_login;
						$user_email   = $customer_user->user_email;
						$seller_name  = $wkmarketplace->wkmp_get_user_display_name( $this->seller_id, $customer_user );
						$display_name = $customer_user->display_name;
					}
				}
				?>
				<div class="woocommerce-account woocommerce">
					<?php do_action( 'mp_get_wc_account_menu' ); ?>
					<div id="main_container" class="woocommerce-MyAccount-content">
						<div class="wkmp_seller_profile_info">

							<div class="wkmp_thumb_image">
								<img src="<?php echo esc_url( $avatar_image ); ?>"/>
							</div>

							<div class="wkmp_profile_info">

								<div class="wkmp_profile_data">
									<label><?php esc_html_e( 'Username', 'wk-marketplace' ); ?></label> :
									<span> <?php echo esc_html( $username ); ?> </span>
								</div>

								<div class="wkmp_profile_data">
									<label><?php esc_html_e( 'E-Mail', 'wk-marketplace' ); ?></label> :
									<span> <a href="mailto:<?php echo esc_attr( $user_email ); ?>"> <?php echo esc_html( $user_email ); ?> </a></span>
								</div>

								<div class="wkmp_profile_data">
									<label><?php esc_html_e( 'Name', 'wk-marketplace' ); ?> </label> :
									<span> <?php echo esc_html( $seller_name ); ?> </span>
								</div>

								<div class="wkmp_profile_data">
									<label><?php esc_html_e( 'Display Name', 'wk-marketplace' ); ?> </label> :
									<span>  <?php echo esc_html( $display_name ); ?> </span>
								</div>

								<?php
								if ( $wkmarketplace->wkmp_user_is_seller( $seller_id ) ) {
									$shopname_visibility = get_option( 'wkmp_shop_name_visibility', 'required' );
									$shopurl_visibility  = get_option( 'wkmp_shop_url_visibility', 'required' );

									if ( 'remove' !== $shopname_visibility ) {
										?>
									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?> </label> :
										<span>  <?php echo isset( $seller_info->shop_name ) ? esc_html( $seller_info->shop_name ) : ''; ?> </span>
									</div>
										<?php
									}
									if ( 'remove' !== $shopurl_visibility ) {
										?>
									<div class="wkmp_profile_data">
										<label><?php esc_html_e( 'Shop address', 'wk-marketplace' ); ?> </label> :
										<span>  <?php echo isset( $seller_info->shop_address ) ? esc_html( $seller_info->shop_address ) : ''; ?> </span>
									</div>
										<?php
									}
								}
								?>

								<div class="wkmp_profile_btn">
									<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . get_option( '_wkmp_profile_endpoint', 'seller-profile' ) ); ?>" title="<?php esc_attr_e( 'Edit Profile', 'wk-marketplace' ); ?>" class="button"><?php esc_html_e( 'Edit', 'wk-marketplace' ); ?></a>
									<?php
									do_action( 'wkmp_after_seller_user_profile_data', $this->seller_id );
									?>
								</div>

							</div>
						</div>

					</div><!-- main_container -->
				</div><!-- woocommerce-account -->
				<?php
			}

			if ( ! $this->seller_id && ! get_option( '_wkmp_separate_seller_registration' ) ) {
				$registration_msg = apply_filters( 'wkmp_seller_registration_message', esc_html__( 'Want to sell your own products...!', 'wk-marketplace' ) );
				?>
				<h3><?php echo esc_html( $registration_msg ); ?></h3><br/>
				<h3>
					<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>"> <?php esc_html_e( 'Login Here', 'wk-marketplace' ); ?> </a> <?php esc_html_e( 'OR', 'wk-marketplace' ); ?>
					<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>">  <?php esc_html_e( 'Register', 'wk-marketplace' ); ?></a>
				</h3>
				<?php
			}
		}
	}
}
