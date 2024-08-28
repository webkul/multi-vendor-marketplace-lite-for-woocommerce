<?php
/**
 * Admin seller notification template function.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Notification;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Notification_Seller' ) ) {

	/**
	 * Class WKMP_Notification_Seller
	 *
	 * @package WkMarketplace\Templates\Admin\Notification
	 */
	class WKMP_Notification_Seller {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Notification_Seller constructor.
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
			global $wkmarketplace, $current_user;
			$notifications = $db_obj->wkmp_get_notification_data( 'seller' );
			$display       = array();

			foreach ( $notifications['data'] as $value ) {
				$datetime1 = new \DateTime( gmdate( 'F j, Y', strtotime( $value['timestamp'] ) ) );
				$datetime2 = new \DateTime( 'now' );
				$interval  = $datetime1->diff( $datetime2 );

				$db_content = empty( $value['content'] ) ? '' : $value['content'];
				$db_content = $db_obj->wkmp_get_formatted_notification_content( $db_content );

				$seller_id = empty( $value['author_id'] ) ? 0 : $value['author_id'];

				if ( ! empty( $seller_id ) && in_array( 'administrator', $current_user->roles, true ) ) {
					$seller_name = $wkmarketplace->wkmp_get_user_display_name( $seller_id, '', 'shop_name' );
					$db_content .= wp_sprintf( /* translators: %s: Seller profile. */ esc_html__( ' on the <strong> %s </strong> Profile', 'wk-marketplace' ), $seller_name );
				}

				if ( ! empty( $value['context'] ) ) {
					$customer_id  = $value['context'];
					$display_name = $wkmarketplace->wkmp_get_user_display_name( $customer_id );

					$content = wp_sprintf( /* translators: %1$s: Content, %2%s: Reviewer name, %3$s: Days. */ _n( ' %1$s from <strong> %2$s </strong> %3$d <strong> day ago.</strong>', ' %1$s from <strong> %2$s </strong> %3$d <strong> days ago.</strong>', $interval->days, 'wk-marketplace' ), $db_content, $display_name, $interval->days );
				} else {
					$content = wp_sprintf( /* translators: %1$s: Content, %2%s: Days. */ _n( ' %1$s %2$d <strong> day ago.</strong>', ' %1$s %2$d <strong> days ago.</strong>', $interval->days, 'wk-marketplace' ), $db_content, $interval->days );
				}

				$display[] = array(
					'content' => $content,
				);
			}
			?>
			<ul class="mp-notification-list">
				<?php
				if ( $display ) {
					foreach ( $display as $value ) {
						?>
						<li class="notification-link"><?php echo wp_kses_post( html_entity_decode( $value['content'] ) ); ?></li>
						<?php
					}
				} else {
					esc_html_e( 'No data Found', 'wk-marketplace' );
				}
				?>
			</ul>
			<?php
			echo wp_kses_post( html_entity_decode( $notifications['pagination'] ) );
		}
	}
}
