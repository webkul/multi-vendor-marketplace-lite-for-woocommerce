<?php
/**
 * Manage shipping Handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Shipping;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Manage_Shipping' ) ) {
	/**
	 * Class WKMP_Manage_Shipping
	 *
	 * @package WkMarketplace\Includes\Shipping
	 */
	class WKMP_Manage_Shipping {
		/**
		 * MP shipping list.
		 *
		 * @var array|mixed|void
		 */
		public static $mp_shipping_list = array();

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Manage_Shipping constructor.
		 */
		public function __construct() {
			self::$mp_shipping_list = apply_filters(
				'wkmp_shipping_methods',
				array(
					'mp_flat_rate',
					'mp_hyperlocal_shipping',
					'mp_usps_shipping',
					'mp_local_pickup',
					'mp_free_shipping',
				)
			);

			if ( ! empty( self::$mp_shipping_list ) && is_array( self::$mp_shipping_list ) && ! is_admin() ) {
				add_action( 'template_redirect', array( $this, 'reset_previous_chosen_shipping_method' ), 1 );
				add_filter( 'woocommerce_shipping_zone_shipping_methods', array( $this, 'wkmp_restrict_shipping' ) );
				add_filter( 'wk_mp_show_seller_available_shipping', array( $this, 'wkmp_restrict_shipping_show' ), 10, 2 );
				add_filter( 'woocommerce_shipping_packages', array( $this, 'wkmp_restrict_cart_shipping' ), 10, 1 );
				add_filter( 'woocommerce_get_zone_criteria', array( $this, 'wkmp_get_zone_ids_for_marketplace_seller' ), 10, 3 );
			}
			add_filter( 'wkmp_validate_product_author', array( $this, 'wkmp_validate_product_author_for_admin' ) );
			add_filter( 'wkmp_shipping_seller_id', array( $this, 'wkmp_manage_seller_id' ) );
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
		 * Take Matching zone_id for Marketplace Seller
		 *
		 * @param array $criteria Criteria.
		 * @param array $package Shipping package.
		 * @param array $postcode_locations Locations.
		 *
		 * @hooked 'woocommerce_get_zone_criteria' filter hook.
		 *
		 * @return array
		 */
		public function wkmp_get_zone_ids_for_marketplace_seller( $criteria, $package, $postcode_locations ) {
			$seller_ids = array();

			foreach ( $package['contents'] as $values ) {
				$product_id = $values['product_id'];
				$seller_id  = get_post_field( 'post_author', $product_id );

				if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
					$seller_id = $values[ "assigned-seller-$product_id" ];
				}

				if ( ! in_array( $seller_id, $seller_ids, true ) ) {
					$seller_ids[] = $seller_id;
				}
			}

			if ( empty( $seller_ids ) ) {
				return $criteria;
			}

			$seller_ids = apply_filters( 'wkmp_shipping_seller_id', $seller_ids );
			$zone_id    = (int) $this->wkmp_get_zone_id_from_package( $package, $seller_ids );

			if ( ! empty( $zone_id ) ) {
				$criteria[] = 'AND zones.zone_id IN (' . $zone_id . ')';
			}

			return $criteria;
		}

		/**
		 * This Function Clears the cache value for cart shipping when Shipping Option changes from admin end.
		 */
		public function reset_previous_chosen_shipping_method() {
			$check = get_option( 'wkmp_shipping_option', 'woocommerce' );

			if ( ( is_checkout() || is_cart() ) && ! empty( WC()->session ) && ! wp_doing_ajax() ) {
				$wkmp_shipping = WC()->session->get( 'wkmp_shipping' );
				if ( empty( $wkmp_shipping ) ) {
					WC()->session->set( 'wkmp_shipping', $check );
					$check = true;
				} elseif ( $check !== $wkmp_shipping ) {
					WC()->session->set( 'wkmp_shipping', $check );
					$check = true;
				} else {
					$check = false;
				}
				if ( $check ) {
					if ( get_current_user_id() && get_user_meta( get_current_user_id(), 'shipping_method', true ) ) {
						delete_user_meta( get_current_user_id(), 'shipping_method' );
					}
					$wc_session_key = 'shipping_for_package_0';
					WC()->session->__unset( $wc_session_key );
				}
			}
		}

		/**
		 * The Function returns the all administrator's Ids when the product author is not a seller.
		 *
		 * @param array $product_author Product author id.
		 *
		 * @return array
		 */
		public function wkmp_validate_product_author_for_admin( $product_author ) {
			global $wkmarketplace;
			$product_author = ( is_array( $product_author ) && count( $product_author ) > 0 ) ? $product_author[0] : $product_author;
			$all_admin_ids  = array( $product_author );

			if ( intval( $product_author ) > 0 && ! $wkmarketplace->wkmp_user_is_seller( $product_author ) ) {
				$args          = array(
					'role'   => 'administrator',
					'fields' => 'ID',
				);
				$wp_user_query = new \WP_User_Query( $args );
				$all_admin_ids = (array) $wp_user_query->get_results();
			}

			return $all_admin_ids;
		}

		/**
		 * The Function returns the administrator's Ids when Shipping option is set to Admin
		 *
		 * @param array $seller_ids Seller ids.
		 *
		 * @return array
		 */
		public function wkmp_manage_seller_id( $seller_ids ) {
			$seller_ids     = is_array( $seller_ids ) ? $seller_ids : array( $seller_ids );
			$shipping_check = get_option( 'wkmp_shipping_option', 'woocommerce' );

			if ( 'marketplace' !== $shipping_check ) {
				$args          = array(
					'role'   => 'administrator',
					'fields' => 'ID',
				);
				$wp_user_query = new \WP_User_Query( $args );
				$seller_ids    = (array) $wp_user_query->get_results();
			}

			return $seller_ids;
		}

		/**
		 * Get items in package.
		 *
		 * @param array  $package Package information.
		 * @param array  $seller_ids Seller ids.
		 * @param string $check Shipping setting check.
		 *
		 * @return int
		 */
		public function wkmp_get_package_item_qty( $package, $seller_ids, $check ) {
			$total_quantity = 0;
			$seller_id      = ( 'marketplace' === $check && ! empty( $seller_ids ) ) ? $seller_ids[0] : 0;

			foreach ( $package['contents'] as $values ) {
				$product_id     = $values['product_id'];
				$item_seller_id = get_post_field( 'post_author', $product_id );

				if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
					$item_seller_id = $values[ "assigned-seller-$product_id" ];
				}

				if ( ( $seller_id > 0 && intval( $seller_id ) === intval( $item_seller_id ) ) || in_array( $item_seller_id, $seller_ids, true ) ) {
					$total_quantity += $values['quantity'];
				}
			}

			return $total_quantity;
		}

		/**
		 * Get items in package.
		 *
		 * @param array  $package Package information.
		 * @param array  $seller_ids Seller ids.
		 * @param string $check Shipping setting check.
		 *
		 * @return float
		 */
		public function wkmp_get_cart_content_total( $package, $seller_ids, $check ) {
			$total_cost = 0;
			$seller_id  = ( 'marketplace' === $check && ! empty( $seller_ids ) ) ? $seller_ids[0] : 0;

			foreach ( $package['contents'] as $values ) {
				$product_id     = $values['product_id'];
				$item_seller_id = get_post_field( 'post_author', $product_id );

				if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
					$item_seller_id = $values[ "assigned-seller-$product_id" ];
				}

				if ( ( $seller_id > 0 && intval( $seller_id ) === intval( $item_seller_id ) ) || in_array( $item_seller_id, $seller_ids, true ) ) {
					if ( $values['line_total'] > 0 && $values['data']->needs_shipping() ) {
						$total_cost += $values['line_total'];
					}
				}
			}

			return $total_cost;
		}

		/**
		 * This Function restricts other shipping methods to show, displays only MP shipping method.
		 *
		 * @param array $methods Shipping methods.
		 *
		 * @hooked 'woocommerce_shipping_zone_shipping_methods' filter hook.
		 *
		 * @return mixed
		 */
		public function wkmp_restrict_shipping( $methods = array() ) {
			$shipping_check = get_option( 'wkmp_shipping_option', 'woocommerce' );
			if ( 'marketplace' === $shipping_check ) {
				foreach ( $methods as $key => $value ) {
					if ( ! in_array( $value->id, self::$mp_shipping_list, true ) ) {
						unset( $methods[ $key ] );
					}
				}
			}

			return $methods;
		}

		/**
		 * This Function restricts other shipping methods to show, displays only MP shipping method for seller
		 *
		 * @param string $status Status.
		 * @param object $method Method object.
		 *
		 * @hooked 'wk_mp_show_seller_available_shipping' filter hook.
		 *
		 * @return false|mixed
		 */
		public function wkmp_restrict_shipping_show( $status, $method ) {

			if ( 'marketplace' === get_option( 'wkmp_shipping_option', 'woocommerce' ) ) {
				if ( ! in_array( $method->id, self::$mp_shipping_list, true ) ) {
					return false;
				}
			}

			return $status;
		}

		/**
		 * Restricting cart shipping.
		 *
		 * @param array $packages Shipping packages.
		 *
		 * @return array
		 */
		public function wkmp_restrict_cart_shipping( $packages ) {
			$shipping_check = get_option( 'wkmp_shipping_option', 'woocommerce' );
			if ( 'marketplace' === $shipping_check ) {
				if ( isset( $packages[0] ) && isset( $packages[0]['rates'] ) ) {
					$rates_new = array();
					foreach ( $packages[0]['rates'] as $key => $value ) {
						if ( in_array( $value->get_method_id(), self::$mp_shipping_list, true ) ) {
							$rates_new[ $key ] = $value;
						}
					}
					$packages[0]['rates'] = $rates_new;
				}
			}

			return $packages;
		}

		/**
		 * Restrict admin to add shipping.
		 *
		 * @param object $zone Zone.
		 */
		public function wkmp_restrict_admin_to_add_shipping( $zone ) {
			add_filter(
				'woocommerce_shipping_method_supports',
				function ( $supports, $feature, $shipping_class ) {
					if ( 'shipping-zones' === $feature && ! in_array( $shipping_class->id, self::$mp_shipping_list, true ) ) {
						return false;
					}

					return $supports;
				},
				10,
				3
			);
		}

		/**
		 * Function for getting zone id.
		 *
		 * @param array $package Package array.
		 * @param array $seller_ids Seller ids.
		 *
		 * @return string
		 */
		public function wkmp_get_zone_id_from_package( $package, $seller_ids = array() ) {
			global $wpdb;
			$seller_ids = is_array( $seller_ids ) ? $seller_ids : array( $seller_ids );
			$country    = strtoupper( wc_clean( $package['destination']['country'] ) );
			$state      = strtoupper( wc_clean( $package['destination']['state'] ) );
			$continent  = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $country ) ) );
			$postcode   = wc_normalize_postcode( wc_clean( $package['destination']['postcode'] ) );

			// Work out criteria for our zone search.
			$criteria   = array();
			$criteria[] = $wpdb->prepare( "( ( location_type = 'country' AND location_code = %s )", $country );
			$criteria[] = $wpdb->prepare( "OR ( location_type = 'state' AND location_code = %s )", $country . ':' . $state );
			$criteria[] = $wpdb->prepare( "OR ( location_type = 'continent' AND location_code = %s )", $continent );
			$criteria[] = 'OR ( location_type IS NULL ) )';

			// Postcode range and wild card matching.
			$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';" );

			if ( $postcode_locations ) {
				$zone_postcode_rules = array_map( 'absint', wp_list_pluck( $postcode_locations, 'zone_id' ) );
				$matches             = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country );
				$do_not_match        = array_unique( array_diff( $zone_postcode_rules, array_keys( $matches ) ) );

				if ( ! empty( $do_not_match ) ) {
					$do_not_match_str = implode( ' ', $do_not_match );
					$criteria[]       = $wpdb->prepare( 'AND zones.zone_id NOT IN (%1s)', $do_not_match_str );
				}
			}

			$seller_ids_str = implode( ',', $seller_ids );
			$criteria_str   = implode( ' ', $criteria );

			// Get matching zones.
			$query = $wpdb->prepare(
				"SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones LEFT OUTER JOIN
			{$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode'
			JOIN {$wpdb->prefix}mpseller_meta as my_zones on zones.zone_id = my_zones.zone_id and my_zones.seller_id IN ( %1s )
			WHERE %2s  ORDER BY zone_order ASC LIMIT 1",
				stripslashes( $seller_ids_str ),
				stripslashes( $criteria_str )
			);

			return $wpdb->get_var( stripslashes_deep( $query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- No applicable variables for this query.
		}
	}
}
