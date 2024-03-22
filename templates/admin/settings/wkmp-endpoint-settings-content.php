<?php
/**
 * Marketplace Admin Endpoints Settings Template.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$seller_dashboard      = in_array( 'seller-dashboard', $wc_endpoints, true ) ? esc_attr( 'wkmp-dashboard' ) : esc_attr( 'seller-dashboard' );
$seller_products       = in_array( 'seller-products', $wc_endpoints, true ) ? esc_attr( 'wkmp-products' ) : esc_attr( 'seller-products' );
$seller_add_product    = in_array( 'seller-add-product', $wc_endpoints, true ) ? esc_attr( 'wkmp-add-product' ) : esc_attr( 'seller-add-product' );
$seller_edit_product   = in_array( 'seller-edit-product', $wc_endpoints, true ) ? esc_attr( 'wkmp-edit-product' ) : esc_attr( 'seller-edit-product' );
$seller_orders         = in_array( 'seller-orders', $wc_endpoints, true ) ? esc_attr( 'wkmp-orders' ) : esc_attr( 'seller-orders' );
$seller_transactions   = in_array( 'seller-transactions', $wc_endpoints, true ) ? esc_attr( 'wkmp-transactions' ) : esc_attr( 'seller-transactions' );
$seller_profile        = in_array( 'seller-profile', $wc_endpoints, true ) ? esc_attr( 'wkmp-profile' ) : esc_attr( 'seller-profile' );
$seller_notifications  = in_array( 'seller-notifications', $wc_endpoints, true ) ? esc_attr( 'wkmp-notifications' ) : esc_attr( 'seller-notifications' );
$seller_shop_followers = in_array( 'seller-shop-followers', $wc_endpoints, true ) ? esc_attr( 'wkmp-shop-followers' ) : esc_attr( 'seller-shop-followers' );
$seller_ask_to_admin   = in_array( 'seller-asktoadmin', $wc_endpoints, true ) ? esc_attr( 'wkmp-asktoadmin' ) : esc_attr( 'seller-asktoadmin' );
$seller_all_products   = in_array( 'seller-all-products', $wc_endpoints, true ) ? esc_attr( 'wkmp-all-products' ) : esc_attr( 'seller-all-products' );
$seller_store          = in_array( 'seller-store', $wc_endpoints, true ) ? esc_attr( 'wkmp-store' ) : esc_attr( 'seller-store' );
$feedbacks             = in_array( 'seller-feedbacks', $wc_endpoints, true ) ? esc_attr( 'wkmp-feedbacks' ) : esc_attr( 'seller-feedbacks' );
$add_feedback          = in_array( 'add-feedback', $wc_endpoints, true ) ? esc_attr( 'wkmp-add-feedback' ) : esc_attr( 'add-feedback' );
$favorite_seller       = in_array( 'favorite-sellers', $wc_endpoints, true ) ? esc_attr( 'wkmp-favorite-sellers' ) : esc_attr( 'favorite-sellers' );

$endpoints_array = apply_filters(
	'wkmp_endpoint_settings_array',
	array(
		'_wkmp_dashboard_endpoint'       => array(
			'slug'  => $seller_dashboard,
			'title' => esc_html__( 'Seller Dashboard', 'wk-marketplace' ),
		),
		'_wkmp_product_list_endpoint'    => array(
			'slug'  => $seller_products,
			'title' => esc_html__( 'Products List', 'wk-marketplace' ),
		),
		'_wkmp_add_product_endpoint'     => array(
			'slug'  => $seller_add_product,
			'title' => esc_html__( 'Add Product', 'wk-marketplace' ),
		),
		'_wkmp_edit_product_endpoint'    => array(
			'slug'  => $seller_edit_product,
			'title' => esc_html__( 'Edit Product', 'wk-marketplace' ),
		),
		'_wkmp_order_history_endpoint'   => array(
			'slug'  => $seller_orders,
			'title' => esc_html__( 'Orders History', 'wk-marketplace' ),
		),
		'_wkmp_transaction_endpoint'     => array(
			'slug'  => $seller_transactions,
			'title' => esc_html__( 'Transactions', 'wk-marketplace' ),
		),
		'_wkmp_profile_endpoint'         => array(
			'slug'  => $seller_profile,
			'title' => esc_html__( 'My Profile', 'wk-marketplace' ),
		),
		'_wkmp_notification_endpoint'    => array(
			'slug'  => $seller_notifications,
			'title' => esc_html__( 'Notifications', 'wk-marketplace' ),
		),
		'_wkmp_shop_follower_endpoint'   => array(
			'slug'  => $seller_shop_followers,
			'title' => esc_html__( 'Shop Followers', 'wk-marketplace' ),
		),
		'_wkmp_asktoadmin_endpoint'      => array(
			'slug'  => $seller_ask_to_admin,
			'title' => esc_html__( 'Ask to Admin', 'wk-marketplace' ),
		),
		'_wkmp_seller_product_endpoint'  => array(
			'slug'  => $seller_all_products,
			'title' => esc_html__( 'Products from Seller', 'wk-marketplace' ),
		),
		'_wkmp_store_endpoint'           => array(
			'slug'  => $seller_store,
			'title' => esc_html__( 'Sellers Store', 'wk-marketplace' ),
		),
		'_wkmp_feedbacks_endpoint'       => array(
			'slug'  => $feedbacks,
			'title' => esc_html__( 'Feedbacks', 'wk-marketplace' ),
		),
		'_wkmp_add_feedback_endpoint'    => array(
			'slug'  => $add_feedback,
			'title' => esc_html__( 'Add Feedback', 'wk-marketplace' ),
		),
		'_wkmp_favorite_seller_endpoint' => array(
			'slug'  => $favorite_seller,
			'title' => esc_html__( 'Favorite Sellers', 'wk-marketplace' ),
		),
	)
);

settings_errors();
$pro_disabled = $wkmarketplace->wkmp_is_pro_module_disabled();
?>
<p><?php esc_html_e( 'Endpoints are appended to your page URLs to handle specific actions on the WooCommerce pages. They should be unique. To avoid any collision, we recommend you to use prefix: ', 'wk-marketplace' ); ?><strong><?php echo esc_html( '"seller-"' ); ?></strong></p>
<p><?php esc_html_e( 'Already registered WooCommerce endpoints: ', 'wk-marketplace' ); ?><strong><?php echo wp_kses_post( implode( ', ', $wc_endpoints ) ); ?></strong></p>

<form method="post" action="options.php" id="wkmp-endpoint-form">
	<?php
	settings_fields( 'wkmp-endpoint-settings-group' );

	foreach ( $endpoints_array as $endpoint_name => $value ) {
		?>
		<fieldset class="mp-fieldset">
			<legend><?php echo esc_html( wc_strtoupper( $value['title'] ) ); ?></legend>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row">
						<label for=""><?php esc_html_e( 'Endpoint', 'wk-marketplace' ); ?></label>
					</th>
					<td class="forminp">
						<?php
						echo wp_kses(
							wc_help_tip( /* translators: %s: Endpoint name. */ sprintf( esc_html__( 'Endpoint for "My Account → %s" page.', 'wk-marketplace' ), esc_html( $value['title'] ) ) ),
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
						<input type="text" etype="endpoint" class="regular-text mp-endpoints-text" name="<?php echo esc_attr( $endpoint_name ); ?>" value="<?php echo esc_attr( get_option( $endpoint_name, $value['slug'] ) ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for=""><?php esc_html_e( 'Title', 'wk-marketplace' ); ?></label>
					</th>
					<td class="forminp">
						<?php
						echo wp_kses(
							wc_help_tip( sprintf( /* translators: %s: username. */ esc_html__( 'Title for "My Account → %s" page.', 'wk-marketplace' ), esc_html( $value['title'] ) ) ),
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
						<input type="text" class="regular-text" name="<?php echo esc_attr( $endpoint_name . '_name' ); ?>" value="<?php echo esc_attr( get_option( $endpoint_name . '_name', esc_attr( $value['title'] ) ) ); ?>" required>
					</td>
				</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	?>
</form>
<p class="submit">
	<input <?php echo ( $pro_disabled ) ? 'onclick="return false"' : ''; ?> type="submit" name="submit" id="wk-endpoint-submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'wk-marketplace' ); ?>">
<?php ( $pro_disabled ) ? $template_functions->wkmp_show_upgrade_lock_icon() : ''; ?>
</p>
<hr/>
