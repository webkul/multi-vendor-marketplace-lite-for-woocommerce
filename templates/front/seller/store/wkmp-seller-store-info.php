<?php
/**
 * Store info.
 *
 * @package WkMarketplace
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$current_user_id = get_current_user_id();
$mp_page_title   = empty( $seller_info->shop_name ) ? '' : $seller_info->shop_name;

$is_pending_review = false;

foreach ( $review_check as $review_data ) {
	if ( 0 === intval( $review_data->status ) ) {
		$is_pending_review = true;
		break;
	}
}

if ( empty( $mp_page_title ) ) {
	$seller_name   = empty( $seller_info->first_name ) ? '' : $seller_info->first_name;
	$seller_name   = ( ! empty( $seller_name ) && ! empty( $seller_info->last_name ) ) ? $seller_name . ' ' . $seller_info->last_name : $seller_name;
	$mp_page_title = empty( $seller_name ) ? esc_html__( 'Store Page', 'wk-marketplace' ) : $seller_name;
}
?>

<div class="mp-profile-wrapper woocommerce">
	<h1 class="mp-page-title"><?php echo esc_html( $mp_page_title ); ?></h1>
	<div class="mp-profile-banner">
		<?php if ( ! empty( $shop_banner ) ) { ?>
			<img src="<?php echo esc_url( $shop_banner ); ?>" class="mp-shop-banner">
		<?php } ?>
	</div>
	<div class="mp-profile-information">
		<div class="mp-shop-stats">
			<img src="<?php echo esc_url( $avatar_image ); ?>" class="wkmp-seller-avatar">
			<div class="mp-seller-avg-rating">
				<?php if ( $quality ) { ?>
					<h2><span class="single-star"></span><?php echo esc_html( number_format( $quality, 2 ) ); ?></h2>
					<a href="javascript:void(0)" class="mp-avg-rating-box-link"><?php esc_html_e( 'Average Rating', 'wk-marketplace' ); ?>
						<div class="mp-avg-rating-box">
							<div class="mp-avg-rating">
								<p><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></p>
								<?php echo wp_kses_post( wc_get_rating_html( $price_stars ) ); ?>
								<p>( <?php echo esc_html( number_format( $price_stars, 2 ) . '/' . $total_feedback ); ?> )</p>
							</div>
							<div class="mp-avg-rating">
								<p><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></p>
								<?php echo wp_kses_post( wc_get_rating_html( $value_stars ) ); ?>
								<p>( <?php echo esc_html( number_format( $value_stars, 2 ) . '/' . $total_feedback ); ?> )</p>
							</div>
							<div class="mp-avg-rating">
								<p><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></p>
								<?php echo wp_kses_post( wc_get_rating_html( $quality_stars ) ); ?>
								<p>( <?php echo esc_html( number_format( $quality_stars, 2 ) . '/' . $total_feedback ); ?> )</p>
							</div>
						</div>
					</a>
					<?php
				}

				if ( $current_user_id > 0 && intval( $this->seller_id ) !== $current_user_id && empty( $quality ) ) {
					?>
					<div class="wk_write_review">
						<a class="open-review-form forloginuser wk_mpsocial_feedback" href="<?php echo esc_url( $add_review ); ?>">
							<?php esc_html_e( 'Be the first one to review!', 'wk-marketplace' ); ?>
						</a>
					</div>
					<?php
				}
				?>
			</div>
			<?php
			if ( $current_user_id > 0 && intval( $this->seller_id ) !== $current_user_id && $is_pending_review ) {
				?>
				<div class="wk_write_review">
					<p class="wkmp-pending-reviews">
						<?php esc_html_e( 'Your review is pending for approval.', 'wk-marketplace' ); ?>
					</p>
				</div>
				<?php
			}
			?>
		</div>
		<?php do_action( 'mp_edit_seller_profile' ); ?>
		<div class="mp-shop-actions-info">
			<div class="mp-shop-action-wrapper">
				<div class="mp-shop-info">
					<?php
					if ( 'yes' === get_option( '_wkmp_is_seller_email_visible' ) ) {
						$seller_email = empty( $seller_info->user_email ) ? '' : $seller_info->user_email;

						if ( ! empty( $seller_email ) ) {
							?>
						<div>
							<span class="dashicons dashicons-email" style="margin-top:4px;"></span>
							<a href="mailto:<?php echo esc_attr( $seller_info->user_email ); ?>"><?php echo esc_html( $seller_info->user_email ); ?></a>
						</div>
							<?php
						}
					}

					if ( 'yes' === get_option( '_wkmp_is_seller_contact_visible' ) ) {
						$billing_phone = empty( $seller_info->billing_phone ) ? '' : $seller_info->billing_phone;

						if ( ! empty( $billing_phone ) ) {
							?>
						<div>
							<span class="dashicons dashicons-phone" style="margin-top:4px;"></span>
							<a href="tel:<?php echo esc_attr( $seller_info->billing_phone ); ?>" target="_blank" title="<?php esc_attr_e( 'Click to Dial - Phone Only', 'wk-marketplace' ); ?>"><?php echo isset( $seller_info->billing_phone ) ? esc_attr( $seller_info->billing_phone ) : ''; ?></a>
						</div>
							<?php
						}
					}

					if ( 'yes' === get_option( '_wkmp_is_seller_address_visible' ) ) {
						$address = '';

						$address .= empty( $seller_info->billing_address_1 ) ? '' : $seller_info->billing_address_1;
						$address .= empty( $seller_info->billing_address_2 ) ? '' : ' ' . $seller_info->billing_address_2;
						$address .= empty( $seller_info->billing_city ) ? '' : ' ' . $seller_info->billing_city;
						$address .= empty( $seller_info->billing_state ) ? '' : '<br>' . $seller_info->billing_state;
						$address .= empty( $seller_info->billing_country ) ? '' : ' (' . $seller_info->billing_country . ')';
						$address .= empty( $seller_info->billing_postcode ) ? '' : ' ' . $seller_info->billing_postcode;

						if ( ! empty( $address ) ) {
							?>
						<div>
						<span class="dashicons dashicons-location" style="margin-top:4px;"> </span> <?php echo wp_kses( $address, array( 'br' => array() ) ); ?>
						</div>
							<?php
						}
					}
					if ( 'yes' === get_option( '_wkmp_is_seller_social_links_visible' ) ) :
						require_once __DIR__ . '/wkmp-seller-social-links-section.php';
					endif;
					?>
				</div>
				<div class="mp-shop-actions">
					<a class="button wc-forward" href="<?php echo esc_url( $seller_collection ); ?>"><?php esc_html_e( 'View Collection', 'wk-marketplace' ); ?></a>

					<?php
					if ( ( $current_user_id > 0 && intval( $this->seller_id ) !== $current_user_id && ! $is_pending_review ) || $current_user_id < 1 ) {
						if ( $current_user_id < 1 ) {
							$account_page_id = wc_get_page_id( 'myaccount' );
							$account_page    = get_post( $account_page_id );
							$add_review      = get_permalink( $account_page ) . '?redirect_to=' . $add_review;
						}
						?>
						<div class="wk_write_review">
							<a class="btn btn-default button button-small open-review-form forloginuser wk_mpsocial_feedback" href="<?php echo esc_url( $add_review ); ?>"><?php esc_html_e( 'Write A Review!', 'wk-marketplace' ); ?></a>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div><!-- mp-profile-information -->
	<?php do_action( 'mkt_before_seller_preview_products', $this->seller_id ); ?>

	<div class="mp-seller-recent-product">
		<h3><?php echo esc_html( get_option( '_wkmp_store_endpoint_name', esc_html__( 'Recent Products', 'wk-marketplace' ) ) ); ?></h3>
		<?php
		$query_args = array(
			'author'         => $this->seller_id,
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 9,
			'paged'          => $store_paged,
		);

		$query_args = apply_filters( 'mp_seller_collection_product_args', $query_args );

		$products = new \WP_Query( $query_args );

		if ( $products->have_posts() ) {
			do_action( 'marketplace_before_shop_loop', $products->max_num_pages );
			woocommerce_product_loop_start();
			while ( $products->have_posts() ) :
				$products->the_post();
				wc_get_template_part( 'content', 'product' );
			endwhile;
			woocommerce_product_loop_end();
			do_action( 'marketplace_after_shop_loop', $products->max_num_pages );
		} else {
			esc_html_e( 'No product available !', 'wk-marketplace' );
		}
		wp_reset_postdata();
		?>
	</div>
	<?php
	do_action( 'mkt_after_seller_preview_products' );

	if ( isset( $seller_info->about_shop ) && ! empty( $seller_info->about_shop ) ) {
		?>
	<!-- About shop -->
	<div class="mp-about-shop wkmp-front-seller-textarea-info">
		<h2><?php esc_html_e( 'About Shop', 'wk-marketplace' ); ?></h2>
		<p><?php echo isset( $seller_info->about_shop ) ? wp_kses_post( nl2br( $seller_info->about_shop ) ) : ''; ?></p>
	</div>
		<?php
	}
	do_action( 'mkt_before_seller_review_data', $seller_info );

	if ( $reviews ) {
		$count = 0;
		?>
		<div class="mp-shop-reviews">
			<?php
			foreach ( $reviews as $key => $review ) {
				if ( 5 === intval( $count ) ) {
					break;
				}
				$commenter_id = $review->user_id;
				$given_by     = $this->marketplace->wkmp_get_user_display_name( $commenter_id );
				?>
				<div class="mp-shop-review-row">
					<div class="mp-shop-review-rating">
						<p><b><?php esc_html_e( 'Review', 'wk-marketplace' ); ?></b></p>
						<div class="rating">
							<span><b><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php
								for ( $i = 1; $i <= 5; $i++ ) {
									if ( $i <= $review->price_r ) {
										?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
										<?php
									}
								}
								?>
							</div>
						</div>
						<div class="rating">
							<span><b><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php
								for ( $i = 1; $i <= 5; $i++ ) {
									if ( $i <= $review->value_r ) {
										?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
										<?php
									}
								}
								?>
							</div>
						</div>
						<div>
							<span><b><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php
								for ( $i = 1; $i <= 5; $i++ ) {
									if ( $i <= $review->quality_r ) {
										?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
					<div class="mp-shop-review-detail">
						<p><b><?php echo esc_html( $review->review_summary ); ?></b></p>
						<p><?php esc_html_e( 'By', 'wk-marketplace' ); ?> <b><?php echo esc_html( $given_by ); ?></b>
							, <?php echo esc_html( gmdate( 'd-F-Y', strtotime( $review->review_time ) ) ); ?></p>
						<p><?php echo esc_html( $review->review_desc ); ?></p>
					</div>
				</div>
				<?php
				++$count;
			}
			if ( count( $reviews ) > 5 ) {
				?>
				<div class="mp-review-page-link">
					<a href="<?php echo esc_url( $all_review ); ?>" class="button"><?php esc_html_e( 'View All Reviews', 'wk-marketplace' ); ?></a>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>
