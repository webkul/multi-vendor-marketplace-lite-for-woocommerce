<?php
/**
 * Google analytics settings template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

settings_errors();
$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();
?>
<form method="POST" action="options.php">
<?php settings_fields( 'wkmp-google-analytics-settings-group' ); ?>
<table class="form-table wkmp-google-analytics-settings">
	<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-enable-google-analytics"><?php esc_html_e( 'Enable Google analytics', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'If checked, google analytics data will be populated.', 'wk-marketplace' ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				)
				?>
				<input <?php echo ( $pro_disabled ) ? 'onclick="return false"' : ''; ?> name="_wkmp_enable_google_analytics" type="checkbox" id="wkmp_enable_google_analytics" value="1" <?php checked( get_option( '_wkmp_enable_google_analytics', false ), 1 ); ?> />
				<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-google-account-number">
					<?php esc_html_e( 'Measurement ID', 'wk-marketplace' ); ?>
				</label>
			</th>
			<td class="forminp forminp-text">
			<?php
			echo wp_kses(
				wc_help_tip( esc_html__( 'Google analytics tracking ID to be obtained from Google Analytics Account.', 'wk-marketplace' ), true ),
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
			<input <?php echo ( $pro_disabled ) ? 'readonly' : ''; ?> type="text" class="regular-text" id="wkmp-google-account-number" name="_wkmp_google_account_number" value="<?php echo esc_attr( get_option( '_wkmp_google_account_number' ) ); ?>" />
			<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
			</td>
		</tr>

		<?php do_action( 'wkmp_after_google_analytics_settings' ); ?>

		<tr><td colspan="2"><hr/></td></tr>

		<tr valign="top">
			<th scope="row" class="wkmp-google-map-api-key">
				<label for="wkmp-google-map-api-key"><?php esc_html_e( 'Google Map API Key', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php
				echo wp_kses(
					wc_help_tip( esc_html__( 'Google Map API Key to be obtained form Google Account.', 'wk-marketplace' ), true ),
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
				<input type="text" class="regular-text" id="wkmp-google-map-api-key" name="_wkmp_google_map_api_key" value="<?php echo esc_attr( get_option( '_wkmp_google_map_api_key' ) ); ?>" />
				<span class="wkmp-help"><a href="https://webkul.com/blog/how-to-get-google-api-key/" target="_blank"><?php esc_html_e( 'Click here', 'wk-marketplace' ); ?></a><?php esc_html_e( ' to know how to create one.', 'wk-marketplace' ); ?></span>
				<p class="wkmp-text"><?php esc_html_e( 'Although it is optional, but if filled top billing countries on seller dashboard will be populated for all countries properly especially for US regions.', 'wk-marketplace' ); ?></p>
			</td>
		</tr>
		<?php do_action( 'wkmp_add_settings_field_analytics' ); ?>
	</tbody>
</table>
<?php submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' ); ?>
</form>
<hr/>
