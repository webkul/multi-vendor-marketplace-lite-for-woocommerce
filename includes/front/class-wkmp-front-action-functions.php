<?php
/**
 * Front hooks template
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Front;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Front_Action_Functions' ) ) {
	/**
	 * Front action function class
	 */
	class WKMP_Front_Action_Functions {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Front_Action_Functions constructor.
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
		 * Save seller ask query info.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data Query data.
		 */
		public function wkmp_after_save_seller_ask_query( $seller_id, $data ) {
			$obj = Common\WKMP_Seller_Ask_Queries::get_instance();
			$obj->wkmp_seller_ask_query_save( $seller_id, $data );
		}
	}
}
