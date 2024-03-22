<?php
/**
 * Seller registration fields template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$args = array( 'method' => 'post' );

$wkmp_firstname = \WK_Caching::wk_get_request_data( 'wkmp_firstname', $args );
$wkmp_lastname  = \WK_Caching::wk_get_request_data( 'wkmp_lastname', $args );
$wkmp_shopname  = \WK_Caching::wk_get_request_data( 'wkmp_shopname', $args );
$wkmp_shopurl   = \WK_Caching::wk_get_request_data( 'wkmp_shopurl', $args );
$wkmp_shopphone = \WK_Caching::wk_get_request_data( 'wkmp_shopphone', $args );

$args['default'] = 'customer';
$user_role       = \WK_Caching::wk_get_request_data( 'role', $args );

$role_style = ( 'customer' === $user_role ) ? ' style=display:none' : '';
if ( ! is_account_page() ) {
	$role_style = 'style=display:block';
}
?>
<div class="wkmp-seller-registration-fields">
	<div class="wkmp-show-fields-if-seller" <?php echo esc_attr( $role_style ); ?>>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-firstname"><?php esc_html_e( 'First Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_firstname" value="<?php echo esc_attr( $wkmp_firstname ); ?>" id="wkmp-firstname"/>
			<div class="wkmp-error-class" id="wkmp-seller-firstname-error"></div>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-lastname"><?php esc_html_e( 'Last Name', 'wk-marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text form-control" name="wkmp_lastname" value="<?php echo esc_attr( $wkmp_lastname ); ?>" id="wkmp-lastname"/>
			<div class="wkmp-error-class" id="wkmp-seller-lastname-error"></div>
		</p>
		<?php

		$shopname_visibility = get_option( 'wkmp_shop_name_visibility', 'required' );

		if ( 'remove' !== $shopname_visibility ) {
			?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="wkmp-shopname"><?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?><?php echo ( 'required' === $shopname_visibility ) ? '<span class="required">*</span>' : ''; ?>
				<?php if ( 'required' !== $shopname_visibility ) { ?>
					<span class="wkmp-front-wc-help-tooltip help">
						<span class="wkmp-help-tip-sol"><?php esc_html_e( 'If empty, First and Last name will be the Shop Name.', 'wk-marketplace' ); ?></span>
						<span class="help-tip"></span>
					</span>
				<?php } ?>
				</label>
				<input data-is_optional="<?php echo ( 'required' !== $shopname_visibility ) ? true : false; ?>" type="text" class="input-text form-control" name="wkmp_shopname" value="<?php echo esc_attr( $wkmp_shopname ); ?>" id="wkmp-shopname"/>
			</p>
			<?php
		}

		$shopurl_visibility = get_option( 'wkmp_shop_url_visibility', 'required' );

		if ( 'remove' !== $shopurl_visibility ) {
			?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="wkmp-shopurl" class="pull-left"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?> <?php echo ( 'required' === $shopurl_visibility ) ? '<span class="required">*</span>' : ''; ?>
				<?php if ( 'required' !== $shopurl_visibility ) { ?>
					<span class="wkmp-front-wc-help-tooltip help">
						<span class="wkmp-help-tip-sol"><?php esc_html_e( 'If empty, your username will be the Shop URL.', 'wk-marketplace' ); ?></span>
						<span class="help-tip"></span>
					</span>
				<?php } ?>
				</label>
				<input data-is_optional="<?php echo ( 'required' !== $shopurl_visibility ) ? true : false; ?>" type="text" class="input-text form-control" name="wkmp_shopurl" value="<?php echo esc_attr( $wkmp_shopurl ); ?>" id="wkmp-shopurl"/>
				<strong id="wkmp-shop-url-availability"></strong>
			</p>
			<?php
		}

		$mkp_show_hide_field = apply_filters( 'wkmp_show_mp_phone_registration_field', true );

		if ( $mkp_show_hide_field ) {
			?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="wkmp-shopphone"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?><span class="required">*</span></label>
			<input placeholder="<?php esc_attr_e( 'Enter a valid phone number from 4 to 15 characters.', 'wk-marketplace' ); ?>" type="text" class="input-text form-control" name="wkmp_shopphone" value="<?php echo esc_attr( $wkmp_shopphone ); ?>" id="wkmp-shopphone"/>
		</p>
			<?php
		}
		do_action( 'wk_mkt_add_register_field' );
		?>
	</div>

	<?php if ( is_account_page() ) { ?>
		<div class="wkmp-role-selector-section">
			<ul class="nav wkmp-role-selector" role="tablist">
				<li class="wkmp-button wkmp-customer<?php echo 'customer' === $user_role ? ' active' : ''; ?>" data-target="0">
					<input type="radio" name="role" value="customer"<?php checked( $user_role, 'customer' ); ?>>
					<label class="radio wkmp-fw-600"><?php esc_html_e( 'I am a Customer', 'wk-marketplace' ); ?></label>
				</li>
				<li class="wkmp-button wkmp-seller<?php echo 'seller' === $user_role ? ' active' : ''; ?>" data-target="1">
					<input type="radio" name="role" value="seller"<?php checked( $user_role, 'seller' ); ?> >
					<label class="radio wkmp-fw-600"><?php esc_html_e( 'I am a Seller', 'wk-marketplace' ); ?></label>
				</li>
				<!-- Ambassador Stripe customization compatibility -->
				<?php do_action( 'wkmp_user_registration_option', $user_role ); ?>
			</ul>
		</div>
	<?php } else { ?>
		<input type="hidden" name="role" value="seller">
		<?php
	}
	?>
</div>
