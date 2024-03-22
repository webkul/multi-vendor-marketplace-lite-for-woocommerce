<?php
/**
 * Shipping queries class
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Front;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Shipping_Queries' ) ) {
	/**
	 * Shipping queries class
	 *
	 * Class WKMP_Shipping_Queries
	 *
	 * @package WkMarketplace\Helper\Front
	 */
	class WKMP_Shipping_Queries {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Zone Data
		 *
		 * @var array
		 */
		protected $mp_data = array(
			'zone_id'        => 0,
			'zone_name'      => '',
			'zone_order'     => 0,
			'zone_locations' => array(),
		);

		/**
		 * Shipping details.
		 *
		 * @var array
		 */
		public $shipping_details = array();

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Shipping_Queries constructor.
		 *
		 * @param int $zone_data Zone array.
		 */
		public function __construct( $zone_data = 0 ) {
			global $wpdb;
			$this->wpdb = $wpdb;

			if ( ! empty( $zone_data ) && array_key_exists( 'zone-id', $zone_data ) ) {
				$this->wkmp_read_zone( $zone_data );
			} elseif ( is_object( $zone_data ) ) {
				$this->wkmp_set_zone_id( $zone_data->zone_id );
				$this->wkmp_set_zone_name( $zone_data->zone_name );
			} else {
				$this->wkmp_set_zone_name( esc_html__( 'Zone', 'wk-marketplace' ) );
			}
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
		 * Read WooCommerce Shipping zone
		 *
		 * @param int $id Zone id.
		 *
		 * @return void
		 */
		public function wkmp_read_zone( $id ) {
			$wpdb_obj  = $this->wpdb;
			$zone_data = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}woocommerce_shipping_zones WHERE zone_id = %d LIMIT 1", $id ) );
			if ( $zone_data ) {
				$this->wkmp_set_zone_id( $zone_data->zone_id );
				$this->wkmp_set_zone_name( $zone_data->zone_name );
			}
		}

		/**
		 * Save Marketplace shipping
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		public function wkmp_save_shipping_details( $data = array() ) {
			if ( ! empty( $data['save_shipping_details'] ) ) {
				$this->wkmp_create_zone( $data );
			} elseif ( ! empty( $data['update_shipping_details'] ) ) {
				$this->wkmp_update_zone( $data );
			}

			\WC_Cache_Helper::invalidate_cache_group( 'shipping_zones' );

			// Increments the transient version to invalidate cache.
			\WC_Cache_Helper::get_transient_version( 'shipping', true );
		}

		/**
		 * Set Shipping zone id
		 *
		 * @param int $set Set value.
		 *
		 * @return void
		 */
		public function wkmp_set_zone_id( $set ) {
			$this->mp_data['zone_id'] = is_null( $set ) ? null : absint( $set );
		}

		/**
		 * Get shipping zone id
		 *
		 * @return int $zone_id
		 */
		public function wkmp_get_zone_id() {
			$zone_id = is_null( $this->mp_data['zone_id'] ) ? null : absint( $this->mp_data['zone_id'] );

			return apply_filters( 'wkmp_get_zone_id', $zone_id );
		}

		/**
		 * Get Shipping location
		 *
		 * @param array $final_data Final data.
		 *
		 * @return array
		 */
		public function wkmp_get_formatted_location( $final_data = array() ) {
			$location_parts = array();
			$all_continents = WC()->countries->get_continents();
			$all_countries  = WC()->countries->get_countries();
			$all_states     = WC()->countries->get_states();
			$locations      = $final_data;
			$continents     = array_filter( $locations, array( $this, 'location_is_continent' ) );
			$countries      = array_filter( $locations, array( $this, 'location_is_country' ) );
			$states         = array_filter( $locations, array( $this, 'location_is_state' ) );
			$postcodes      = array_filter( $locations, array( $this, 'location_is_postcode' ) );

			foreach ( $continents as $location ) {
				$location_parts[] = $all_continents[ $location->code ]['name'];
			}

			foreach ( $countries as $location ) {
				$location_parts[] = $all_countries[ $location->code ];
			}

			foreach ( $states as $location ) {
				$location_codes   = explode( ':', $location->code );
				$location_parts[] = $all_states[ $location_codes[0] ][ $location_codes[1] ];
			}

			foreach ( $postcodes as $location ) {
				$location_parts[] = $location->code;
			}

			// Fix display of encoded characters.
			$location_parts = array_map( 'html_entity_decode', $location_parts );

			$return = esc_html__( 'Everywhere', 'wk-marketplace' );

			if ( ! empty( $location_parts ) ) {
				$return = implode( ', ', $location_parts );
			}

			return apply_filters( 'wkmp_get_formatted_location', $return, $final_data );
		}

		/**
		 * Get shipping formatted code
		 *
		 * @param array $final_data final Data.
		 *
		 * @return string
		 */
		public function wkmp_get_formatted_code( $final_data = array() ) {
			$location_parts = array();
			$locations      = $final_data;
			$continents     = array_filter( $locations, array( $this, 'location_is_continent' ) );
			$countries      = array_filter( $locations, array( $this, 'location_is_country' ) );
			$states         = array_filter( $locations, array( $this, 'location_is_state' ) );
			$postcodes      = array_filter( $locations, array( $this, 'location_is_postcode' ) );

			foreach ( $continents as $location ) {
				$location_parts[] = $location->type . ':' . $location->code;
			}

			foreach ( $countries as $location ) {
				$location_parts[] = $location->type . ':' . $location->code;
			}

			foreach ( $states as $location ) {
				$location_parts[] = $location->type . ':' . $location->code;
			}
			foreach ( $postcodes as $location ) {
				$location_parts[] = $location->type . ':' . $location->code;
			}
			// Fix display of encoded characters.
			$location_parts = array_map( 'html_entity_decode', $location_parts );

			$return = esc_html__( 'Everywhere', 'wk-marketplace' );

			if ( ! empty( $location_parts ) ) {
				$return = implode( ', ', $location_parts );
			}

			return apply_filters( 'wkmp_get_formatted_code', $return, $final_data );
		}

		/**
		 * Location type detection
		 *
		 * @param object $location Location.
		 *
		 * @return boolean
		 */
		private function location_is_continent( $location ) {
			return 'continent' === $location->type;
		}

		/**
		 * Location type detection
		 *
		 * @param object $location Location.
		 *
		 * @return boolean
		 */
		private function location_is_country( $location ) {
			return 'country' === $location->type;
		}

		/**
		 * Location type detection
		 *
		 * @param object $location Location.
		 *
		 * @return boolean
		 */
		private function location_is_state( $location ) {
			return 'state' === $location->type;
		}

		/**
		 * Location type detection
		 *
		 * @param object $location Location.
		 *
		 * @return boolean
		 */
		private function location_is_postcode( $location ) {
			return 'postcode' === $location->type;
		}

		/**
		 * Insert zone into the database
		 *
		 * @param array $data Data.
		 */
		public function wkmp_create_zone( $data = array() ) {
			$wpdb_obj          = $this->wpdb;
			$current_seller_id = get_current_user_id();

			if ( ! empty( $data['mp_zone_name'] ) ) {
				$wpdb_obj->insert(
					$wpdb_obj->prefix . 'woocommerce_shipping_zones',
					array(
						'zone_name'  => wp_strip_all_tags( $data['mp_zone_name'] ),
						'zone_order' => 1,
					)
				);

				$insert_id = $wpdb_obj->insert_id;

				wkmp_wc_log( "A new zone is created with zone ID: $insert_id" );

				if ( ! empty( $insert_id ) ) {
					$notice_data = array(
						'action'    => 'Added',
						'zone_name' => $data['mp_zone_name'],
					);
					update_user_meta( $current_seller_id, '_wkmp_shipping_notice_data', $notice_data );

					$insert_id = intval( $insert_id );

					$wpdb_obj->insert(
						$wpdb_obj->prefix . 'mpseller_meta',
						array(
							'seller_id' => intval( $current_seller_id ),
							'zone_id'   => $insert_id,
						)
					);
				}

				if ( ! empty( $data['zone_postcodes'] ) ) {
					$single_postcodes = array_filter( array_map( 'strtoupper', array_map( 'wc_clean', explode( "\n", $data['zone_postcodes'] ) ) ) );
					foreach ( $single_postcodes as $single_postcode ) {
						$wpdb_obj->insert(
							$wpdb_obj->prefix . 'woocommerce_shipping_zone_locations',
							array(
								'zone_id'       => $insert_id,
								'location_code' => $single_postcode,
								'location_type' => 'postcode',
							)
						);
					}
				}

				if ( ! empty( $data['zone_locations'] ) ) {
					$value = $data['zone_locations'];
					foreach ( $value as $key_note ) {
						$final_data         = explode( ':', $key_note );
						$location_code_temp = $final_data[1];
						if ( isset( $final_data[2] ) ) {
							$location_code_temp .= ':' . $final_data[2];
						}

						$wpdb_obj->insert(
							$wpdb_obj->prefix . 'woocommerce_shipping_zone_locations',
							array(
								'zone_id'       => $insert_id,
								'location_code' => $location_code_temp,
								'location_type' => $final_data[0],
							)
						);
					}
				}
			}
		}

		/**
		 * Update zone.
		 *
		 * @param array $data Data.
		 *
		 * @return void
		 */
		public function wkmp_update_zone( $data ) {
			$wpdb_obj = $this->wpdb;

			if ( ! empty( $data['mp_zone_id'] ) ) {
				$current_seller_id = get_current_user_id();
				$wpdb_obj->update(
					$wpdb_obj->prefix . 'woocommerce_shipping_zones',
					array(
						'zone_name'  => wp_strip_all_tags( $data['mp_zone_name'] ),
						'zone_order' => 1,
					),
					array(
						'zone_id' => $data['mp_zone_id'],
					)
				);

				$notice_data = array(
					'action'    => 'Updated',
					'zone_name' => $data['mp_zone_name'],
				);
				update_user_meta( $current_seller_id, '_wkmp_shipping_notice_data', $notice_data );

				$wpdb_obj->delete( $wpdb_obj->prefix . 'woocommerce_shipping_zone_locations', array( 'zone_id' => $data['mp_zone_id'] ) );

				if ( ! empty( $data['zone_postcodes'] ) ) {
					$single_postcodes = array_filter( array_map( 'strtoupper', array_map( 'wc_clean', explode( "\n", $data['zone_postcodes'] ) ) ) );
					foreach ( $single_postcodes as $single_postcode ) {
						$wpdb_obj->insert(
							$wpdb_obj->prefix . 'woocommerce_shipping_zone_locations',
							array(
								'zone_id'       => $data['mp_zone_id'],
								'location_code' => $single_postcode,
								'location_type' => 'postcode',
							)
						);
					}
				}

				if ( isset( $data['zone_locations'] ) && ! empty( $data['zone_locations'] ) ) {
					$value = $data['zone_locations'];
					foreach ( $value as $key_note ) {
						$final_data = explode( ':', $key_note );
						if ( isset( $final_data[2] ) ) {
							$location_code_temp_2 = $final_data[1] . ':' . $final_data[2];
						} else {
							$location_code_temp_2 = $final_data[1];
						}
						$wpdb_obj->insert(
							$wpdb_obj->prefix . 'woocommerce_shipping_zone_locations',
							array(
								'zone_id'       => $data['mp_zone_id'],
								'location_code' => $location_code_temp_2,
								'location_type' => $final_data[0],
							)
						);
					}
				}
			}
		}

		/**
		 * Set zone name
		 *
		 * @param string $set Set value.
		 *
		 * @return void
		 */
		public function wkmp_set_zone_name( $set ) {
			$this->mp_data['zone_name'] = wc_clean( $set );
		}

		/**
		 * Get zone name
		 *
		 * @return mixed
		 */
		public function wkmp_get_zone_name() {
			return $this->mp_data['zone_name'];
		}

		/**
		 * Get seller zone
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_zone( $seller_id ) {
			$wpdb_obj     = $this->wpdb;
			$query        = $wpdb_obj->prepare( "SELECT zone_id FROM {$wpdb_obj->prefix}mpseller_meta WHERE seller_id = %d", esc_attr( $seller_id ) );
			$seller_zones = $wpdb_obj->get_results( $query );

			return apply_filters( 'wkmp_get_seller_zone', $seller_zones, $seller_id );
		}
	}
}
