<?php
/**
 * Local pickup Shipping Handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Includes\Shipping;

if ( ! class_exists( 'WKMP_Local_Pickup_Shipping_Method' ) ) {
	/**
	 * Marketplace Local pickup shipping class.
	 */
	class WKMP_Local_Pickup_Shipping_Method extends WC_Shipping_Method {
		/**
		 * Fee Cost
		 *
		 * @var float
		 */
		public $fee_cost;

		/**
		 * WKMP_Local_Pickup_Shipping_Method constructor.
		 *
		 * @param int $instance_id Instance id.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'mp_local_pickup';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Marketplace Local Pickup Shipping', 'wk-marketplace' );
			$this->method_description = __( 'Custom Local pickup Shipping Method for WooCommerce Marketplace Plugin', 'wk-marketplace' );

			// Load the settings.
			$this->availability = 'including';
			$this->init_form_fields();
			$this->instance_form_fields = $this->wkmp_get_local_pickup_settings();
			$this->init_settings();

			$this->supports = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);

			// Define user set variables.
			$this->enabled = $this->get_option( 'enabled' );
			$this->title   = $this->get_option( 'title' );

			add_filter( 'woocommerce_package_rates', array( $this, 'wc_mp_local_pickup_handler' ), 1, 2 );
		}

		/**
		 * Function to iterate through all packages.
		 *
		 * @param array $rates Rates array.
		 * @param array $package Package rates.
		 *
		 * @return array
		 */
		public function wc_mp_local_pickup_handler( $rates, $package ) {
			$seller_ids               = array();
			$matching_zone_ids        = array();
			$ids_supported_methods    = array();
			$allowed_shipping_methods = array( 0 );

			$manage_shipping = Shipping\WKMP_Manage_Shipping::get_instance();

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

			$seller_ids = apply_filters( 'wkmp_shipping_seller_id', $seller_ids );

			$country   = strtoupper( wc_clean( $package['destination']['country'] ) );
			$state     = strtoupper( wc_clean( $package['destination']['state'] ) );
			$postcode  = wc_normalize_postcode( wc_clean( $package['destination']['postcode'] ) );
			$cache_key = WC_Cache_Helper::get_cache_prefix( 'shipping_zones' ) . 'wc_shipping_zone_' . md5( sprintf( '%s+%s+%s', $country, $state, $postcode ) );
			wp_cache_delete( $cache_key, 'shipping_zones' );

			$matching_zone_id = wp_cache_get( $cache_key, 'shipping_zones' );

			if ( 1 === count( $seller_ids ) ) {
				if ( false === $matching_zone_id ) {
					$matching_zone_id = $manage_shipping->wkmp_get_zone_id_from_package( $package, $seller_ids[0] );
					wp_cache_set( $cache_key, $matching_zone_id, 'shipping_zones' );
				}
			} else {
				foreach ( $seller_ids as $s_value ) {
					$matching_zone_id = $manage_shipping->wkmp_get_zone_id_from_package( $package, $s_value );
					if ( null !== $matching_zone_id ) {
						wp_cache_set( $cache_key, $matching_zone_id, 'shipping_zones' );
						$matching_zone_ids[] = $matching_zone_id;
					}
				}
			}
			if ( ! empty( $matching_zone_ids ) && count( $matching_zone_ids ) > 1 ) {
				foreach ( $matching_zone_ids as $mz_value ) {
					$ids_zone             = new WC_Shipping_Zone( $mz_value ? $mz_value : 0 );
					$ids_shipping_methods = $ids_zone->get_shipping_methods( true );
					foreach ( $ids_shipping_methods as $ids_value ) {
						$ids_supported_methods[ $mz_value ][] = $ids_value->id;
					}
				}
				if ( count( $ids_supported_methods ) > 1 ) {
					$allowed_shipping_methods = call_user_func_array( 'array_intersect', $ids_supported_methods );
				} else {
					$allowed_shipping_methods = reset( $ids_supported_methods );
				}
			}
			if ( empty( $matching_zone_id ) ) {
				$matching_zone_id = 0;
			}

			$shipping_zone    = new WC_Shipping_Zone( $matching_zone_id );
			$shipping_methods = $shipping_zone->get_shipping_methods( true );

			foreach ( $shipping_methods as $shipping_method ) {
				if ( ! empty( $allowed_shipping_methods ) && ! in_array( $shipping_method->id, $allowed_shipping_methods, true ) ) {
					continue;
				} elseif ( ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() ) ) {
					$package['rates'] = $package['rates'] + $shipping_method->get_rates_for_package( $package ); // + instead of array_merge maintains numeric keys
				}
			}

			return $package['rates'];
		}

		/**
		 * Marketplace Local pickup Form Fields goes here.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wk-marketplace' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable WooCommerce Marketplace Local Pickup Shipping', 'wk-marketplace' ),
					'default' => 'yes',
				),
				'title'   => array(
					'title'       => __( 'Marketplace Local Pickup Shipping', 'wk-marketplace' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
					'default'     => __( 'Marketplace Local Pickup Shipping', 'wk-marketplace' ),
				),
			);
		}

		/**
		 * Calculate shipping function.
		 *
		 * @param array $package Packages array.
		 */
		public function calculate_shipping( $package = array() ) {
			$cost             = 0;
			$ids              = array();
			$local_exist      = false;
			$shipping_methods = array();
			$seller_zone_id   = 0;

			if ( 'yes' === $this->enabled ) {
				$manage_shipping = Shipping\WKMP_Manage_Shipping::get_instance();

				foreach ( $package['contents'] as $values ) {
					$product_id     = $values['product_id'];
					$seller_details = get_post_field( 'post_author', $product_id );

					if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
						$seller_details = $values[ "assigned-seller-$product_id" ];
					}

					$check      = get_option( 'wkmp_shipping_option', 'woocommerce' );
					$seller_ids = apply_filters( 'wkmp_shipping_seller_id', $seller_details );
					$seller_ids = array_map( 'intval', $seller_ids );
					$seller_id  = count( $seller_ids ) > 0 ? $seller_ids[0] : 0;

					if ( $seller_id > 0 ) {
						$seller_zone_id   = $manage_shipping->wkmp_get_zone_id_from_package( $package, $seller_ids );
						$shipping_zone    = new WC_Shipping_Zone( $seller_zone_id ? $seller_zone_id : 0 );
						$shipping_methods = $shipping_zone->get_shipping_methods( true );

						foreach ( $shipping_methods as $sm_value ) {
							if ( $this->id === $sm_value->id ) { // If local pickup shipping.
								$local_exist = true;
							}
						}
					}

					if ( 'marketplace' !== $check && empty( $seller_zone_id ) ) {
						$shipping_methods = array();
					}

					if ( $local_exist && ! empty( $shipping_methods ) ) {
						$manage_shipping = Shipping\WKMP_Manage_Shipping::get_instance();
						$qty             = $manage_shipping->wkmp_get_package_item_qty( $package, $seller_ids, $check );
						$cart_total      = $manage_shipping->wkmp_get_cart_content_total( $package, $seller_ids, $check );

						foreach ( $shipping_methods as $shipping_method ) {
							if ( $this->id === $shipping_method->id && ! in_array( $seller_details, $ids, true ) ) { // Local pickup shipping.
								if ( isset( $shipping_method->instance_settings['cost'] ) ) {
									$eval_cost = $this->evaluate_cost(
										$shipping_method->instance_settings['cost'],
										array(
											'qty'  => $qty,
											'cost' => $cart_total,
										)
									);

									$cost += $eval_cost;

									if ( empty( $seller_zone_id ) && 'marketplace' !== $check ) {
										$this->title = $shipping_method->title;
									}

									$ses_obj            = WC()->session->get( 'shipping_sess_cost', array() );
									$shipping_cost_list = WC()->session->get( 'shipping_cost_list', array() );

									$ses_obj[ $seller_id ] = array(
										'cost'  => $eval_cost,
										'title' => $shipping_method->id,
									);

									$shipping_cost_list[ $shipping_method->id ][ $seller_id ] = array(
										'cost'  => $eval_cost,
										'title' => $shipping_method->id,
									);

									WC()->session->set( 'shipping_sess_cost', $ses_obj );
									WC()->session->set( 'shipping_cost_list', $shipping_cost_list );
								}
							}
						}
					}

					$ids[] = $seller_details;
				}

				if ( $local_exist ) {
					// Send the final rate to the user.
					$rate = array(
						'id'    => $this->id,
						'label' => $this->title,
						'cost'  => $cost,
					);

					$this->add_rate( $rate );
				}
			}
		}

		/**
		 * Evaluate a cost from a sum/string.
		 *
		 * @param string $sum Sum to evaluate.
		 * @param array  $args Arguments.
		 *
		 * @return string
		 */
		protected function evaluate_cost( $sum, $args = array() ) {
			include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

			$locale   = localeconv();
			$decimals = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

			$this->fee_cost = $args['cost'];
			// Expand shortcodes.
			add_shortcode( 'fee', array( $this, 'fee' ) );

			$sum = do_shortcode(
				str_replace(
					array(
						'[qty]',
						'[cost]',
					),
					array(
						$args['qty'],
						$args['cost'],
					),
					$sum
				)
			);

			remove_shortcode( 'fee', array( $this, 'fee' ) );

			// Remove whitespace from string.
			$sum = preg_replace( '/\s+/', '', $sum );

			// Remove locale from string.
			$sum = str_replace( $decimals, '.', $sum );

			// Trim invalid start/end characters.
			$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

			// Do the math.
			return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
		}

		/**
		 * Retuning the setting array.
		 *
		 * @return array
		 */
		public function wkmp_get_local_pickup_settings() {
			$settings  = array();
			$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. ', 'wk-marketplace' ) . '<code>10.00 * [qty]</code>.<br/><br/>' . __( 'Use ', 'wk-marketplace' ) . '<code>[qty]</code>' . __( ' for the number of items, ', 'wk-marketplace' ) . '<br/><code>[cost]</code>' . __( ' for the total cost of items, and ', 'wk-marketplace' ) . '<code>[fee percent="10" min_fee="20" max_fee=""]</code>' . __( ' for percentage based fees.', 'wk-marketplace' );

			$settings = array_merge(
				$settings,
				array(
					'title' => array(
						'title'       => __( 'Marketplace Local Pickup Shipping', 'wk-marketplace' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
						'default'     => __( 'Marketplace Local Pickup Shipping', 'wk-marketplace' ),

					),
				)
			);

			$settings = array_merge(
				$settings,
				array(
					'enabled'    => array(
						'title'   => __( 'Enable/Disable', 'wk-marketplace' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable WooCommerce Marketplace Local Pickup Shipping', 'wk-marketplace' ),
						'default' => 'yes',
					),
					'tax_status' => array(
						'title'   => __( 'Tax status', 'wk-marketplace' ),
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'default' => 'taxable',
						'options' => array(
							'taxable' => __( 'Taxable', 'wk-marketplace' ),
							'none'    => _x( 'None', 'Tax status', 'wk-marketplace' ),
						),
					),
					'cost'       => array(
						'title'       => __( 'Cost', 'wk-marketplace' ),
						'type'        => 'text',
						'placeholder' => '',
						'description' => $cost_desc,
						'default'     => '0',
						'desc_tip'    => true,
					),
				)
			);

			return $settings;
		}
	}
}
