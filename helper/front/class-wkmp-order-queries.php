<?php
/**
 * Order queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Order_Queries' ) ) {

	/**
	 * Order queries class
	 */
	class WKMP_Order_Queries {
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
		 * Seller Orders map data.
		 *
		 * @param array $sellers Sellers.
		 * @param int   $order_id Order id.
		 */
		public function wkmp_update_seller_orders( $sellers, $order_id ) {
			$wpdb_obj = $this->wpdb;
			foreach ( is_iterable( $sellers ) ? $sellers : array() as $seller_id ) {
				$wpdb_obj->insert(
					$wpdb_obj->prefix . 'mpseller_orders',
					array(
						'order_id'  => $order_id,
						'seller_id' => $seller_id,
					),
					array( '%d', '%d' )
				);
			}
		}

		/**
		 * Seller Orders map data
		 *
		 * @param int $order_id Order id.
		 *
		 * @return bool
		 */
		public function wkmp_check_seller_order_exists( $order_id ) {
			$wpdb_obj = $this->wpdb;
			$query    = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT order_id FROM {$wpdb_obj->prefix}mporders WHERE order_id = %d", $order_id ) );

			return $query ? true : false;
		}

		/**
		 * Insert order data.
		 *
		 * @param array $data Data.
		 */
		public function wkmp_insert_mporders_data( $data ) {
			$wpdb_obj = $this->wpdb;
			if ( $data ) {
				$wpdb_obj->insert( "{$wpdb_obj->prefix}mporders", $data );
			}
		}

		/**
		 * Insert meta data.
		 *
		 * @param array $data Data.
		 */
		public function wkmp_insert_mporders_meta_data( $data ) {
			$wpdb_obj = $this->wpdb;
			if ( $data ) {
				$wpdb_obj->insert( $wpdb_obj->prefix . 'mporders_meta', $data );
			}
		}

		/**
		 * Seller Order info
		 *
		 * @param int $order_id Order id.
		 * @param int $seller_id Seller id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_seller_order( $order_id, $seller_id ) {
			$wpdb_obj   = $this->wpdb;
			$sql        = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpseller_orders WHERE seller_id = %d AND order_id = %d", $seller_id, $order_id );
			$order_info = $wpdb_obj->get_row( $sql );

			return apply_filters( 'wkmp_get_seller_order', $order_info, $order_id, $seller_id );
		}
	}
}
