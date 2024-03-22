<?php
/**
 * Seller product at front.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Ask_To_Admin' ) ) {
	/**
	 * Ask to Admin class.
	 *
	 * Class WKMP_Ask_To_Admin
	 *
	 * @package WkMarketplace\Templates\Front\Seller
	 */
	class WKMP_Ask_To_Admin {
		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		private $seller_id;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Ask_Queries $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Ask_To_Admin constructor.
		 *
		 * @param int $seller_id Seller id.
		 */
		public function __construct( $seller_id = 0 ) {
			$this->db_obj    = Common\WKMP_Seller_Ask_Queries::get_instance();
			$this->seller_id = $seller_id;
		}

		/**
		 * Ensures only one instance of this class is loaded or can be loaded.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}

		/**
		 * Seller queries list.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_seller_queries_list( $seller_id ) {
			global $wkmarketplace, $wp;

			$this->seller_id = empty( $this->seller_id ) ? $seller_id : $this->seller_id;
			$nonce           = \WK_Caching::wk_get_request_data( 'wkmp-sellerAskQuery-nonce', array( 'method' => 'post' ) );

			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp-sellerAskQuery-nonce-action' ) ) {
				$message = empty( $_POST['message'] ) ? '' : wc_clean( wp_unslash( $_POST['message'] ) );
				$subject = empty( $_POST['subject'] ) ? '' : wc_clean( wp_unslash( $_POST['subject'] ) );

				if ( empty( $message ) || empty( $subject ) ) {
					wc_print_notice( esc_html__( 'Invalid (or empty) subject and/or message.', 'wk-marketplace' ), 'error' );
				} elseif ( ! preg_match( '/^[a-z0-9 .\-_]+$/i', $subject ) ) {
					wc_print_notice( esc_html__( 'Subject Invalid. Accept only alphanumeric, undescores(-) and hyphens (_) upto 100 characters.', 'wk-marketplace' ), 'error' );
				} else {
					$data = array(
						'message' => $message,
						'subject' => $subject,
					);
					do_action( 'wkmp_save_seller_ask_query', $this->seller_id, $data );
					wc_print_notice( esc_html__( 'Ask to admin query submitted successfully.', 'wk-marketplace' ), 'success' );
				}
			}

			$query_vars = $wp->query_vars;
			$endpoint   = get_option( '_wkmp_asktoadmin_endpoint', 'seller-asktoadmin' );
			$query_args = empty( $query_vars[ $endpoint ] ) ? 0 : $query_vars[ $endpoint ];
			$page_no    = get_query_var( 'pagenum' ) ? get_query_var( 'pagenum' ) : 1;

			if ( ! empty( $query_args ) ) {
				$args_array = explode( '/', $query_args );
				$page_no    = ( is_array( $args_array ) && count( $args_array ) > 1 && 'page' === $args_array[0] ) ? $args_array[1] : $page_no;
			}

			$filter_name = '';
			$nonce       = \WK_Caching::wk_get_request_data( 'wkmp_query_search_nonce' );

			// Filter ask queries.
			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'wkmp_query_search_nonce_action' ) ) {
				$filter_name = \WK_Caching::wk_get_request_data( 'wkmp_search' );
			}

			$limit = 20;

			$filter_data = array(
				'offset'    => ( $page_no - 1 ) * $limit,
				'limit'     => $limit,
				'search'    => $filter_name,
				'seller_id' => $this->seller_id,
			);

			$queries       = $this->db_obj->wkmp_get_all_seller_queries( $filter_data );
			$total_queries = $this->db_obj->wkmp_get_total_seller_queries( $filter_data );

			$url        = get_permalink() . get_option( '_wkmp_asktoadmin_endpoint', 'seller-asktoadmin' );
			$pagination = $wkmarketplace->wkmp_get_pagination( $total_queries, $page_no, $limit, $url );

			?>
			<form method="GET" id="wkmp-query-list-form">
				<div class="wkmp-table-action-wrap">
					<div class="wkmp-action-section left">
						<input type="text" name="wkmp_search" placeholder="<?php esc_attr_e( 'Search by subject', 'wk-marketplace' ); ?>" value="<?php echo esc_attr( $filter_name ); ?>">
						<?php wp_nonce_field( 'wkmp_query_search_nonce_action', 'wkmp_query_search_nonce' ); ?>
						<input type="submit" value="<?php esc_attr_e( 'Search', 'wk-marketplace' ); ?>" data-action="search"/>
					</div>
					<div class="wkmp-action-section right wkmp-text-right">
						<button type="button" class="button" id="wkmp-ask-query" data-modal_src="#wkmp-seller-query-modal" title="<?php esc_attr_e( 'Ask Query', 'wk-marketplace' ); ?>">
							<span class="dashicons dashicons-plus-alt"></span></button>
					</div>
				</div>
			</form>
			<div class="wkmp-table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
					<tr>
						<td><?php esc_html_e( 'Date', 'wk-marketplace' ); ?></td>
						<td><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?></td>
						<td><?php esc_html_e( 'Message', 'wk-marketplace' ); ?></td>
					</tr>
					</thead>
					<tbody>
					<?php if ( $queries ) { ?>
						<?php foreach ( $queries as $query ) { ?>
							<tr>
								<td><?php echo esc_html( gmdate( get_option( 'date_format' ), strtotime( $query['create_date'] ) ) ); ?></td>
								<td><?php echo esc_html( wp_unslash( $query['subject'] ) ); ?></td>
								<td><?php echo esc_html( wp_unslash( $query['message'] ) ); ?></td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td colspan="4" class="wkmp-text-center"><?php esc_html_e( 'No Data Found', 'wk-marketplace' ); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div><!-- wkmp-table-responsive end here-->
			<?php
			echo wp_kses_post( $pagination['results'] );
			echo wp_kses_post( $pagination['pagination'] );
			?>

			<div id="wkmp-seller-query-modal" class="wkmp-popup-modal">
				<!-- Modal content -->
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"><?php esc_html_e( 'Ask you query', 'wk-marketplace' ); ?></h4>
					</div>
					<div class="modal-body wkmp-form-wrap">
						<form action="" method="post" enctype="multipart/form-data" id="wkmp-seller-query-form">
							<div class="form-group">
								<label for="wkmp-subject"><?php esc_html_e( 'Subject', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
								<input class="form-control" type="text" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'wk-marketplace' ); ?>" id="wkmp-subject" value="">
								<div id="wkmp-subject-error" class="wkmp-text-danger"></div>
							</div>
							<div class="form-group">
								<label for="wkmp-message"><?php esc_html_e( 'Message', 'wk-marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label>
								<textarea rows="4" name="message" id="wkmp-message" placeholder="<?php esc_attr_e( 'Message', 'wk-marketplace' ); ?>"></textarea>
								<div id="wkmp-message-error" class="wkmp-text-danger"></div>
							</div>
							<?php wp_nonce_field( 'wkmp-sellerAskQuery-nonce-action', 'wkmp-sellerAskQuery-nonce' ); ?>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="button close-modal"><?php esc_html_e( 'Close', 'wk-marketplace' ); ?></button>
						<button id="wkmp-submit-ask-form" type="button" form="wkmp-seller-query-form" class="button"><?php esc_html_e( 'Submit', 'wk-marketplace' ); ?></button>
					</div>
				</div>

			</div>
			<?php
		}
	}
}
