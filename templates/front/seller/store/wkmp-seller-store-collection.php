<?php
/**
 * Store collection.
 *
 * @package WkMarketplace\Includes\Shipping
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.
?>
<div class="mp-profile-wrapper woocommerce">
	<?php $this->wkmp_seller_profile_details_section( 'store-collection' ); ?>
	<div class="mp-seller-recent-product">
		<h3><?php echo esc_html( get_option( '_wkmp_seller_product_endpoint_name', esc_html__( 'All Products', 'wk-marketplace' ) ) ); ?></h3>
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

	<?php do_action( 'wkmp_after_seller_store_collection' ); ?>

</div>
