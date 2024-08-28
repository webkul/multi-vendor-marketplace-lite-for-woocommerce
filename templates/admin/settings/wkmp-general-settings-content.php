<?php
/**
 * General settings template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

settings_errors();
$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();
?>
<form method="POST" action="options.php">
	<?php settings_fields( 'wkmp-general-settings-group' ); ?>
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-default-commission">
					<?php esc_html_e( 'Default Commission (in %)', 'wk-marketplace' ); ?>
				</label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'Default commission from seller if not set on seller basis.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<input type="text" class="regular-text wc_input_decimal" id="wkmp-default-commission" name="_wkmp_default_commission" value="<?php echo esc_attr( wc_format_localized_decimal( get_option( '_wkmp_default_commission' ) ) ); ?>"/>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-auto-approve-seller"><?php esc_html_e( 'Auto Approve Seller', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'If checked, Seller will be approved automatically.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<input <?php echo ( $pro_disabled ) ? 'onclick="return false"' : ''; ?> name="_wkmp_auto_approve_seller" type="checkbox" id="wkmp-auto-approve-seller" value="1" <?php checked( get_option( '_wkmp_auto_approve_seller', true ), 1 ); ?> />
				<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="titledesc">
				<label for="wkmp-separate-seller-dashboard"><?php esc_html_e( 'Separate Seller Dashboard', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'If checked, Seller will have separate dashboard like Admin.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<input <?php echo ( $pro_disabled ) ? 'onclick="return false"' : ''; ?> type="checkbox" name="_wkmp_separate_seller_dashboard" id="wkmp-separate-seller-dashboard" value="1" <?php checked( get_option( '_wkmp_separate_seller_dashboard', false ), 1 ); ?> />
				<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
			</td>
		</tr>
		<tr>
			<th scope="row" class="titledesc">
				<label for="wkmp-separate-seller-registration"><?php esc_html_e( 'Separate Seller Registration', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'If checked, Seller registration will be done from separate page than My Account.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<input <?php echo ( $pro_disabled ) ? 'onclick="return false"' : ''; ?> type="checkbox" name="_wkmp_separate_seller_registration" id="wkmp-separate-seller-registration" value="1" <?php checked( get_option( '_wkmp_separate_seller_registration' ), 1 ); ?> />
				<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
			</td>
		</tr>

		<tr>
			<th scope="row" class="titledesc">
				<label for="wkmp-seller-delete"><?php esc_html_e( 'Data delete after seller delete', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'If checked, Then data delete after seller delete else assigned to the admin', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<input type="checkbox" name="_wkmp_seller_delete" id="wkmp-seller-delete" value="1" <?php checked( get_option( '_wkmp_seller_delete' ), 1 ); ?> />
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="wkmp_shipping_option">
					<?php
					$method_title = apply_filters( 'wkmp_general_settings_shipping_option_title', esc_html__( 'Applicable Shipping Methods', 'wk-marketplace' ) );
					echo esc_html( $method_title );
					?>
				</label>
			</th>
			<td>
				<?php
				$shipping_method = array(
					'woocommerce' => esc_html__( 'Admin Shipping Methods', 'wk-marketplace' ),
					'marketplace' => esc_html__( 'Seller Shipping Methods', 'wk-marketplace' ),
				);
				$shipping_method = apply_filters( 'wkmp_general_settings_shipping_methods', $shipping_method );
				?>
				<?php
				echo wp_kses(
					wc_help_tip( apply_filters( 'wkmp_general_settings_shipping_option_message', esc_html__( 'Check Whose shipping method is applicable at cart Page', 'wk-marketplace' ) ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<select name="wkmp_shipping_option" class="regular-text" id="wkmp_shipping_option">
					<?php
					if ( ! empty( $shipping_method ) && is_iterable( $shipping_method ) ) {
						$selected = get_option( 'wkmp_shipping_option', 'woocommerce' );
						foreach ( $shipping_method as $key => $methods ) :
							?>
							<option <?php echo ( $pro_disabled && 'marketplace' === $key ) ? 'disabled' : ''; ?> value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key ); ?> >
								<?php echo esc_html( $methods ); ?>
							</option>
							<?php
						endforeach;
					}
					?>
				</select>
				<?php
				( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : '';
				if ( 'disabled' === get_option( 'woocommerce_ship_to_countries', false ) ) {
					?>
				<p class="description"><?php printf( wp_kses( 'Shipping must be enabled from %s woocommerce settings %s in order to work this functionality.', 'wk-marketplace' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">', '</a>' ); ?></p>
				<?php } ?>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="wkmp_seller_page_option">
					<?php
					$seller_page_title = apply_filters( 'wkmp_general_settings_seller_page', esc_html__( 'Select Seller Page', 'wk-marketplace' ) );
					echo esc_html( $seller_page_title );
					?>
				</label>
			</th>
			<td>
				<?php
				$args = apply_filters(
					'wkmp_get_pages_for_seller_dashboard_args',
					array(
						'exclude' => array(
							get_option( 'woocommerce_shop_page_id', 0 ),
							get_option( 'woocommerce_cart_page_id', 0 ),
							get_option( 'woocommerce_checkout_page_id', 0 ),
							get_option( 'woocommerce_pay_page_id', 0 ),
							get_option( 'woocommerce_thanks_page_id', 0 ),
							get_option( 'woocommerce_myaccount_page_id', 0 ),
							get_option( 'woocommerce_edit_address_page_id', 0 ),
							get_option( 'woocommerce_view_order_page_id', 0 ),
							get_option( 'woocommerce_terms_page_id', 0 ),
						),
					)
				);

				$site_pages = get_pages( $args );
				echo wp_kses(
					wc_help_tip( apply_filters( 'wkmp_general_settings_seller_page_messages', esc_html__( 'Select page to show seller dashboard.', 'wk-marketplace' ) ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<select name="wkmp_select_seller_page" class="regular-text" id="wkmp_seller_page">
					<?php
					if ( ! empty( $site_pages ) && is_iterable( $site_pages ) ) {
						$seller_page_id = get_option( 'wkmp_seller_page_id' );
						foreach ( $site_pages as $site_page ) {
							?>
							<option value="<?php echo esc_attr( $site_page->ID ); ?>" <?php selected( $seller_page_id, $site_page->ID ); ?> >
								<?php echo esc_html( $site_page->post_title ); ?>
							</option>
							<?php
						}
					}
					?>
				</select>
				<p class="description"><?php printf( wp_kses( 'Updating a new seller page will erase the previous content of the newly selected page. Kindly update %s permalinks %s after change.', 'wk-marketplace' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">', '</a>' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="wkmp_shop_name_visibility">
					<?php esc_html_e( 'Shop Name on Registration', 'wk-marketplace' ); ?>
				</label>
			</th>
			<td>
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'Select Shop name visibility on seller registration page.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<select name="wkmp_shop_name_visibility" class="regular-text" id="wkmp_shop_name_visibility">
					<option value="required" <?php selected( get_option( 'wkmp_shop_name_visibility' ), 'required' ); ?> ><?php esc_html_e( 'Required', 'wk-marketplace' ); ?></option>
					<option value="optional" <?php selected( get_option( 'wkmp_shop_name_visibility' ), 'optional' ); ?> ><?php esc_html_e( 'Optional', 'wk-marketplace' ); ?></option>
					<option value="remove" <?php selected( get_option( 'wkmp_shop_name_visibility' ), 'remove' ); ?> ><?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'If shop name is removed or empty in case of optional the module will use seller name instead.', 'wk-marketplace' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="wkmp_shop_url_visibility">
					<?php esc_html_e( 'Shop URL on Registration', 'wk-marketplace' ); ?>
				</label>
			</th>
			<td>
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'Select Shop URL visibility on seller registration page.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<select name="wkmp_shop_url_visibility" class="regular-text" id="wkmp_shop_url_visibility">
					<option value="required" <?php selected( get_option( 'wkmp_shop_url_visibility' ), 'required' ); ?> ><?php esc_html_e( 'Required', 'wk-marketplace' ); ?></option>
					<option value="optional" <?php selected( get_option( 'wkmp_shop_url_visibility' ), 'optional' ); ?> ><?php esc_html_e( 'Optional', 'wk-marketplace' ); ?></option>
					<option value="remove" <?php selected( get_option( 'wkmp_shop_url_visibility' ), 'remove' ); ?> ><?php esc_html_e( 'Remove', 'wk-marketplace' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'If shop url is removed or empty in case of optional the module will use seller\'s username instead.', 'wk-marketplace' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="wkmp_seller_terms_page_option">
					<?php
					$seller_term_title = apply_filters( 'wkmp_general_settings_seller_terms', esc_html__( 'Seller Terms & Condition Page', 'wk-marketplace' ) );
					echo esc_html( $seller_term_title );
					?>
				</label>
			</th>
			<td>
				<?php
				echo wp_kses(
					wc_help_tip( apply_filters( 'wkmp_general_settings_seller_term_page_messages', esc_html__( 'Select page to show terms and condition checkbox on seller registration page.', 'wk-marketplace' ) ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
				?>
				<select name="wkmp_seller_terms_page_id" class="regular-text" id="wkmp_seller_terms_page_id">
					<option style="font-weight: 800;" value="0"><?php esc_html_e( '-----Disable Terms and Conditions-----', 'wk-marketplace' ); ?></option>
					<?php
					if ( ! empty( $site_pages ) && is_iterable( $site_pages ) ) {
						$seller_term_page_id = get_option( 'wkmp_seller_terms_page_id', 0 );
						foreach ( $site_pages as $site_page ) {
							?>
							<option <?php echo ( $pro_disabled ) ? 'disabled' : ''; ?> value="<?php echo esc_attr( $site_page->ID ); ?>" <?php selected( $seller_term_page_id, $site_page->ID ); ?> >
								<?php echo esc_html( $site_page->post_title ); ?>
							</option>
							<?php
						}
					}
					?>
				</select>
				<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
			</td>
		</tr>

		<?php do_action( 'wkmp_add_settings_field' ); ?>
		</tbody>
	</table>
	<?php submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' ); ?>
</form>
<hr/>
