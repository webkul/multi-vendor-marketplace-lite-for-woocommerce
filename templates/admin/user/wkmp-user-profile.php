<?php
/**
 * User profile.
 *
 * @package @package @package WkMarketplace\Admin
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

$user_id   = 0;
$wkmp_role = \WK_Caching::wk_get_request_data( 'role', array( 'method' => 'post' ) );

if ( ! $show_fields && $user instanceof \WP_User ) {
	if ( in_array( 'wk_marketplace_seller', $user->roles, true ) || ( count( array_intersect( array( 'customer', 'subscriber' ), $user->roles ) ) > 0 ) ) {
		$show_fields = true;
	}
	$user_id = $user->ID;
}

if ( $show_fields ) {
	$payment_info = get_user_meta( $user_id, 'mp_seller_payment_details', true );
	$payment_info = empty( $payment_info ) ? esc_html__( 'No information provided.', 'wk-marketplace' ) : $payment_info;

	$address = get_user_meta( $user_id, 'shop_address', true );
	$disable = empty( $address ) ? '' : 'disabled';

	$shopname_visibility = get_option( 'wkmp_shop_name_visibility', 'required' );
	$shopurl_visibility  = get_option( 'wkmp_shop_url_visibility', 'required' );
	?>
	<div class="mp-seller-details wkmp-hide">
		<h3 class="heading"><?php esc_html_e( 'Marketplace Seller Details', 'wk-marketplace' ); ?></h3>
		<table class="form-table">
			<tr>
				<th>
					<label for="company-name">
						<?php esc_html_e( 'Shop Name', 'wk-marketplace' ); ?><?php echo ( 'required' === $shopname_visibility ) ? '<span style="display:inline-block;" class="required">*</span>' : ''; ?>
					</label>
				</th>
				<td>
					<input type="text" class="input-text form-control" name="shopname" id="org-name" value="<?php echo esc_attr( get_user_meta( $user_id, 'shop_name', true ) ); ?>" <?php echo ( 'required' === $shopname_visibility ) ? 'required=required' : ''; ?>/>
				</td>
			</tr>
			<tr>
				<th><label for="seller-url" class="pull-left"><?php esc_html_e( 'Shop URL', 'wk-marketplace' ); ?> <?php echo ( 'required' === $shopurl_visibility ) ? '<span class="required" style="display:inline-block;">*</span>' : ''; ?></label></th>
				<td>
					<input type="text" class="input-text form-control" name="shopurl" id="seller-shop" value="<?php echo esc_attr( $address ); ?>" <?php echo ( 'required' === $shopurl_visibility ) ? 'required=required' : ''; ?> <?php echo esc_html( $disable ); ?>>
					<p><strong id="seller-shop-alert-msg" class="pull-right"></strong></p>
				</td>
			</tr>
			<?php
			if ( empty( $wkmp_role ) ) {
				?>
			<tr>
				<th>
					<label for="seller-payment-info" class="pull-left">
						<?php esc_html_e( 'Payment Information', 'wk-marketplace' ); ?>
					</label>
				</th>
				<td>
					<?php echo esc_html( $payment_info ); ?><br>
				</td>
			</tr>
				<?php
			}
			do_action( 'wkmp_after_seller_profile_fields', $user, $disable );
			?>
		</table>
	</div>
	<?php
}
