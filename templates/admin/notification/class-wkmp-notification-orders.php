<?php
/**
 * Admin template Functions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @since 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Notification;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Notification_Orders' ) ) {
	/**
	 * Admin seller profile templates class.
	 *
	 * Class WKMP_Notification_Orders
	 *
	 * @package WkMarketplace\Templates\Admin\Notification
	 */
	class WKMP_Notification_Orders {
		/**
		 * DB Object.
		 *
		 * @var Object $db_obj DB Object.
		 */
		private $db_obj;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Notification_Orders constructor.
		 *
		 * @param Object $db_object DB Object.
		 */
		public function __construct( $db_object = null ) {
			$this->db_obj = $db_object;
			add_action( 'wkmp_orders_all_content', array( $this, 'wkmp_orders_all_content' ), 10 );
			add_action( 'wkmp_orders_processing_content', array( $this, 'wkmp_orders_processing_content' ), 10 );
			add_action( 'wkmp_orders_completed_content', array( $this, 'wkmp_orders_completed_content' ), 10 );

			$this->wkmp_display_orders_notification_tabs();
		}

		/**
		 * Display notification tabs.
		 */
		public function wkmp_display_orders_notification_tabs() {
			$config_tabs = array(
				'all'        => esc_html__( 'All', 'wk-marketplace' ),
				'processing' => esc_html__( 'Processing', 'wk-marketplace' ),
				'completed'  => esc_html__( 'Completed', 'wk-marketplace' ),
			);

			$config_tabs = apply_filters( 'wkmp_notification_orders_tabs', $config_tabs );

			$wk_page         = \WK_Caching::wk_get_request_data( 'page' );
			$current_section = \WK_Caching::wk_get_request_data( 'section' );
			$tab             = \WK_Caching::wk_get_request_data( 'tab' );

			$current_section = empty( $current_section ) ? 'all' : $current_section;
			$tab             = empty( $tab ) ? 'orders' : $tab;

			$url = admin_url( 'admin.php?page=' . $wk_page . '&tab=' . $tab );
			?>
			<ul class="subsubsub">
				<?php foreach ( $config_tabs as $name => $lable ) { ?>
					<li>
						<a href="<?php echo esc_url( $url ) . '&section=' . esc_attr( $name ); ?>" class=" <?php echo ( $current_section === $name ) ? 'current' : ''; ?>"><?php echo esc_html( $lable ); ?></a>
						|
					</li>
				<?php } ?>
			</ul>
			<br class="clear">
			<?php
			do_action( 'wkmp_orders_' . esc_attr( $current_section ) . '_content' );
		}

		/**
		 * All content.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_orders_all_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'order' );
			$this->wkmp_display_notification( $notifications );
		}

		/**
		 * Order processing content.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_orders_processing_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'order', 'processing' );
			$this->wkmp_display_notification( $notifications );
		}

		/**
		 * Completed content.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_orders_completed_content() {
			$notifications = $this->db_obj->wkmp_get_notification_data( 'order', 'complete' );
			$this->wkmp_display_notification( $notifications );
		}

		/**
		 * Display notification.
		 *
		 * @param array $notifications Notification.
		 *
		 * @throws \Exception Throwing exception.
		 */
		public function wkmp_display_notification( $notifications ) {
			global $wkmarketplace;
			$display = array();

			foreach ( $notifications['data'] as $value ) {
				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );

				$interval = $datetime1->diff( $datetime2 );

				$db_content        = empty( $value['content'] ) ? '' : $value['content'];
				$formatted_content = $this->db_obj->wkmp_get_formatted_notification_content( $db_content );

				if ( $value['context'] ) {
					$url = admin_url( 'post.php?post=' . $value['context'] . '&action=edit' );

					if ( $wkmarketplace->wkmp_user_is_seller( get_current_user_id() ) ) {
						$url = admin_url( 'admin.php?page=order-history&action=view&oid=' . $value['context'] );
					}

					$link = '<a href="' . $url . '" target="_blank"> #' . $value['context'] . ' </a>';

					$content = wp_sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ _n( ' %1$s %2$s %3$d  <strong> day ago.</strong>', ' %1$s %2$s %3$d  <strong> days ago.</strong>', $interval->days, 'wk-marketplace' ), $link, $formatted_content, $interval->days );
				} else {
					$content = wp_sprintf( /* translators: %1$s: Content, %2%s: Days.  */ _n( ' %1$s %2$d <strong> day ago.</strong>', ' %1$s %2$d <strong> days ago.</strong>', $interval->days, 'wk-marketplace' ), $formatted_content, $interval->days );
				}

				$display[] = array(
					'content' => $content,
				);
			}
			?>
			<ul class="mp-notification-list">
				<?php if ( $display ) { ?>
					<?php foreach ( $display as $value ) { ?>
						<li class="notification-link"><?php echo wp_kses_post( html_entity_decode( $value['content'] ) ); ?></li>
					<?php } ?>
				<?php } else { ?>
					<?php esc_html_e( 'No data Found', 'wk-marketplace' ); ?>
				<?php } ?>
			</ul>
			<?php
			echo wp_kses_post( html_entity_decode( $notifications['pagination'] ) );
		}
	}
}
