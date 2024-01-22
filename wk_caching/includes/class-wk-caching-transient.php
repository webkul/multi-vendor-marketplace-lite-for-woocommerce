<?php
/**
 * Transient caching.
 *
 * @package WK_Caching
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WK_Caching_Transient' ) ) {
	/**
	 * WK_Caching_Transient class.
	 */
	class WK_Caching_Transient {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WK_Caching_Transient constructor.
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
		 * Set the transient contents by key and group within page scope.
		 *
		 * @param string $transient_key Transient key.
		 * @param mixed  $transient_data Transient Data.
		 * @param string $transient_group Transient group.
		 * @param int    $expiry Default 1 hour.
		 */
		public function set( $transient_key, $transient_data, $transient_group = 'wkwc', $expiry = 3600 ) {
			$option_key = '_wkwc_transient_' . $transient_group . '_' . $transient_key;

			$transient_value = array(
				'time'  => time() + (int) $expiry,
				'value' => $transient_data,
			);

			$phpfast_caching_enabled   = $this->is_phpfast_caching_enabled();
			$file_writing_enabled      = $this->is_file_writing_enabled();
			$transient_caching_enabled = $this->is_transient_caching_enabled();

			WK_Caching::log( "WK_Caching_Transient Set transient key: $transient_key, Option key: $option_key, Transient group: $transient_group, Expiry: $expiry, PHP Fast enabled: $phpfast_caching_enabled, File writing enabled: $file_writing_enabled, Transient caching enabled: $transient_caching_enabled" );

			if ( $phpfast_caching_enabled ) {
				$phpfast = WK_Caching_PHPFastCache::get_instance();
				$phpfast->set( $transient_key, $transient_value, $transient_group, $expiry );
			} elseif ( $file_writing_enabled ) {
				$file_api = new WK_Caching_File( $transient_group . '-transient' );
				$file_api->touch( $option_key );

				if ( $file_api->is_writable( $option_key ) && $file_api->is_readable( $option_key ) ) {
					$transient_value = maybe_serialize( $transient_value );
					$file_api->put_contents( $option_key, $transient_value );
					WK_Caching::log( "Data written in file, transient key: $transient_key, Option key: $option_key, Transient group: $transient_group, Expiry: $expiry" );
				} elseif ( $transient_caching_enabled ) {
					// wkwc folder is not writable.
					WK_Caching::log( "Data written in transient, transient key: $transient_key, Option key: $option_key" );
					update_option( $option_key, $transient_value, false );
				}
			} elseif ( $transient_caching_enabled ) {
				// WK file API method not available.
				WK_Caching::log( "Cache Data written in transient, transient key: $transient_key, Option key: $option_key" );
				update_option( $option_key, $transient_value, false );
			}
		}

		/**
		 * Get the transient contents by the transient key or group.
		 *
		 * @param string $transient_key Transient Key.
		 * @param string $transient_group Transient Group.
		 *
		 * @return bool|mixed
		 */
		public function get( $transient_key, $transient_group = 'wkwc' ) {
			$option_key = '_wkwc_transient_' . $transient_group . '_' . $transient_key;

			$phpfast_caching_enabled   = $this->is_phpfast_caching_enabled();
			$file_writing_enabled      = $this->is_file_writing_enabled();
			$transient_caching_enabled = $this->is_transient_caching_enabled();

			WK_Caching::log( "Get transient key: $transient_key, Option key: $option_key, Transient group: $transient_group, File writing: $file_writing_enabled, Transient caching enabled: $transient_caching_enabled, PHP Fast enabled: $phpfast_caching_enabled" );

			if ( $phpfast_caching_enabled ) {
				$phpfast = WK_Caching_PHPFastCache::get_instance();
				$data    = $phpfast->get( $transient_key, $transient_group );

				$data  = maybe_unserialize( $data );
				$value = $this->get_value( $option_key, $data );

				if ( false !== $value ) {
					WK_Caching::log( "Data found in PHP Fast cache, Transient key: $transient_key, Transient group: $transient_group, Option key: $option_key" );
					return $value;
				}
			}

			if ( $file_writing_enabled ) {
				$file_api = new WK_Caching_File( $transient_group . '-transient' );
				if ( $file_api->is_writable( $option_key ) && $file_api->is_readable( $option_key ) ) {
					$data  = $file_api->get_contents( $option_key );
					$data  = maybe_unserialize( $data );
					$value = $this->get_value( $option_key, $data );

					if ( false !== $value ) {
						WK_Caching::log( "Data found in file, Transient key: $transient_key, Option key: $option_key" );
						return $value;
					}

					$file_api->delete( $option_key );
				}
			}

			if ( ! $transient_caching_enabled ) {
				return false;
			}

			// WK file api method is not available.
			$data = get_option( $option_key, false );
			if ( false === $data ) {
				return false;
			}

			WK_Caching::log( "Data found in Transient with key: $transient_key, Option key: $option_key" );

			return $this->get_value( $option_key, $data, true );
		}

		/**
		 * Get all cached data.
		 *
		 * @param string $type Return type.
		 *
		 * @return bool|mixed
		 */
		public function get_all( $type = '' ) {
			$phpfast_caching_enabled   = $this->is_phpfast_caching_enabled();
			$file_writing_enabled      = $this->is_file_writing_enabled();
			$transient_caching_enabled = $this->is_transient_caching_enabled();

			WK_Caching::log( "Get all cached data, Type: $type, PHP Fast enabled: $phpfast_caching_enabled, File writing Enabled: $file_writing_enabled, Transient caching enabled: $transient_caching_enabled," );

			if ( $phpfast_caching_enabled ) {
				$phpfast = WK_Caching_PHPFastCache::get_instance();
				$data    = $phpfast->get_all( $type );

				$data = maybe_unserialize( $data );

				if ( ! empty( $data ) ) {
					WK_Caching::log( 'All data found from PHP Fast cache' );
					return $data;
				}
			}

			return array();
		}

		/**
		 * Get transient value.
		 *
		 * @param string $transient_key Transient key.
		 * @param string $data JSON data.
		 * @param bool   $db_call is DB call.
		 *
		 * @return bool|string
		 */
		public function get_value( $transient_key, $data, $db_call = false ) {
			$current_time = time();
			if ( is_array( $data ) && isset( $data['time'] ) ) {
				if ( $current_time > (int) $data['time'] ) {
					if ( true === $db_call ) {
						delete_option( $transient_key );
					}

					return false;
				}
				return $data['value'];
			}

			return false;
		}

		/**
		 * Delete the transient by key.
		 *
		 * @param string $transient_key Transient key.
		 * @param string $transient_group Transient group.
		 *
		 * @return bool
		 */
		public function delete_transient( $transient_key, $transient_group = 'wkwc' ) {
			$deleted    = false;
			$option_key = '_wkwc_transient_' . $transient_group . '_' . $transient_key;

			$phpfast_caching_enabled   = $this->is_phpfast_caching_enabled();
			$file_writing_enabled      = $this->is_file_writing_enabled();
			$transient_caching_enabled = $this->is_transient_caching_enabled();

			WK_Caching::log( "Deleting transient and file, Option key: $option_key, File writing: $file_writing_enabled, Transient caching enabled: $transient_caching_enabled, PHP Fast enabled: $phpfast_caching_enabled" );

			if ( $phpfast_caching_enabled ) {
				$phpfast = WK_Caching_PHPFastCache::get_instance();
				$deleted = $phpfast->delete( $transient_key, $transient_group );
				WK_Caching::log( "Deleting php fast cache: $option_key, deleted: $deleted" );
			}

			if ( $file_writing_enabled ) {
				$file_api = new WK_Caching_File( $transient_group . '-transient' );

				if ( $file_api->exists( $option_key ) ) {
					$file_api->delete_file( $option_key );
					$deleted = true;
					WK_Caching::log( "Deleting file: $option_key, Deleted: $deleted" );
				}
			}

			// Removing db transient.
			if ( $transient_caching_enabled ) {
				WK_Caching::log( "Deleting transient: $option_key" );
				delete_option( $option_key );
				$deleted = true;
			}

			return $deleted;
		}

		/**
		 * Delete all the transients.
		 *
		 * @param string $transient_group Transient Group.
		 */
		public function delete_all_transients( $transient_group = '' ) {
			global $wpdb;

			// Removing files if file api exist.
			$phpfast_caching_enabled   = $this->is_phpfast_caching_enabled();
			$file_writing_enabled      = $this->is_file_writing_enabled();
			$transient_caching_enabled = $this->is_transient_caching_enabled();
			$deleted                   = false;

			WK_Caching::log( "Deleting all transients and files: File writing: $file_writing_enabled, Cache in transient: $transient_caching_enabled, PHP Fast enabled: $phpfast_caching_enabled" );

			if ( $phpfast_caching_enabled ) {
				$phpfast = WK_Caching_PHPFastCache::get_instance();
				$deleted = $phpfast->delete( '', $transient_group, true );
				WK_Caching::log( "Deleting all php fast cache: $transient_group, Deleted: $deleted" );
			}

			if ( $file_writing_enabled ) {
				$file_api = new WK_Caching_File( $transient_group . '-transient' );
				$deleted  = $file_api->delete_all( $transient_group . '-transient', true );
				WK_Caching::log( "Deleting all file: $transient_group, Deleted: $deleted" );
			}

			if ( $transient_caching_enabled ) {
				// Removing db transient.
				$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s", esc_sql( '_wk_transient_' . $transient_group ) ) );

				WK_Caching::log( "Deleting all transient: $transient_group, Deleted: $deleted" );
			}

			return $deleted;
		}

		/**
		 * Delete all wk plugins transients.
		 */
		public function delete_force_transients() {
			global $wpdb;

			// Removing files if file api exist.
			$phpfast_caching_enabled   = $this->is_phpfast_caching_enabled();
			$file_writing_enabled      = $this->is_file_writing_enabled();
			$transient_caching_enabled = $this->is_transient_caching_enabled();
			$deleted                   = false;

			WK_Caching::log( "Deleting force transients and files: File writing: $file_writing_enabled, Cache in transient: $transient_caching_enabled, PHP Fast enabled: $phpfast_caching_enabled" );

			if ( $phpfast_caching_enabled ) {
				$phpfast = WK_Caching_PHPFastCache::get_instance();
				$deleted = $phpfast->delete( '', '', true );
				WK_Caching::log( "Deleting force php fast cache: Deleted: $deleted" );
			}

			if ( $file_writing_enabled ) {
				$file_api = new WK_Caching_File( 'wk-transient' );

				$upload      = wp_upload_dir();
				$folder_path = $upload['basedir'] . '/wkwc';
				$deleted     = $file_api->delete_folder( $folder_path, true );

				WK_Caching::log( "Deleting force files: $folder_path, Deleted: $deleted" );
			}

			if ( $transient_caching_enabled ) {
				// Removing db transient.
				$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE %s", '%_wk_transient_%' ) );

				WK_Caching::log( "Deleted force transient: $deleted" );
			}
			return $deleted;
		}

		/**
		 * Allow or stop file writing.
		 *
		 * @return bool
		 */
		protected function is_file_writing_enabled() {
			$enabled = true;

			if ( ! class_exists( 'WK_Caching_File' ) ) {
				$enabled = false;
			}
			return apply_filters( '_wkwc_is_file_writing_enabled', $enabled );
		}

		/**
		 * Allow to stop file writing.
		 *
		 * @return bool
		 */
		protected function is_transient_caching_enabled() {
			return apply_filters( '_wkwc_is_transient_caching_enabled', true );
		}

		/**
		 * Allow to stop file writing.
		 *
		 * @return bool
		 */
		protected function is_phpfast_caching_enabled() {
			$redis_enabled = apply_filters( 'wkwc_caching_redis_enabled', 'no' );
			$enabled       = false;

			if ( 'yes' === $redis_enabled ) {
				$redis_enabled = get_option( 'wkwc_caching_redis_enabled', 'no' );
			}

			if ( 'yes' === $redis_enabled ) {
				$configuration = WK_Caching_Core_Loader::get_the_latest();

				if ( ! empty( $configuration['plugin_path'] ) && file_exists( $configuration['plugin_path'] . 'wk_caching/vendor/autoload.php' ) ) {
					$enabled = true;
				}
				$enabled = apply_filters( '_wkwc_phpfast_caching_enabled', $enabled );
			}

			return $enabled;
		}
	}
}
