<?php
/**
 * File caching.
 *
 * @package WK_Caching
 */
defined( 'ABSPATH' ) || exit(); // Exit if access directly.

/**
 * Including WordPress Filesystem API
 */
require_once ABSPATH . '/wp-admin/includes/file.php';

if ( function_exists( 'WP_Filesystem' ) ) {
	WP_Filesystem();
}

if ( class_exists( 'WP_Filesystem_Direct' ) && ! class_exists( 'WK_Caching_File' ) ) {
	/**
	 * WK_Caching_File class.
	 */
	class WK_Caching_File extends WP_Filesystem_Direct {
		/**
		 * Upload directory.
		 *
		 * @var mixed
		 */
		private $upload_dir;

		/**
		 * Webkul core directory.
		 *
		 * @var string
		 */
		private $wk_core_dir = '';

		/**
		 * Directory name for files in Uploads folder.
		 *
		 * @var string
		 */
		private $wk_dir = 'wkwc';

		/**
		 * Module wise directory name.
		 *
		 * @var string
		 */
		private $module_dir_name = '';

		/**
		 * __construct
		 *
		 * @param  string $module Module wise directory name.
		 *
		 * @return void
		 */
		public function __construct( $module ) {
			$upload            = wp_upload_dir();
			$this->upload_dir  = $upload['basedir'];
			$this->wk_core_dir = $this->upload_dir . '/' . $this->wk_dir;
			$this->set_module_dir( $module );

			$this->makedirs();
		}

		/**
		 * Set Module name.
		 *
		 * @param string $module Module name.
		 *
		 * @return void
		 */
		public function set_module_dir( $module ) {
			if ( '' !== $module ) {
				$this->module_dir_name = $module;
			}
		}

		/**
		 * Get module directory.
		 *
		 * @return string
		 */
		public function get_module_dir() {
			return $this->wk_core_dir . '/' . $this->module_dir_name;
		}

		/**
		 * Create file.
		 *
		 * @param string $file File directory.
		 * @param int    $time Time stamp.
		 * @param string $atime At time.
		 *
		 * @return string
		 */
		public function touch( $file, $time = 0, $atime = 0 ) {
			$file = $this->file_path( $file );

			return parent::touch( $file, $time, $atime );
		}

		/**
		 * Get file path.
		 *
		 * @param string $file File name.
		 *
		 * @return string
		 */
		public function file_path( $file ) {
			$file_path = $this->wk_core_dir . '/' . $this->module_dir_name . '/' . $file;

			return $file_path;
		}

		/**
		 * Folder path.
		 *
		 * @param string $folder_name Folder name.
		 *
		 * @return string
		 */
		public function folder_path( $folder_name ) {
			$folder_path = $this->wk_core_dir . '/' . $folder_name . '/';

			return $folder_path;
		}

		/**
		 * Is readable.
		 *
		 * @param string $file File name.
		 *
		 * @return bool
		 */
		public function is_readable( $file ) {
			$file = $this->file_path( $file );

			return parent::is_readable( $file );
		}

		/**
		 * Is writable.
		 *
		 * @param string $file File name.
		 *
		 * @return bool
		 */
		public function is_writable( $file ) {
			$file = $this->file_path( $file );

			return parent::is_writable( $file );
		}

		/**
		 * Put contents.
		 *
		 * @param string $file File name.
		 * @param string $contents File content.
		 * @param bool   $mode File mode.
		 *
		 * @return bool
		 */
		public function put_contents( $file, $contents, $mode = false ) {
			$file = $this->file_path( $file );

			return parent::put_contents( $file, $contents, $mode );
		}

		/**
		 * Delete file.
		 *
		 * @param string $file File name.
		 * @param bool   $recursive Recursive.
		 * @param string $type File type.
		 *
		 * @return bool
		 */
		public function delete_file( $file, $recursive = false, $type = 'f' ) {
			$file = $this->file_path( $file );

			return parent::delete( $file, $recursive, $type );
		}

		/**
		 * Delete all.
		 *
		 * @param string $folder_name Folder name.
		 * @param bool   $recursive Is recursive.
		 *
		 * @return bool
		 */
		public function delete_all( $folder_name, $recursive = false ) {
			$folder_path = $this->folder_path( $folder_name );

			return parent::rmdir( $folder_path, $recursive );
		}

		/**
		 * Gets details for files in a directory or a specific file.
		 *
		 * @since 2.5.0
		 *
		 * @param string $path           Path to directory or file.
		 * @param bool   $include_hidden Optional. Whether to include details of hidden ("." prefixed) files.
		 *                               Default true.
		 * @param bool   $recursive      Optional. Whether to recursively include file details in nested directories.
		 *                               Default false.
		 * @return array|false {
		 *     Array of files. False if unable to list directory contents.
		 *
		 *     @type string $name        Name of the file or directory.
		 *     @type string $perms       *nix representation of permissions.
		 *     @type int    $permsn      Octal representation of permissions.
		 *     @type string $owner       Owner name or ID.
		 *     @type int    $size        Size of file in bytes.
		 *     @type int    $lastmodunix Last modified unix timestamp.
		 *     @type mixed  $lastmod     Last modified month (3 letter) and day (without leading 0).
		 *     @type int    $time        Last modified time.
		 *     @type string $type        Type of resource. 'f' for file, 'd' for directory.
		 *     @type mixed  $files       If a directory and $recursive is true, contains another array of files.
		 * }
		 */
		public function dirlist( $path, $include_hidden = true, $recursive = false ) {
			if ( $this->is_file( $path ) ) {
				$limit_file = basename( $path );
				$path       = dirname( $path );
			} else {
				$limit_file = false;
			}

			if ( ! $this->is_dir( $path ) || ! $this->is_readable( $path ) ) {
				return false;
			}

			$dir = dir( $path );

			if ( ! $dir ) {
				return false;
			}

			$path = trailingslashit( $path );
			$ret  = array();

			while ( false !== ( $entry = $dir->read() ) ) {
				$struc         = array();
				$struc['name'] = $entry;

				if ( '.' === $struc['name'] || '..' === $struc['name'] ) {
					continue;
				}

				if ( ! $include_hidden && '.' === $struc['name'][0] ) {
					continue;
				}

				if ( $limit_file && $struc['name'] !== $limit_file ) {
					continue;
				}

				$struc['perms']       = $this->gethchmod( $path . $entry );
				$struc['permsn']      = $this->getnumchmodfromh( $struc['perms'] );
				$struc['number']      = false;
				$struc['owner']       = $this->owner( $path . $entry );
				$struc['group']       = $this->group( $path . $entry );
				$struc['size']        = $this->size( $path . $entry );
				$struc['lastmodunix'] = $this->mtime( $path . $entry );
				$struc['lastmod']     = gmdate( 'M j', $struc['lastmodunix'] );
				$struc['time']        = gmdate( 'h:i:s', $struc['lastmodunix'] );
				$struc['type']        = $this->is_dir( $path . $entry ) ? 'd' : 'f';

				if ( 'd' === $struc['type'] ) {
					if ( $recursive ) {
						$struc['files'] = $this->dirlist( $path . $struc['name'], $include_hidden, $recursive );
					} else {
						$struc['files'] = array();
					}
				}

				$ret[ $struc['name'] ] = $struc;
			}

			$dir->close();
			unset( $dir );

			return $ret;
		}

		/**
		 * Delete folder.
		 *
		 * @param string $folder_path Folder path.
		 * @param bool   $recursive Delete recursive.
		 *
		 * @return bool
		 */
		public function delete_folder( $folder_path, $recursive = false ) {
			return parent::rmdir( $folder_path, $recursive );
		}

		/**
		 * If file exists.
		 *
		 * @param string $file_name File name.
		 *
		 * @return bool
		 */
		public function exists( $file_name ) {
			$file_path = $this->file_path( $file_name );

			return parent::exists( $file_path );
		}

		/**
		 * Get contents.
		 *
		 * @param string $file File name.
		 *
		 * @return bool
		 */
		public function get_contents( $file ) {
			$file = $this->file_path( $file );

			return parent::get_contents( $file );
		}

		/**
		 * Make dirs.
		 *
		 * @return void
		 */
		public function makedirs() {
			$module = $this->module_dir_name;

			if ( parent::is_writable( $this->upload_dir ) ) {
				if ( false === $this->is_dir( $this->wk_core_dir ) ) {
					$this->mkdir( $this->wk_core_dir );
					$file_handle = fopen( trailingslashit( $this->wk_core_dir ) . '/.htaccess', 'w' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
					if ( $file_handle ) {
						fwrite( $file_handle, 'deny from all' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
						fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
					}
				}
				$dir = $this->wk_core_dir . '/' . $module;
				if ( false === $this->is_dir( $dir ) ) {
					$this->mkdir( $dir );
				}
			}
		}

		/**
		 * Get all cached data or keys.
		 *
		 * @param string     $type Return type. Data or keys.
		 * @param array      $keys Data keys, If $type is 'data' then all keys for which data is to be retrieved.
		 * @param string|int $data_group The data group.
		 *
		 * @return bool|array
		 */
		public function get_all( $type = '', $keys = array(), $data_group = '' ) {
			$path = $this->get_module_dir();
			return $this->get_file_list( $path, array() );
		}

		/**
		 * Get all folder and file name list.
		 *
		 * @param string $dir_path Directory path.
		 * @param array  $result File list.
		 *
		 * @return array()
		 */
		public function get_file_list( $dir_path, $result = array() ) {
			if ( $this->is_dir( $dir_path ) ) {
				$all_dirs = scandir( $dir_path );
				foreach ( $all_dirs as $dir ) {
					if ( in_array( $dir, array( '.', '..', '.htaccess' ), true ) ) {
						continue;
					}

					if ( $this->is_dir( $this->get_module_dir() . $dir ) ) {
						$result = $this->get_file_list( $this->get_module_dir() . $dir, $result );
					}
					$result[] = $dir;
				}
			}
			return $result;
		}
	}
}
