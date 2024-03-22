<?php
/**
 * Seller Shipping DB actions.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Shipping' ) ) {

	/**
	 * Seller Shipping related DB queries class
	 */
	class WKMP_Seller_Shipping {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
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
		 * Get seller Shipping zone count
		 *
		 * @param int $zone_id Zone Id.
		 *
		 * @return int $total
		 */
		public function wkmp_get_shipping_zone_count( $zone_id ) {
			$wpdb_obj = $this->wpdb;

			$zone_count = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT count(*) as total from {$wpdb_obj->prefix}mpseller_meta where zone_id = %d", $zone_id ) );

			return apply_filters( 'wkmp_get_seller_shipping_zone_count', $zone_count, $zone_id );
		}

		/**
		 * Insert new shipping zone.
		 *
		 * @param array $zone_data Shipping zone data.
		 *
		 * @return int
		 */
		public function wkmp_insert_seller_shipping_zone( $zone_data ) {
			$wpdb_obj = $this->wpdb;
			return $wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpseller_meta',
				$zone_data
			);
		}

		/**
		 * Deleting shipping zone.
		 *
		 * @param int $zone_id Shipping zone id.
		 *
		 * @return int
		 */
		public function wkmp_delete_shipping_zone( $zone_id ) {
			$wpdb_obj = $this->wpdb;
			return $wpdb_obj->delete( $wpdb_obj->prefix . 'mpseller_meta', array( 'zone_id' => $zone_id ), array( '%d' ) );
		}
	}
}
