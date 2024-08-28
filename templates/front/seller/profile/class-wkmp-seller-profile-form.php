<?php
/**
 * Seller profile HTML form.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Profile;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Profile_Form' ) ) {
	/**
	 * Seller Profile Edit Form Class.
	 *
	 * Class WKMP_Seller_Profile_Form
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Profile
	 */
	class WKMP_Seller_Profile_Form {
		/**
		 * Marketplace class object.
		 *
		 * @var object $marketplace Marketplace class object.
		 */
		private $marketplace;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Seller_Profile_Form constructor.
		 */
		public function __construct() {
			global $wkmarketplace;
			$this->marketplace = $wkmarketplace;
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
		 * Profile Edit form.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $errors Errors.
		 * @param array $posted_data Posted data.
		 *
		 * @return void
		 */
		public function wkmp_seller_profile_edit_form( $seller_id, $errors, $posted_data ) {
			$seller_info = $this->marketplace->wkmp_get_parsed_seller_info( $seller_id, $posted_data );

			if ( ! empty( $errors ) ) {
				wc_print_notice( esc_html__( 'Warning Please check all the tabs of the form carefully for the errors.', 'wk-marketplace' ), 'error' );
			}

			$tabs = apply_filters(
				'wkmp_seller_front_profile_tabs',
				array(
					'wkmp-general-tab' => esc_html__( 'General', 'wk-marketplace' ),
					'wkmp-shop-tab'    => esc_html__( 'Shop', 'wk-marketplace' ),
					'wkmp-image-tab'   => esc_html__( 'Image', 'wk-marketplace' ),
					'wkmp-social-tab'  => esc_html__( 'Social Profile', 'wk-marketplace' ),
				)
			);

			do_action( 'wkmp_before_seller_profile_form', $seller_info, $posted_data );

			$seller_profile = site_url() . '/' . $this->marketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'seller-store' ) . '/';

			$shopurl_visibility = get_option( 'wkmp_shop_url_visibility', 'required' );
			$no_slug            = true;

			if ( 'remove' !== $shopurl_visibility ) {
				$shop_slug = get_user_meta( $seller_id, 'shop_address', true );
				if ( ! empty( $shop_slug ) ) {
					$seller_profile .= $shop_slug;
					$no_slug         = false;
				}
			}

			if ( $no_slug ) {
				$seller_profile .= $seller_id;
			}

			$profile_data = empty( $posted_data['wkmp_profile_data'] ) ? array() : $posted_data['wkmp_profile_data'];

			$first_name        = empty( $profile_data['first_name'] ) ? $seller_info['wkmp_first_name'] : $profile_data['first_name'];
			$last_name         = empty( $profile_data['last_name'] ) ? $seller_info['wkmp_last_name'] : $profile_data['last_name'];
			$email             = empty( $profile_data['user_email'] ) ? $seller_info['wkmp_seller_email'] : $profile_data['user_email'];
			$shop_name         = empty( $profile_data['shop_name'] ) ? $seller_info['wkmp_shop_name'] : $profile_data['shop_name'];
			$shop_url          = empty( $profile_data['shop_url'] ) ? $seller_info['wkmp_shop_url'] : $profile_data['shop_url'];
			$billing_phone     = empty( $profile_data['billing_phone'] ) ? $seller_info['wkmp_shop_phone'] : $profile_data['billing_phone'];
			$payment_details   = empty( $profile_data['mp_seller_payment_details'] ) ? $seller_info['wkmp_payment_details'] : $profile_data['mp_seller_payment_details'];
			$billing_country   = empty( $profile_data['billing_country'] ) ? $seller_info['wkmp_shop_country'] : $profile_data['billing_country'];
			$billing_address_1 = empty( $profile_data['billing_address_1'] ) ? $seller_info['wkmp_shop_address_1'] : $profile_data['billing_address_1'];
			$billing_address_2 = empty( $profile_data['billing_address_2'] ) ? $seller_info['wkmp_shop_address_2'] : $profile_data['billing_address_2'];
			$billing_city      = empty( $profile_data['billing_city'] ) ? $seller_info['wkmp_shop_city'] : $profile_data['billing_city'];
			$billing_state     = empty( $profile_data['billing_state'] ) ? $seller_info['wkmp_shop_state'] : $profile_data['billing_state'];
			$billing_postcode  = empty( $profile_data['billing_postcode'] ) ? $seller_info['wkmp_shop_postcode'] : $profile_data['billing_postcode'];
			$about_shop        = empty( $profile_data['about_shop'] ) ? $seller_info['wkmp_about_shop'] : $profile_data['about_shop'];
			?>
			<div class="wkmp-table-action-wrap">
				<div class="wkmp-action-section right wkmp-text-right">
					<button type="submit" class="button" form="wkmp-seller-profile"><?php esc_html_e( 'Save', 'wk-marketplace' ); ?></button>&nbsp;&nbsp;
					<a href="<?php echo esc_url( $seller_profile ); ?>" class="button" title="<?php esc_attr_e( 'View Profile', 'wk-marketplace' ); ?>" target="_blank"> <?php esc_html_e( 'View Profile', 'wk-marketplace' ); ?></a>
				</div>
			</div>

			<ul class="wkmp_nav_tabs">
				<?php
				foreach ( $tabs as $tab_id => $tab_title ) {
					?>
					<li><a data-id="#<?php echo esc_attr( $tab_id ); ?>" class="active"><?php echo esc_html( $tab_title ); ?></a></li>
					<?php
				}
				?>
			</ul>

			<form action="" method="post" enctype="multipart/form-data" class="wkmp-form-wrap" id="wkmp-seller-profile">
				<div class="wkmp_tab_content">

					<div id="wkmp-general-tab" class="wkmp_tab_pane">

						<div class="form-group">
							<label for="username"><?php esc_html_e( 'Username', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_username" id="username" value="<?php echo esc_attr( $seller_info['wkmp_username'] ); ?>" readonly>
						</div>

						<div class="form-group">
							<label for="first-name"><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_first_name" id="first-name" value="<?php echo esc_attr( $first_name ); ?>">
							<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_first_name'] ) ? esc_html( $errors['wkmp_first_name'] ) : ''; ?></div>
						</div>

						<div class="form-group">
							<label for="last-name"><?php esc_html_e( 'Last Name', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_last_name" id="last-name" value="<?php echo esc_attr( $last_name ); ?>">
							<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_last_name'] ) ? esc_html( $errors['wkmp_last_name'] ) : ''; ?></div>
						</div>

						<?php do_action( 'wkmp_add_fields_to_general_tab', $seller_info ); ?>

						<div class="form-group">
							<label for="user_email"><?php esc_html_e( 'E-Mail', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_seller_email" id="user_email" value="<?php echo esc_attr( $email ); ?>">
							<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_seller_email'] ) ? esc_html( $errors['wkmp_seller_email'] ) : ''; ?></div>
						</div>

					</div><!-- wkmp-general-tab end here -->

					<div id="wkmp-shop-tab" class="wkmp_tab_pane">

						<div class="form-group">
							<label for="wkmp-shop-name"><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_shop_name" id="wkmp_shop_name" value="<?php echo esc_attr( $shop_name ); ?>">
							<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_shop_name'] ) ? esc_html( $errors['wkmp_shop_name'] ) : ''; ?></div>
						</div>

						<div class="form-group">
							<label for="wkmp-shop-address"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_shop_url" id="wkmp_shop_address" value="<?php echo esc_attr( $shop_url ); ?>" readonly>
						</div>

						<div class="form-group">
							<label for="phone-number"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?></label>
							<input placeholder="<?php esc_attr_e( 'Enter a valid phone number from 4 to 15 characters.', 'wk-marketplace' ); ?>" class="form-control" type="text" name="wkmp_shop_phone" id="phone-number" value="<?php echo esc_attr( $billing_phone ); ?>">
							<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_shop_phone'] ) ? esc_html( $errors['wkmp_shop_phone'] ) : ''; ?></div>
						</div>

						<?php if ( apply_filters( 'wkmp_show_add_payment_options', true, $seller_info ) ) { ?>
							<div class="form-group">
								<label for="mp_seller_payment_details"><?php esc_html_e( 'Payment Information', 'wk-marketplace' ); ?></label>
								<textarea placeholder="<?php esc_attr_e( 'Enter payment information like bank details or Paypal URL to receive payment from the admin after deducting commission.', 'wk-marketplace' ); ?>" rows="4" id="mp_seller_payment_details" name="wkmp_payment_details"><?php echo esc_html( $payment_details ); ?></textarea>
								<?php do_action( 'marketplace_payment_gateway' ); ?>
							</div>
							<?php
						}
						do_action( 'wkmp_after_payment_information_field', $seller_info );
						?>

						<div class="form-group">
							<label for="billing-country"><?php esc_html_e( 'Country', 'wk-marketplace' ); ?></label>
							<select name="wkmp_shop_country" id="billing-country" class="form-control">
								<option value=""><?php esc_html_e( 'Select Country', 'wk-marketplace' ); ?></option>
								<?php
								$countries_obj = new \WC_Countries();
								$countries     = $countries_obj->__get( 'countries' );
								foreach ( $countries as $key => $country ) {
									?>
									<option <?php selected( $key, $billing_country, true ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="address-1"><?php esc_html_e( 'Address Line 1', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_shop_address_1" id="address-1" value="<?php echo esc_attr( $billing_address_1 ); ?>">
						</div>

						<div class="form-group">
							<label for="address-2"><?php esc_html_e( 'Address Line 2', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_shop_address_2" id="address-2" value="<?php echo esc_attr( $billing_address_2 ); ?>">
						</div>

						<div class="form-group">
							<label for="billing-city"><?php esc_html_e( 'City', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_shop_city" id="billing-city" value="<?php echo esc_attr( $billing_city ); ?>">
						</div>

						<div class="form-group">
							<label for="billing-state"><?php esc_html_e( 'State', 'wk-marketplace' ); ?></label>
							<?php
							$get_states = array();
							if ( ! empty( $billing_country ) ) {
								$get_states = $countries_obj->get_states( $billing_country );
							}

							if ( ! empty( $get_states ) && ! empty( $billing_country ) ) {
								?>
								<select name="wkmp_shop_state" id="wkmp_shop_state" class="form-control">
									<option value=""><?php esc_html_e( 'Select state', 'wk-marketplace' ); ?></option>
									<?php foreach ( is_array( $get_states ) ? $get_states : array() as $key => $state ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php echo selected( $key, $billing_state, false ); ?>><?php echo esc_html( $state ); ?></option>
									<?php } ?>
								</select>
							<?php } else { ?>
								<input id="wkmp_shop_state" type="text" name="wkmp_shop_state" class="form-control" value="<?php echo esc_attr( $billing_state ); ?>">
								<?php
							}
							?>
						</div>

						<div class="form-group">
							<label for="billing-postal-code"><?php esc_html_e( 'Postal Code', 'wk-marketplace' ); ?></label>
							<input class="form-control" type="text" name="wkmp_shop_postcode" id="billing-postal-code" value="<?php echo esc_attr( $billing_postcode ); ?>">
							<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_shop_postcode'] ) ? esc_html( $errors['wkmp_shop_postcode'] ) : ''; ?></div>
						</div>

						<div class="form-group">
							<label for="about-shop"><?php esc_html_e( 'About Shop', 'wk-marketplace' ); ?></label>
							<?php
							$settings = array(
								'media_buttons'    => true,
								'textarea_name'    => 'wkmp_about_shop',
								'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
								'tabindex'         => '',
								'editor_class'     => 'backend',
								'teeny'            => false,
								'dfw'              => false,
								'tinymce'          => true,
								'quicktags'        => false,
								'drag_drop_upload' => true,
							);

							$content = '';

							if ( ! empty( $about_shop ) ) {
								$content = html_entity_decode( wp_unslash( $about_shop ) );
							}
							wp_editor( $content, 'wkmp_about_shop', $settings );
							?>
						</div>
						<?php do_action( 'wkmp_after_shop_tab_content', $seller_info, $profile_data ); ?>

					</div><!-- wkmp-shop-tab end here -->

					<div id="wkmp-image-tab" class="wkmp_tab_pane">

						<div class="wkmp_avatar_logo_section">

							<div class="wkmp_profile_img">
								<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_avatar_file'] ) ? esc_attr( $errors['wkmp_avatar_file'] ) : ''; ?></div>
								<label for="seller_avatar_file"><?php esc_html_e( 'User Image', 'wk-marketplace' ); ?></label>

								<div id="wkmp-thumb-image" class="wkmp-img-thumbnail" style="display:table;">
									<img class="wkmp-img-thumbnail" src="<?php echo empty( $seller_info['wkmp_avatar_file'] ) ? esc_url( $seller_info['wkmp_generic_avatar'] ) : esc_url( $seller_info['wkmp_avatar_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_avatar'] ); ?>"/>
									<input type="hidden" id="thumbnail_id_avatar" name="wkmp_avatar_id" value="<?php echo esc_attr( $seller_info['wkmp_avatar_id'] ); ?>"/>
									<input type="file" name="wkmp_avatar_file" class="wkmp_hide" id="seller_avatar_file"/>
								</div>


								<div class="wkmp-button" style="font-size:13px;margin-top:2px;">
									<button type="button" class="button" id="wkmp-upload-profile-image"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
									<button type="button" class="button wkmp-remove-profile-image" style="color:#fff;background-color:#da2020"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
								</div>
							</div>

							<div class="wkmp_profile_logo">
								<div class="wkmp-text-danger"><?php echo isset( $errors['wkmp_logo_file'] ) ? esc_html( $errors['wkmp_logo_file'] ) : ''; ?></div>
								<label for="seller_shop_logo_file"><?php esc_html_e( 'Shop Logo', 'wk-marketplace' ); ?></label>

								<div id="wkmp-thumb-image" class="wkmp-img-thumbnail" style="display:table;">
									<img class="wkmp-img-thumbnail" src="<?php echo empty( $seller_info['wkmp_logo_file'] ) ? esc_url( $seller_info['wkmp_generic_logo'] ) : esc_url( $seller_info['wkmp_logo_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_logo'] ); ?>"/>
									<input type="hidden" id="thumbnail_id_company_logo" name="wkmp_logo_id" value="<?php echo esc_attr( $seller_info['wkmp_logo_id'] ); ?>"/>
									<input type="file" name="wkmp_logo_file" class="wkmp_hide" id="seller_shop_logo_file"/>
								</div>

								<div class="wkmp-button" style="font-size:13px;margin-top:2px;">
									<button type="button" class="button" id="wkmp-upload-shop-logo"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
									<button type="button" class="button wkmp-remove-shop-logo" style="color:#fff;background-color:#da2020"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
								</div>
							</div>

						</div>

						<?php do_action( 'wkmp_before_seller_profile_banner', $seller_id ); ?>

						<div class="form-group">
							<label><b><?php esc_html_e( 'Banner Image', 'wk-marketplace' ); ?></b></label>
							<p>
								<input type="checkbox" id="wk-seller-banner-status" name="wkmp_display_banner" value="yes" <?php echo ( 'yes' === $seller_info['wkmp_display_banner'] ) ? 'checked' : ''; ?>><label for="wk-seller-banner-status"><?php esc_html_e( 'Show banner on seller page', 'wk-marketplace' ); ?> </label>
							</p>

							<div class="wkmp_shop_banner">
								<div class="wkmp-text-danger"><?php echo empty( $errors['wkmp_banner_file'] ) ? '' : esc_html( $errors['wkmp_banner_file'] ); ?></div>

								<div class="wk_banner_img" id="wk_seller_banner">
									<input type="file" class="wkmp_hide" name="wkmp_banner_file" id="wk_mp_shop_banner"/>
									<input type="hidden" id="thumbnail_id_shop_banner" name="wkmp_banner_id" value="<?php echo esc_attr( $seller_info['wkmp_banner_id'] ); ?>"/>
									<img src="<?php echo empty( $seller_info['wkmp_banner_file'] ) ? esc_url( $seller_info['wkmp_generic_banner'] ) : esc_url( $seller_info['wkmp_banner_file'] ); ?>" data-placeholder-url="<?php echo esc_url( $seller_info['wkmp_generic_banner'] ); ?>"/>
								</div>

								<div class="wkmp-shop-banner-buttons">
									<button type="button" class="button wkmp_upload_banner" id="wkmp-upload-seller-banner"><?php esc_html_e( 'Upload', 'wk-marketplace' ); ?></button>
									<button type="button" class="button wkmp_remove_banner" id="wkmp-remove-seller-banner"> <?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></button>
								</div>
							</div>
						</div>

					</div>

					<div id="wkmp-social-tab" class="wkmp_tab_pane">
						<?php do_action( 'wkmp_before_social_tab', $seller_id, $seller_info, $posted_data ); ?>
						<div class="form-group">
							<label for="social-facebok"><?php esc_html_e( 'Facebook Profile ID', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
							<input class="form-control" type="text" name="wkmp_settings[social][fb]" id="social-facebok" value="<?php echo esc_attr( $seller_info['wkmp_facebook'] ); ?>" placeholder="https://">
						</div>

						<div class="form-group">
							<label for="social-instagram"><?php esc_html_e( 'Instagram Profile ID', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
							<input class="form-control" type="text" name="wkmp_settings[social][insta]" id="social-facebok" value="<?php echo esc_attr( $seller_info['wkmp_instagram'] ); ?>" placeholder="https://">
						</div>

						<div class="form-group">
							<label for="social-twitter"><?php esc_html_e( 'X (Formerly Twitter) Profile ID ', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
							<input class="form-control" type="text" name="wkmp_settings[social][twitter]" id="social-twitter" value="<?php echo esc_attr( $seller_info['wkmp_twitter'] ); ?>" placeholder="https://">
						</div>

						<div class="form-group">
							<label for="social-linkedin"><?php esc_html_e( 'Linkedin Profile ID  ', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
							<input class="form-control" type="text" name="wkmp_settings[social][linkedin]" id="social-linkedin" value="<?php echo esc_attr( $seller_info['wkmp_linkedin'] ); ?>" placeholder="https://">
						</div>

						<div class="form-group">
							<label for="social-youtube"><?php esc_html_e( 'Youtube Profile', 'wk-marketplace' ); ?></label><i> <?php esc_html_e( '(optional)', 'wk-marketplace' ); ?></i>
							<input class="form-control" type="text" name="wkmp_settings[social][youtube]" id="social-youtube" value="<?php echo esc_attr( $seller_info['wkmp_youtube'] ); ?>" placeholder="https://">
						</div>

						<?php do_action( 'mp_manage_seller_details', $seller_info ); ?>

					</div>
					<?php do_action( 'wkmp_after_seller_profile_tabs', $seller_info, $tabs, $posted_data ); ?>
				</div><!-- wkmp_tab_content end here -->
				<?php wp_nonce_field( 'wkmp-user-nonce-action', 'wkmp-user-nonce' ); ?>
			</form>
			<?php
		}
	}
}
