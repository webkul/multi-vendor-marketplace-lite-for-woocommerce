<?php
/**
 * Add Feedback.
 *
 * @package WkMarketplace\Includes\Shipping
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

$current_user_id = get_current_user_id();

$is_pending_review = false;

foreach ( $review_check as $review_data ) {
	if ( 0 === intval( $review_data->status ) ) {
		$is_pending_review = true;
		break;
	}
}
$price_r   = empty( $posted_data['price_r'] ) ? 0 : $posted_data['price_r'];
$value_r   = empty( $posted_data['value_r'] ) ? 0 : $posted_data['value_r'];
$quality_r = empty( $posted_data['quality_r'] ) ? 0 : $posted_data['quality_r'];
?>
<div id="wkmp_seller_review_form" class="mp-profile-wrapper woocommerce">

	<?php $this->wkmp_seller_profile_details_section( 'add-feedback' ); ?>

	<?php if ( $current_user_id > 0 && intval( $this->seller_id ) !== $current_user_id && ! $is_pending_review ) { ?>
		<div class="mp-add-feedback-section">
			<h4><?php esc_html_e( 'Write your review', 'wk-marketplace' ); ?></h4>
			<b><p><?php esc_html_e( 'How do you rate this store ?', 'wk-marketplace' ); ?> <span class="wkmp-error-class">*</span></p></b>
			<form action="" class="mp-seller-review-form" method="post" enctype="multipart/form-data">
				<div class="wkmp_feedback_main_in">
					<div class="mp-feedback-price-rating mp-rating-input" data-id="#feed-price-rating">
						<p><?php esc_html_e( 'Price', 'wk-marketplace' ); ?></p>
						<?php if ( isset( $validation_errors['feed_price_error'] ) ) { ?>
							<div class="wkmp-text-danger"><?php echo esc_html( $validation_errors['feed_price_error'] ); ?></div>
						<?php } ?>
						<p class="stars <?php echo empty( $price_r ) ? '' : ' selected'; ?>">
						<span>
							<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
								<?php if ( $price_r === $i ) { ?>
									<a class="star-<?php echo esc_attr( $i ); ?> active"><?php echo esc_html( $i ); ?></a>
								<?php } else { ?>
									<a class="star-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
								<?php } ?>
							<?php } ?>
						</span>
						</p>
						<select name="feed_price" id="feed-price-rating" aria-required="true" style="display:none;">
							<option value=""></option>
							<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
								<?php if ( $price_r === $i ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" selected></option>
								<?php } else { ?>
									<option value="<?php echo esc_attr( $i ); ?>"></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="mp-feedback-value-rating mp-rating-input" data-id="#feed-value-rating">
						<p><?php esc_html_e( 'Value', 'wk-marketplace' ); ?></p>
						<?php if ( isset( $validation_errors['feed_value_error'] ) ) { ?>
							<div class="wkmp-text-danger"><?php echo esc_html( $validation_errors['feed_value_error'] ); ?></div>
						<?php } ?>
						<p class="stars <?php echo empty( $value_r ) ? '' : ' selected'; ?>">
						<span>
							<?php
							for ( $i = 1; $i <= 5; $i++ ) {
								if ( $value_r === $i ) {
									?>
									<a class="star-<?php echo esc_attr( $i ); ?> active"><?php echo esc_html( $i ); ?></a>
									<?php
								} else {
									?>
									<a class="star-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
									<?php
								}
							}
							?>
						</span>
						</p>
						<select name="feed_value" id="feed-value-rating" aria-required="true" style="display:none;">
							<option value=""></option>
							<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
								<?php if ( $value_r === $i ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" selected></option>
								<?php } else { ?>
									<option value="<?php echo esc_attr( $i ); ?>"></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="mp-feedback-quality-rating mp-rating-input" data-id="#feed-quality-rating">
						<p><?php esc_html_e( 'Quality', 'wk-marketplace' ); ?></p>
						<?php if ( isset( $validation_errors['feed_quality_error'] ) ) { ?>
							<div class="wkmp-text-danger"><?php echo esc_html( $validation_errors['feed_quality_error'] ); ?></div>
						<?php } ?>
						<p class="stars <?php echo empty( $quality_r ) ? '' : ' selected'; ?>">
						<span>
							<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
								<?php if ( $quality_r === $i ) { ?>
									<a class="star-<?php echo esc_attr( $i ); ?> active"><?php echo esc_html( $i ); ?></a>
								<?php } else { ?>
									<a class="star-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
								<?php } ?>
							<?php } ?>
						</span>
						</p>
						<select name="feed_quality" id="feed-quality-rating" aria-required="true" style="display:none;">
							<option value=""></option>
							<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
								<?php if ( $quality_r === $i ) { ?>
									<option value="<?php echo esc_attr( $i ); ?>" selected></option>
								<?php } else { ?>
									<option value="<?php echo esc_attr( $i ); ?>"></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>

				</div>

				<div class="wkmp-error-class" id="feedback-rate-error"></div>
				<div class="wkmp_feedback_fields_in">
					<p><b><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?><span class="wkmp-error-class">*</span></b></p>
					<input type="text" name="feed_summary" class="form-row-wide" value="<?php echo isset( $posted_data['review_summary'] ) ? esc_attr( $posted_data['review_summary'] ) : ''; ?>">
					<?php if ( isset( $validation_errors['feed_summary_error'] ) ) { ?>
						<div class="wkmp-text-danger"><?php echo esc_html( $validation_errors['feed_summary_error'] ); ?></div>
					<?php } ?>
				</div>
				<div class="wkmp_feedback_fields_in">
					<p><b><?php esc_html_e( 'Review', 'wk-marketplace' ); ?><span class="wkmp-error-class">*</span></b></p>
					<textarea rows="4" name="feed_review" class="form-row-wide"><?php echo isset( $posted_data['review_desc'] ) ? esc_html( $posted_data['review_desc'] ) : ''; ?></textarea>
					<?php if ( isset( $validation_errors['feed_description_error'] ) ) { ?>
						<div class="wkmp-text-danger"><?php echo esc_html( $validation_errors['feed_description_error'] ); ?></div>
					<?php } ?>
				</div>
				<?php wp_nonce_field( 'wkmp-add-feedback-nonce-action', 'wkmp-add-feedback-nonce' ); ?>
				<p><input type="submit" id="wk_mp_reviews_user" value="<?php esc_attr_e( 'Submit Review', 'wk-marketplace' ); ?>" class="button"></p>
			</form>
		</div>
		<?php
	}
	do_action( 'wkmp_after_add_review_form', $this->seller_id );
	?>
</div>
