<?php
/**
 * This class is a main loader class for all caching core files.
 *
 * @package WK_Caching
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WK_Caching' ) ) {
	/**
	 * WK_Caching Class.
	 */
	class WK_Caching {
		/**
		 * Constructor.
		 */
		public function __construct() {
		}

		/**
		 * Init function hooked on `admin_init`
		 * Set the required variables and register some important hooks
		 */
		public static function init() {
			self::define_constants();
			add_action( 'init', array( __CLASS__, 'localization' ) );
			add_action( 'plugins_loaded', array( __CLASS__, 'initialize' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'wk_caching_admin_scripts' ) );
			add_action( 'wp_footer', array( __CLASS__, 'wk_caching_front_footer_info' ) );
		}

		/**
		 * Define constants.
		 */
		public static function define_constants() {
			defined( 'WK_CACHING_VERSION' ) || define( 'WK_CACHING_VERSION', '1.0.8' );
			defined( 'WK_CACHING_SUBMODULE_URL' ) || define( 'WK_CACHING_SUBMODULE_URL', plugin_dir_url( __DIR__ ) );
			defined( 'WKMP_ALLOWED_WKMP_SELLER_DATA_COUNT' ) || define( 'WKMP_ALLOWED_WKMP_SELLER_DATA_COUNT', '-005.03' ); // Adding negative decimal values to avoid search. We'll use absint where we'll use it.
		}

		/**
		 * Admin enqueue scripts.
		 *
		 * @return void
		 */
		public static function wk_caching_admin_scripts() {
			wp_enqueue_style( 'wkwc_caching_style', WK_CACHING_SUBMODULE_URL . '/assets/css/wkwc-caching.css', array(), WK_CACHING_VERSION );
		}

		/**
		 * Show caching settings.
		 */
		public static function wkwc_show_caching_settings() {
			$save = self::wk_get_request_data( 'wk_caching_set_save', array( 'method' => 'post' ) );

			if ( ! empty( $save ) ) {
				$enabled       = self::wk_get_request_data( 'wkwc_caching_enabled', array( 'method' => 'post' ) );
				$redis_enabled = self::wk_get_request_data( 'wkwc_caching_redis_enabled', array( 'method' => 'post' ) );

				$enabled       = empty( $enabled ) ? 'no' : 'yes';
				$redis_enabled = empty( $redis_enabled ) ? 'no' : 'yes';

				update_option( 'wkwc_caching_enabled', $enabled );
				update_option( 'wkwc_caching_redis_enabled', $redis_enabled );
			}
			$enabled       = get_option( 'wkwc_caching_enabled', 'no' );
			$redis_enabled = get_option( 'wkwc_caching_redis_enabled', 'no' );
			?>
			<h1 class=wkwc_caching_title><?php esc_html_e( 'Webkul Core Caching', 'wk_caching' ); ?></h1>
			<div class="wrap">
				<form method="post">
					<table class="wkwc_caching_setting_table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable Caching', 'wk_caching' ); ?></th>
							<td><input <?php checked( $enabled, 'yes', true ); ?> type="checkbox" name="wkwc_caching_enabled" value="yes"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Redis Server is installed and Running?', 'wk_caching' ); ?></th>
							<td><input <?php checked( $redis_enabled, 'yes', true ); ?> type="checkbox" name="wkwc_caching_redis_enabled" value="yes"></td>
						</tr>
						<tr>
							<td>
								<?php submit_button( esc_attr__( 'Save', 'wk_caching' ), 'primary', 'wk_caching_set_save' ); ?>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<?php

			$all_keys = array();

			if ( class_exists( 'WK_Caching_Core' ) ) {
				$cache_obj = \WK_Caching_Core::get_instance();

				$clear_cache = self::wk_get_request_data( 'wkwc_caching_clear' );

				if ( 'yes' === $clear_cache ) {
					$cache_obj->reset( '', '', true );
				}

				$all_keys = $cache_obj->get_all( 'all_keys' );
			}

			echo '<p><a class="wk_caching_clear_all" href="' . esc_url( admin_url( 'admin.php?page=wkwc_caching&wkwc_show_caching=yes&wkwc_caching_clear=yes' ) ) . '">' . esc_html__( 'Clear all', 'wk_caching' ) . '</a></p>';

			if ( 'yes' === $redis_enabled ) {
				echo '<p>' . wp_sprintf( /* Translators: %s: Saved cache keys count. */ esc_html__( ' Saved keys count on PHPFastcache: %s', 'wk_caching' ), count( $all_keys ) ) . '</p>';
				echo '<p><a class="wk_caching_show_keys" href="' . esc_url( admin_url( 'admin.php?page=wkwc_caching&wkwc_show_caching=yes&wkwc_caching_show_all=keys' ) ) . '">' . esc_html__( 'Show Keys on PHPFastcache', 'wk_caching' ) . '</a></p>';
				echo '<p><a class="wk_caching_show_data" href="' . esc_url( admin_url( 'admin.php?page=wkwc_caching&wkwc_show_caching=yes&wkwc_caching_show_all=data' ) ) . '">' . esc_html__( 'Show Data on PHPFastcache', 'wk_caching' ) . '</a></p>';
			}

			if ( class_exists( 'WK_Caching_Core' ) ) {
				$cache_obj     = \WK_Caching_Core::get_instance();
				$show_all_type = self::wk_get_request_data( 'wkwc_caching_show_all' );

				if ( ! empty( $show_all_type ) ) {
					if ( 'data' === $show_all_type ) {
						$data = $cache_obj->get_all( 'data', $all_keys );
						echo '<pre>';
						print_r( wc_clean( $data ) );
						echo '</pre>';
					} else {
						echo '<pre>';
						print_r( wc_clean( $all_keys ) );
						echo '</pre>';
					}
				}
			}
		}

		/**
		 * Localization.
		 *
		 * @return void
		 */
		public static function localization() {
			load_plugin_textdomain( 'wk_caching', false, plugin_basename( dirname( __DIR__ ) ) . '/languages' );
		}

		/**
		 * Initialization.
		 *
		 * @return void
		 */
		public static function initialize() {
			$caching_enabled = apply_filters( 'wkwc_caching_enabled', true );

			if ( $caching_enabled ) {
				add_action( 'admin_menu', array( __CLASS__, 'wkwc_add_caching_tools' ), 99 );

				$enabled = get_option( 'wkwc_caching_enabled', 'yes' );

				if ( 'yes' === $enabled ) {
					// Load core auto-loader.
					require dirname( __DIR__ ) . '/inc/class-wk-caching-autoload.php';

					if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
						require dirname( __DIR__ ) . '/vendor/autoload.php';
					}
				}
			}
		}

		/**
		 * Add caching tools.
		 *
		 * @return void
		 */
		public static function wkwc_add_caching_tools() {
			if ( defined( 'WKWC_DEV' ) && true === WKWC_DEV ) {
				add_management_page( esc_html__( 'WK Caching', 'wk_caching' ), esc_html__( 'WK Caching', 'wk_caching' ), 'manage_options', 'wkwc_caching', array( __CLASS__, 'wkwc_caching_tools_callback' ), 99 );
			}
		}

		/**
		 * Caching Tools callback.
		 *
		 * @return void
		 */
		public static function wkwc_caching_tools_callback() {
			$wk_page = self::wk_get_request_data( 'page' );

			if ( ! empty( $wk_page ) && 'wkwc_caching' === $wk_page && class_exists( 'WK_Caching' ) ) {
				self::wkwc_show_caching_settings();
			}
		}

		/**
		 * Log function for debugging.
		 *
		 * @param mixed  $message Message string or array.
		 * @param array  $context Additional parameter, like file name 'source'.
		 * @param string $level One of the following:
		 *     'emergency': System is unusable.
		 *     'alert': Action must be taken immediately.
		 *     'critical': Critical conditions.
		 *     'error': Error conditions.
		 *     'warning': Warning conditions.
		 *     'notice': Normal but significant condition.
		 *     'info': Informational messages.
		 *     'debug': Debug-level messages.
		 */
		public static function log( $message, $context = array(), $level = 'info' ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				$log_enabled = apply_filters( 'wk_caching_is_log_enabled', true );

				if ( $log_enabled ) {
					$source            = ( is_array( $context ) && ! empty( $context['source'] ) ) ? $context['source'] : 'wk_caching';
					$context['source'] = $source;
					$logger            = wc_get_logger();
					$current_user_id   = get_current_user_id();

					$in_action = wp_sprintf( ( /* translators: %s current user id */ esc_html__( 'User in action: %s: ', 'wk_caching' ) ), $current_user_id );
					$message   = $in_action . $message;

					$logger->log( $level, $message, $context );
				}
			}
		}

		/**
		 * Get request data.
		 *
		 * @param string $key Key to get the data.
		 * @param array  $args Arguments to get the request data.
		 *
		 * @return bool|int|string|void|array|object
		 */
		public static function wk_get_request_data( $key, $args = array() ) {
			if ( empty( $key ) ) {
				return '';
			}

			$method  = empty( $args['method'] ) ? 'get' : sanitize_text_field( wp_unslash( $args['method'] ) );
			$filter  = empty( $args['filter'] ) ? 'string' : sanitize_text_field( wp_unslash( $args['filter'] ) );
			$default = empty( $args['default'] ) ? null : sanitize_text_field( wp_unslash( $args['default'] ) );
			$flag    = empty( $args['flag'] ) ? '' : sanitize_text_field( wp_unslash( $args['flag'] ) );

			$method     = ( 'get' === $method ) ? INPUT_GET : INPUT_POST;
			$filter_int = ( 'int' === $filter ) ? FILTER_SANITIZE_NUMBER_INT : FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			$filter_int = ( 'float' === $filter ) ? FILTER_SANITIZE_NUMBER_FLOAT : $filter_int;
			$filter_int = ( 'email' === $filter ) ? FILTER_SANITIZE_EMAIL : $filter_int;

			if ( ! empty( $flag ) && 'array' === $flag ) {
				$flag_value = ( 'array' === $flag ) ? FILTER_REQUIRE_ARRAY : FILTER_REQUIRE_SCALAR;
				$data       = filter_input( $method, $key, $filter_int, $flag_value );

				if ( empty( $data ) ) {
					return array();
				}

				if ( 519 === $filter_int ) { // Int.
					return empty( $data ) ? array() : map_deep(
						wp_unslash( $data ),
						function ( $value ) {
							return empty( $value ) ? $value : intval( $value );
						}
					);
				}
				if ( 520 === $filter_int ) { // Float.
					return empty( $data ) ? array() : map_deep(
						wp_unslash( $data ),
						function ( $value ) {
							return empty( $value ) ? $value : floatval( $value );
						}
					);
				}
				return empty( $data ) ? array() : map_deep( wp_unslash( $data ), 'sanitize_text_field' );
			}

			$data = filter_input( $method, $key, $filter_int );

			if ( 520 === $filter_int && 'array' !== $flag ) {
				$flag_value = ( 'fraction' === $flag ) ? FILTER_FLAG_ALLOW_FRACTION : FILTER_FLAG_ALLOW_THOUSAND;
				$data       = filter_input( $method, $key, $filter_int, $flag_value );
			}

			if ( empty( $data ) ) {
				return $default;
			}

			if ( 519 === $filter_int ) { // Int.
				return intval( wp_unslash( $data ) );
			}
			if ( 520 === $filter_int ) { // Float.
				return floatval( wp_unslash( $data ) );
			}
			if ( 517 === $filter_int ) { // Email.
				return sanitize_email( wp_unslash( $data ) );
			}

			return sanitize_text_field( wp_unslash( $data ) );
		}

		/**
		 * Wrapper for admin notice.
		 *
		 * @param  string $message The notice message.
		 * @param  string $type Notice type like info, error, success.
		 * @param  array  $args Additional arguments for wp-6.4.
		 *
		 * @return void
		 */
		public static function wk_show_notice_on_admin( $message = '', $type = 'error', $args = array() ) {
			if ( ! empty( $message ) ) {

				if ( function_exists( 'wp_admin_notice' ) ) {
					$args         = is_array( $args ) ? $args : array();
					$args['type'] = empty( $args['type'] ) ? $type : $args['type'];

					wp_admin_notice( $message, $args );
				} else {
					?>
				<div class="<?php echo esc_attr( $type ); ?>"><p><?php echo wp_kses_post( $message ); ?></p></div>
					<?php
				}
			}
		}

		/**
		 * Show current plugin version and last working day on front end.
		 *
		 * @hooked wp_footer Action hook.
		 *
		 * @return void
		 */
		public static function wk_caching_front_footer_info() {
			$show_info = self::wk_get_request_data( 'wkmodule_info', array( 'filter' => 'int' ) );
			$show_info = empty( $show_info ) ? 0 : intval( $show_info );
			if ( 200 === $show_info ) {
				?>
			<input type="hidden" data-lwdt="202401101320" data-wk_caching_version="<?php echo esc_attr( WK_CACHING_VERSION ); ?>" data-wk_caching_slug="wk_caching">
				<?php
			}
		}
	}
	WK_Caching::init();
}
