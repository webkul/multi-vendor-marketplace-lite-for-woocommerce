<?php
/**
 * This object cache helper.
 *
 * @package WK_Caching
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WK_Caching_Object' ) ) {
	/**
	 * WK_Caching_Object Class.
	 */
	class WK_Caching_Object {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected $cache = array();

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
		 * Set the data in object cache on a key with group.
		 *
		 * @param string     $key The key on which data will be saved.
		 * @param mixed      $data Data to save.
		 * @param string|int $data_group The data group.
		 */
		public function set( $key, $data, $data_group = '0' ) {
			WK_Caching::log( "WK_Caching_Object Set Cache key: $key, Cache group: $data_group" );
			$this->cache[ $data_group ][ $key ] = $data;
		}

		/**
		 * Get the cached data by the key or data group.
		 *
		 * @param string     $key Key of the data.
		 * @param string|int $data_group The data group.
		 *
		 * @return bool|mixed
		 */
		public function get( $key, $data_group = '0' ) {
			if ( isset( $this->cache[ $data_group ] ) && isset( $this->cache[ $data_group ][ $key ] ) && ! empty( $this->cache[ $data_group ][ $key ] ) ) {
				return $this->cache[ $data_group ][ $key ];
			}

			return false;
		}

		/**
		 * Reset the cache by group or complete reset by force param.
		 *
		 * @param string $data_group The data group.
		 * @param bool   $force Reset the complete cache object.
		 *
		 * @return void
		 */
		public function reset( $data_group = '0', $force = false ) {
			if ( true === $force ) {
				$this->cache = array();
			} elseif ( isset( $this->cache[ $data_group ] ) ) {
				$this->cache[ $data_group ] = array();
			}
		}
	}
}
