<?php
/**
 * Dynamically loads classes.
 *
 * @package WK_Caching
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WK_Caching_Autoload' ) ) {
	/**
	 * WK_Caching_Autoload class
	 */
	class WK_Caching_Autoload {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Autoload constructor.
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload_entities' ) );
		}

		/**
		 * Autoload classes, traits and other entities.
		 *
		 * @param string $entity_name The name of the class to load.
		 */
		public function autoload_entities( $entity_name ) {
			if ( 0 !== strpos( $entity_name, 'WK_Caching' ) ) {
				return;
			}

			$current_file = strtolower( $entity_name );
			$current_file = str_ireplace( '_', '-', $current_file );
			$file_name    = "class-{$current_file}.php";

			$filepath    = trailingslashit( dirname( __DIR__ ) );
			$file_exists = false;

			$all_paths = array(
				'includes',
			);

			foreach ( $all_paths as $the_path ) {
				$file_path = $filepath . $the_path . '/' . $file_name;

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
					$file_exists = true;
					break;
				}
			}

			// If the file exists in the specified path, then include it.
			if ( ! $file_exists ) {
				wp_die(
					sprintf( /* Translators: %d: product filepath. */ esc_html__( 'The file attempting to be loaded at %s does not exist.', 'wk_caching' ), esc_html( $file_path ) )
				);
			}
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
	}
	WK_Caching_Autoload::get_instance();
}
