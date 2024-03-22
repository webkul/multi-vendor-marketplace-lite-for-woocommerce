<?php
/**
 * Seller Data Helper
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Admin;

use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Seller_Data' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Seller_Data {
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
		 * Constructor
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
		 * Insert new seller in database.
		 *
		 * @param array $seller_data Seller data.
		 *
		 * @return void
		 */
		public function wkmp_insert_seller( $seller_data ) {
			if ( ! empty( $data ) ) {
				$this->wpdb->insert(
					$this->wpdb->prefix . 'mpsellerinfo',
					$seller_data
				);
			}
		}

		/**
		 * Update new seller in database.
		 *
		 * @param int    $seller_id seller id.
		 * @param string $role role.
		 *
		 * @return void
		 */
		public function wkmp_update_seller_role( $seller_id, $role ) {
			if ( $seller_id && $role ) {
				$this->wpdb->update(
					$this->wpdb->prefix . 'mpsellerinfo',
					array(
						'seller_value' => sanitize_text_field( wp_unslash( $role ) ),
					),
					array( 'user_id' => $seller_id ),
					array( '%s' ),
					array( '%d' )
				);
			}
		}

		/**
		 * Get all sellers.
		 *
		 * @param array $data data.
		 *
		 * @return array
		 */
		public function wkmp_get_sellers( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$fields   = empty( $data['fields'] ) ? '* ' : $data['fields'];

			$sql = $wpdb_obj->prepare( "SELECT %1s FROM {$wpdb_obj->prefix}mpsellerinfo mp LEFT JOIN {$wpdb_obj->base_prefix}users u ON (mp.user_id = u.ID) WHERE 1=1", esc_sql( $fields ) );

			if ( ! empty( $data['role'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND mp.seller_value = %s', esc_sql( $data['role'] ) );
			}

			if ( ! empty( $data['seller_id'] ) ) {
				$sql .= $wpdb_obj->prepare( ' AND mp.user_id=%d', esc_attr( $data['seller_id'] ) );
			}

			$search = empty( $data['search'] ) ? '' : $data['search'];

			if ( ! empty( $search ) ) {
				$sql .= $wpdb_obj->prepare( ' AND u.user_email LIKE %s OR u.user_nicename LIKE %s OR u.display_name LIKE %s OR u.user_login LIKE %s', esc_sql( $search ) . '%', esc_sql( $search ) . '%', esc_sql( $search ) . '%', esc_sql( $search ) . '%' );
			}

			if ( empty( $data['single_col'] ) ) {
				$orderby = empty( $data['orderby'] ) ? 'user_nicename' : $data['orderby']; // If no sort, default to date.
				$order   = empty( $data['order'] ) ? 'desc' : $data['order']; // If no order, default to asc.

				$sql .= $wpdb_obj->prepare( ' ORDER BY %1s %2s', esc_sql( $orderby ), esc_sql( $order ) );
			}

			if ( ! empty( $data['limit'] ) ) {
				$offset = empty( $data['start'] ) ? 0 : intval( $data['start'] );
				$sql   .= $wpdb_obj->prepare( ' LIMIT %d, %d', esc_sql( $offset ), esc_sql( $data['limit'] ) );
			}

			$sellers = empty( $data['single_col'] ) ? $wpdb_obj->get_results( $sql ) : $wpdb_obj->get_col( $sql );

			return apply_filters( 'wkmp_get_sellers', $sellers );
		}

		/**
		 * Get All Sellers Count.
		 *
		 * @param array $data data.
		 *
		 * @return int
		 */
		public function wkmp_get_total_sellers( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT COUNT(*) FROM {$this->wpdb->prefix}mpsellerinfo mp LEFT JOIN {$this->wpdb->base_prefix}users u ON (mp.seller_id = u.ID)";

			$search = empty( $data['search'] ) ? '' : $data['search'];

			if ( ! empty( $search ) ) {
				$sql .= $wpdb_obj->prepare( ' WHERE u.user_email LIKE %s OR u.user_nicename LIKE %s OR u.display_name LIKE %s OR u.user_login LIKE %s', esc_sql( $search ) . '%', esc_sql( $search ) . '%', esc_sql( $search ) . '%', esc_sql( $search ) . '%' );
			}

			if ( ! empty( $data['verified'] ) ) {
				$sql .= $wpdb_obj->prepare( ' WHERE mp.seller_value=%s', esc_sql( 'seller' ) );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_total_sellers', $total );
		}

		/**
		 * Delete seller by seller id
		 *
		 * @param int  $seller_id seller id.
		 * @param bool $delete_user Delete WP User also.
		 *
		 * @return void
		 */
		public function wkmp_delete_seller( $seller_id, $delete_user = true ) {
			global $wkmarketplace;

			$commission_helper = Common\WKMP_Commission::get_instance();

			$wpdb_obj = $this->wpdb;

			if ( $delete_user && get_userdata( $seller_id ) instanceof \WP_User ) {
				$wpdb_obj->delete( "{$wpdb_obj->base_prefix}users", array( 'ID' => esc_attr( $seller_id ) ), array( '%d' ) );
				$wpdb_obj->delete( "{$wpdb_obj->base_prefix}usermeta", array( 'user_id' => esc_attr( $seller_id ) ), array( '%d' ) );
			}

			$wpdb_obj->delete( "{$wpdb_obj->prefix}mpsellerinfo", array( 'user_id' => esc_attr( $seller_id ) ), array( '%d' ) );

			$commission_helper->wkmp_delete_commission( $seller_id );

			$post_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT ID FROM {$this->wpdb->prefix}posts WHERE post_author = %d", esc_attr( $seller_id ) ) );

			if ( $post_ids ) {
				if ( get_option( '_wkmp_seller_delete' ) ) {
					foreach ( $post_ids as $post_id ) {
						$wpdb_obj->delete( "{$wpdb_obj->prefix}posts", array( 'ID' => esc_attr( $post_id->ID ) ), array( '%d' ) );
						$wpdb_obj->delete( "{$wpdb_obj->prefix}postmeta", array( 'post_id' => esc_attr( $post_id->ID ) ), array( '%d' ) );
					}
				} else {
					$first_admin_id = $wkmarketplace->wkmp_get_first_admin_user_id();
					foreach ( $post_ids as $post_id ) {
						$wpdb_obj->update( $wpdb_obj->prefix . 'posts', array( 'post_author' => $first_admin_id ), array( 'ID' => $post_id->ID ), array( '%d' ), array( '%d' ) );
					}
				}
			}

			$query_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT id FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE seller_id =%d", esc_attr( $seller_id ) ) );

			if ( $query_ids ) {
				foreach ( $query_ids as $id ) {
					$wpdb_obj->delete( "{$wpdb_obj->prefix}mpseller_asktoadmin", array( 'id' => esc_attr( $id->id ) ), array( '%d' ) );
					$wpdb_obj->delete( "{$wpdb_obj->prefix}mpseller_asktoadmin_meta", array( 'id' => esc_attr( $id->id ) ), array( '%d' ) );
				}
			}
		}

		/**
		 * Get product count by seller id
		 *
		 * @param int $seller_id Seller ID.
		 *
		 * @return $count
		 */
		public function wkmp_get_seller_product_count( $seller_id ) {
			$count = 0;

			if ( $seller_id > 0 ) {
				$product_ids = wc_get_products(
					array(
						'limit'  => -1,
						'return' => 'ids',
						'author' => $seller_id,
					)
				);

				$count = empty( $product_ids ) ? $count : count( $product_ids );
			}

			return apply_filters( 'wkmp_seller_product_count', $count, $seller_id );
		}

		/**
		 * Get total number allowed sellers in lite.
		 *
		 * @return int
		 */
		public function wkmp_get_lite_allowed_sellers() {
			$reflect = new \ReflectionClass( $this );
			$allowed = strtoupper( 'WKMP_ALLOWED_' . $reflect->getShortName() . '_COUNT' );
			$consts  = empty( get_defined_constants( true ) ['user'] ) ? array() : get_defined_constants( true ) ['user'];

			return defined( $allowed ) ? $consts[ $allowed ] : 0;
		}

		/**
		 * Get total number allowed sellers in lite.
		 *
		 * @param int    $seller_id Seller ID.
		 * @param object $seller_user_object Seller user object.
		 * @param bool   $lite_disabled Disabled due to lite.
		 *
		 * @return array.
		 */
		public function wkmp_disapprove_seller( $seller_id, $seller_user_object = '', $lite_disabled = false ) {
			$results = array(
				'success'   => false,
				'seller_id' => $seller_id,
			);

			if ( ! $seller_user_object instanceof \WP_User && $seller_id > 0 ) {
				$seller_user_object = get_user_by( 'ID', $seller_id );
			}

			if ( $seller_user_object instanceof \WP_User ) {
				$wpdb_obj = $this->wpdb;
				$seller_user_object->set_role( get_option( 'default_role', 'subscriber' ) );
				$wpdb_obj->get_results( $wpdb_obj->prepare( "UPDATE {$wpdb_obj->prefix}posts SET post_status = 'draft' WHERE post_author = %d", $seller_id ) );
				do_action( 'wkmp_seller_account_disapproved', $seller_id );

				$this->wkmp_update_seller_role( $seller_id, 'customer' );

				if ( $lite_disabled ) {
					update_user_meta( $seller_id, 'wkmp_lite_disabled', true ); // Disabled on deactivation of pro, and this meta will be used on activation on pro to approve these sellers.
				}

				$results['success'] = $seller_id;

				return $results;
			}
		}

		/**
		 * Disable sellers that are beyond lite allowed seller count.
		 *
		 * @return array
		 */
		public function wkmp_disable_extra_sellers() {
			$results      = array( 'success' => false );
			$lite_allowed = absint( $this->wkmp_get_lite_allowed_sellers() );

			if ( intval( $this->wkmp_get_total_sellers( array( 'verified' => true ) ) ) > $lite_allowed ) {
				$seller_ids = $this->wkmp_get_sellers(
					array(
						'fields'     => 'mp.user_id',
						'single_col' => true,
					)
				);

				$seller_ids = array_slice( $seller_ids, $lite_allowed );

				foreach ( $seller_ids as $seller_id ) {
					$seller_user_object    = get_user_by( 'ID', $seller_id );
					$results[ $seller_id ] = $this->wkmp_disapprove_seller( $seller_id, $seller_user_object, true );
				}
				$results['success'] = true;

				return $results;
			}
		}
	}
}
