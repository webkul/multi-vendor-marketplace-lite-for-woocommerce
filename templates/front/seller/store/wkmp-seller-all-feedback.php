<?php
/**
 * All Feedback.
 *
 * @package WkMarketplace\Includes\Shipping
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>
<div class="mp-profile-wrapper woocommerce">
	<?php $this->wkmp_seller_profile_details_section( 'all-feedback' ); ?>
	<!-- Shop reviews -->
	<?php if ( $reviews ) { ?>
		<div class="mp-shop-reviews">
			<?php foreach ( $reviews as $key => $review ) { ?>
				<div class="mp-shop-review-row">
					<div class="mp-shop-review-rating">
						<p><b><?php esc_html_e( 'Review', 'wk-marketplace' ); ?></b></p>
						<div class="rating">
							<span><b><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
									<?php if ( $i <= $review->price_r ) { ?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
						<div class="rating">
							<span><b><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
									<?php if ( $i <= $review->value_r ) { ?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
						<div>
							<span><b><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></b></span>
							<div class="star-rating">
								<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
									<?php if ( $i <= $review->quality_r ) { ?>
										<div class="star star-full" aria-hidden="true"></div>
									<?php } else { ?>
										<div class="star star-empty" aria-hidden="true"></div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="mp-shop-review-detail">
						<p><b><?php echo wp_kses_post( $review->review_summary ); ?></b></p>
						<p><?php esc_html_e( 'By', 'wk-marketplace' ); ?> <b><?php echo esc_html( $review->nickname ); ?></b>
							, <?php echo esc_html( gmdate( 'd-F-Y', strtotime( $review->review_time ) ) ); ?></p>
						<p><?php echo esc_html( $review->review_desc ); ?></p>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
		echo wp_kses_post( $pagination['results'] );
		echo wp_kses_post( $pagination['pagination'] );
	}
	?>
</div>
