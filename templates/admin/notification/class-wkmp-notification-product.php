<?php
/**
 * Admin template Functions
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Notification;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Notification_Product' ) ) {

	/**
	 * Class WKMP_Notification_Product
	 *
	 * @package WkMarketplace\Templates\Admin\Notification
	 */
	class WKMP_Notification_Product {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Notification_Product constructor.
		 */
		public function __construct() {
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
		 * Show product notification data.
		 *
		 * @param object $db_obj DB object.
		 *
		 * @return void
		 */
		public function display_notification_content( $db_obj ) {
			$notifications = $db_obj->wkmp_get_notification_data( 'product', 'all' );
			$display       = array();

			foreach ( $notifications['data'] as $value ) {
				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );
				$interval  = $datetime1->diff( $datetime2 );

				$db_content = empty( $value['content'] ) ? '' : $value['content'];
				$db_content = $db_obj->wkmp_get_formatted_notification_content( $db_content );

				if ( ! empty( $value['context'] ) ) {
					$product_id = $value['context'];

					$product_title = get_the_title( $product_id );
					$edit_link     = admin_url( 'post.php?post=' . $product_id . '&action=edit' );
					$target        = '_blank';

					if ( empty( $product_title ) ) {
						$product_title = __( '(deleted-item)', 'wk-marketplace' );
						$edit_link     = 'javascript:void(0);';
						$target        = '';
					}

					$link    = '<a title="' . $product_id . '" href="' . $edit_link . '" target="' . $target . '"> #' . $product_title . ' </a>';
					$content = sprintf( /* translators: %1$s: URL, %2%s: Content, %3$s: Days. */ esc_html__( ' %1$s  %2$s %3$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $link, $db_content, $interval->days );
				} else {
					$content = sprintf( /* translators: %1$s: Content, %2%s: Days. */ esc_html__( ' %1$s %2$d  <strong> day(s) ago </strong>', 'wk-marketplace' ), $db_content, $interval->days );
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
