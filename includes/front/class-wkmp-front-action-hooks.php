<?php
/**
 * Front hooks template.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Front_Action_Hooks' ) ) {

	/**
	 * Front action hooks class.
	 */
	class WKMP_Front_Action_Hooks {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			$function_obj = WKMP_Front_Action_Functions::get_instance();

			add_action( 'wkmp_save_seller_ask_query', array( $function_obj, 'wkmp_after_save_seller_ask_query' ), 10, 2 );
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
}
