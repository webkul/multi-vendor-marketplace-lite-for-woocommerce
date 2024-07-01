<?php
/**
 * The Core cache class.
 *
 * @package WK_Caching
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WK_Caching_Core' ) ) {
	/**
	 * WK_Caching_Core Class.
	 */
	class WK_Caching_Core {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
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
		 * Set the data in different cache on a key with group.
		 *
		 * @param string     $key The key on which data will be saved.
		 * @param mixed      $data Data to save.
		 * @param string|int $data_group The data group.
		 * @param int        $expiry Expiry in seconds in case of transient.
		 */
		public function set( $key, $data, $data_group, $expiry = 3600 ) {
			$cache_obj = WK_Caching_Object::get_instance();
			$cache_obj->set( $key, $data, $data_group );

			$transient_obj = WK_Caching_Transient::get_instance();
			$transient_obj->set( $key, $data, $data_group, $expiry );
		}

		/**
		 * Get the cached data by the key or data group.
		 *
		 * @param string     $key Key of the data.
		 * @param string|int $data_group The data group.
		 *
		 * @return bool|mixed
		 */
		public function get( $key, $data_group ) {
			$cache_obj = WK_Caching_Object::get_instance();
			$data      = $cache_obj->get( $key, $data_group );

			if ( ! empty( $data ) ) {
				return $data;
			}

			$transient_obj = WK_Caching_Transient::get_instance();

			return $transient_obj->get( $key, $data_group );
		}

		/**
		 * Get all cached data or keys.
		 *
		 * @param string     $type Return type. Data or keys.
		 * @param array      $keys Data keys, If $type is 'data' then all keys for which data is to be retrieved.
		 * @param string|int $data_group The data group.
		 *
		 * @return bool|mixed
		 */
		public function get_all( $type = '', $keys = array(), $data_group = '' ) {
			$transient_obj = WK_Caching_Transient::get_instance();
			return $transient_obj->get_all( $type, $keys, $data_group );
		}

		/**
		 * Reset the cache by group or complete reset by force param.
		 *
		 * @param string $key The key to reset.
		 * @param string $data_group The data group to rest.
		 * @param bool   $force Reset the complete cache object.
		 *
		 * @return bool
		 */
		public function reset( $key = '', $data_group = '', $force = false ) {
			$cache_obj = WK_Caching_Object::get_instance();
			$cache_obj->reset( $data_group, $force );

			$transient_obj = WK_Caching_Transient::get_instance();

			if ( $force ) {
				return $transient_obj->delete_force_transients();
			}

			if ( empty( $key ) ) {
				return $transient_obj->delete_all_transients( $data_group );
			}

			return $transient_obj->delete_transient( $key, $data_group );
		}
	}
}
