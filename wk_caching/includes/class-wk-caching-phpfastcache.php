<?php
/**
 * This PHP fastcache helper.
 *
 * @package WK_Caching
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use Phpfastcache\Helper\Psr16Adapter;

if ( ! class_exists( 'WK_Caching_PHPFastCache' ) ) {
	/**
	 * WK_Caching_PHPFastCache class.
	 */
	class WK_Caching_PHPFastCache {
		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * PSR 16 adapter.
		 *
		 * @var $psr_16_adapter
		 */
		protected $psr_16_adapter = null;

		/**
		 * Cached keys in FastCache.
		 *
		 * @var $cached_keys
		 */
		protected $cached_key_name = '';

		/**
		 * __construct.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->cached_key_name = '_wkwc_cache_keys'; // The key name to save all cache keys name in FastCache.
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
		 * Set the PHP fast cache contents by key and group.
		 *
		 * @param string $cache_key Cache key.
		 * @param mixed  $cache_value Cache value.
		 * @param string $cache_group Cache group.
		 * @param int    $expiry Default 1 hour.
		 */
		public function set( $cache_key, $cache_value, $cache_group = 'wkwc', $expiry = 3600 ) {
			$fast_key = '_wkwc_cache_' . $cache_group . '_' . $cache_key;

			$driver = $this->get_driver();

			if ( is_null( $this->psr_16_adapter ) ) {
				$this->psr_16_adapter = new Psr16Adapter( $driver );
			}

			$this->psr_16_adapter->set( $fast_key, $cache_value, $expiry );

			$all_keys = $this->psr_16_adapter->get( $this->cached_key_name );
			$all_keys = is_array( $all_keys ) ? $all_keys : array();

			array_push( $all_keys, $fast_key );

			$all_keys = array_unique( $all_keys );

			$this->psr_16_adapter->set( $this->cached_key_name, $all_keys ); // Never expires.
		}

		/**
		 * Get the driver viz. Files, Redis, Predis, Mongodb -> https://www.phpfastcache.com/
		 *
		 * @return bool
		 */
		protected function get_driver() {
			$driver = 'Files';

			$redis_enabled = apply_filters( 'wkwc_caching_redis_enabled', 'no' );

			if ( 'yes' === $redis_enabled ) {
				$redis_enabled = get_option( 'wkwc_caching_redis_enabled', 'no' );
			}

			if ( 'yes' === $redis_enabled ) {
				$driver = 'Redis';
			}

			return apply_filters( '_wkwc_get_fast_cache_driver', $driver );
		}

		/**
		 * Get the php fast cache contents by the key & group.
		 *
		 * @param string $cache_key Cache key.
		 * @param string $cache_group Cache Group.
		 *
		 * @return bool|mixed
		 */
		public function get( $cache_key, $cache_group = 'wkwc' ) {
			$fast_key = '_wkwc_cache_' . $cache_group . '_' . $cache_key;

			$driver = $this->get_driver();

			if ( is_null( $this->psr_16_adapter ) ) {
				$this->psr_16_adapter = new Psr16Adapter( $driver );
			}

			WK_Caching::log( "Get PHP Fast Cache, Driver: $driver key: $fast_key, cache key: $cache_key, Cache group: $cache_group" );

			$data = $this->psr_16_adapter->get( $fast_key );

			$data = empty( $data ) ? false : $data;

			$found = wc_bool_to_string( ! empty( $data ) );

			WK_Caching::log( "In PHP Fast Cache key: $fast_key, cache key: $cache_key, Data Found: $found, Data: " );

			return $data;
		}

		/**
		 * Get all cached data.
		 *
		 * @param string $type Data type. Keys count or Full data.
		 * @param array  $existing_keys Existing keys.
		 *
		 * @return bool|mixed
		 */
		public function get_all( $type = 'all_keys', $existing_keys = array() ) {
			if ( is_null( $this->psr_16_adapter ) ) {
				$this->psr_16_adapter = new Psr16Adapter( $this->get_driver() );
			}

			if ( empty( $existing_keys ) ) {
				$existing_keys = $this->psr_16_adapter->get( $this->cached_key_name );
				$existing_keys = empty( $existing_keys ) ? array() : $existing_keys;
			}

			if ( 'all_keys' === $type ) {
				return $existing_keys;
			}

			$data = array();

			foreach ( $existing_keys as $key ) {
				$data[ $key ] = $this->psr_16_adapter->get( $key );
			}

			return $data;
		}

		/**
		 * Get the php fast cache contents by the key & group.
		 *
		 * @param string $cache_key Cache key.
		 * @param string $cache_group Cache Group.
		 * @param bool   $force Force delete.
		 *
		 * @return bool|mixed
		 */
		public function delete( $cache_key, $cache_group = 'wkwc', $force = false ) {
			$fast_key = '_wkwc_cache_' . $cache_group . '_' . $cache_key;

			$driver = $this->get_driver();

			if ( is_null( $this->psr_16_adapter ) ) {
				$this->psr_16_adapter = new Psr16Adapter( $driver );
			}

			WK_Caching::log( "Delete PHP Fast Cache, Driver: $driver, key: $fast_key, cache key: $cache_key, Cache group: $cache_group" );

			$result    = false;
			$fast_keys = array();

			if ( $force ) {
				$result = $this->psr_16_adapter->clear();
			} else {
				$all_keys = $this->psr_16_adapter->get( $this->cached_key_name );
				$all_keys = is_array( $all_keys ) ? $all_keys : array();

				if ( ! empty( $all_keys ) ) {
					$input     = preg_quote( $fast_key, '~' ); // Get like keys.
					$fast_keys = preg_grep( '~' . $input . '~', $all_keys );

					if ( ! empty( $fast_keys ) ) { // Deleting all matching keys from cache.
						$result         = $this->psr_16_adapter->deleteMultiple( $fast_keys );
						$remaining_keys = array_diff( $all_keys, $fast_keys );

						$this->psr_16_adapter->set( $this->cached_key_name, $remaining_keys ); // Never expires.
					}
				}

				if ( empty( $fast_keys ) ) { // Deleting only one fast key as know like keys present.
					$result = $this->psr_16_adapter->delete( $fast_key );
					if ( in_array( $fast_key, $all_keys, true ) ) {
						unset( $all_keys[ array_search( $fast_key, $all_keys, true ) ] );
						$this->psr_16_adapter->set( $this->cached_key_name, $all_keys ); // Never expires.
					}
				}
			}
			return $result;
		}
	}
}
