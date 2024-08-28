<?php
/**
 * Assets option template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

settings_errors();
?>
<p><?php esc_html_e( 'Configure the seller\'s assets, and enable them to make them visible on the seller profile page.', 'wk-marketplace' ); ?></p>

<form method="post" action="options.php">
	<?php settings_fields( 'wkmp-assets-settings-group' ); ?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wkmp-is-seller-email-visible"><?php esc_html_e( 'Email', 'wk-marketplace' ); ?></label>
				</th>

				<td class="forminp">
					<input type="checkbox" name="_wkmp_is_seller_email_visible" value="yes" <?php echo checked( get_option( '_wkmp_is_seller_email_visible' ), 'yes', false ); ?>>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wkmp-is-seller-phone-visible"><?php esc_html_e( 'Phone Number', 'wk-marketplace' ); ?></label>
				</th>

				<td class="forminp">
					<input type="checkbox" name="_wkmp_is_seller_contact_visible" value="yes" <?php echo checked( get_option( '_wkmp_is_seller_contact_visible' ), 'yes', false ); ?>>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wkmp-is-seller-address-visible"><?php esc_html_e( 'Address', 'wk-marketplace' ); ?></label>
				</th>

				<td class="forminp">
					<input type="checkbox" name="_wkmp_is_seller_address_visible" value="yes" <?php echo checked( get_option( '_wkmp_is_seller_address_visible' ), 'yes', false ); ?>>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wkmp-is-seller-social-links-visible"><?php esc_html_e( 'Social Links', 'wk-marketplace' ); ?></label>
				</th>

				<td class="forminp">
					<input type="checkbox" name="_wkmp_is_seller_social_links_visible" value="yes" <?php echo checked( get_option( '_wkmp_is_seller_social_links_visible' ), 'yes', false ); ?>>
				</td>
			</tr>
			<?php do_action( 'wkmp_admin_after_assets_options' ); ?>
		</tbody>
	</table>
	<?php submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' ); ?>
</form>
<hr />
