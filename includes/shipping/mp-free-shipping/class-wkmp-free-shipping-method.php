<?php
/**
 * Marketplace free shipping handler.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @since 5.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if access directly.

use WkMarketplace\Includes\Shipping;

if ( ! class_exists( 'WKMP_Free_Shipping_Method' ) ) {
	/**
	 * Marketplace Free shipping class.
	 */
	class WKMP_Free_Shipping_Method extends WC_Shipping_Method {
		/**
		 * Requires.
		 *
		 * @var string $requires.
		 */
		public $requires;

		/**
		 * Min amount.
		 *
		 * @var string $min_amount.
		 */
		public $min_amount;

		/**
		 * Function constructor.
		 *
		 * WKMP_Free_Shipping_Method constructor.
		 *
		 * @param int $instance_id instance id.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'mp_free_shipping';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Marketplace Free Shipping', 'wk-marketplace' );
			$this->method_description = __( 'Custom Free Shipping Method for WooCommerce Marketplace Plugin', 'wk-marketplace' );

			// Load the settings.
			$this->availability = 'including';
			$this->init_form_fields();
			$this->instance_form_fields = $this->wkmp_get_free_shipping_settings();
			$this->init_settings();

			$this->supports = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);

			// Define user set variables.
			$this->enabled    = $this->get_option( 'enabled' );
			$this->title      = $this->get_option( 'title' );
			$this->min_amount = $this->get_option( 'min_amount', 0 );
			$this->requires   = $this->get_option( 'requires' );
			add_action( 'admin_footer', array( $this, 'enqueue_admin_js' ) ); // Priority needs to be higher than wc_print_js (25).
			add_filter( 'woocommerce_package_rates', array( $this, 'wc_mp_free_shipping_handler' ), 1, 2 );
		}

		/**
		 * Enqueue JS to handle free shipping options.
		 *
		 * Static so that's enqueued only once.
		 */
		public function enqueue_admin_js() {
			wc_enqueue_js(
				"jQuery( function( $ ) {
				function wcFreeShippingShowHideMinAmountField( el ) {
					var form = $( el ).closest( 'form' );
					var minAmountField = $( '#woocommerce_mp_free_shipping_min_amount', form ).closest( 'tr' );
					var ignoreDiscountField = $( '#woocommerce_mp_free_shipping_ignore_discounts', form ).closest( 'tr' );
					if ( 'coupon' === $( el ).val() || '' === $( el ).val() ) {
						minAmountField.hide();
						ignoreDiscountField.hide();
					} else {
						minAmountField.show();
						ignoreDiscountField.show();
					}
				}

				$( document.body ).on( 'change', '#woocommerce_mp_free_shipping_requires', function() {
					wcFreeShippingShowHideMinAmountField( this );
				});

				// Change while load.
				$( '#woocommerce_mp_free_shipping_requires' ).change();
				$( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
					if ( 'wc-modal-shipping-method-settings' === target ) {
						wcFreeShippingShowHideMinAmountField( $( '#wc-backbone-modal-dialog #woocommerce_mp_free_shipping_requires', evt.currentTarget ) );
					}
				} );
			});"
			);
		}

		/**
		 * Function to iterate through all packages.
		 *
		 * @param array $rates Rates array.
		 * @param array $package Package rates.
		 *
		 * @return array
		 */
		public function wc_mp_free_shipping_handler( $rates, $package ) {
			$seller_ids               = array();
			$matching_zone_ids        = array();
			$ids_supported_methods    = array();
			$allowed_shipping_methods = array( 0 );
			$manage_shipping          = Shipping\WKMP_Manage_Shipping::get_instance();

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

			$zone             = new WC_Shipping_Zone( $matching_zone_id );
			$shipping_methods = $zone->get_shipping_methods( true );

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
		 * Marketplace Free Form Fields goes here.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wk-marketplace' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable WooCommerce Marketplace Free Shipping', 'wk-marketplace' ),
					'default' => 'yes',
				),
				'title'   => array(
					'title'       => __( 'Marketplace Free Shipping', 'wk-marketplace' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
					'default'     => __( 'Marketplace Free Shipping', 'wk-marketplace' ),
				),
			);
		}

		/**
		 * Calculate shipping function.
		 *
		 * @param array $package Packages array.
		 */
		public function calculate_shipping( $package = array() ) {
			$ids                = array();
			$is_available       = true;
			$seller_cart_amount = array();

			if ( 'yes' === $this->enabled ) {
				$manage_shipping = Shipping\WKMP_Manage_Shipping::get_instance();

				foreach ( $package['contents'] as $values ) {
					$product_id = $values['product_id'];
					$seller_id  = get_post_field( 'post_author', $product_id );

					if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
						$seller_id = $values[ "assigned-seller-$product_id" ];
					}

					$seller_ids     = array( $seller_id );
					$check          = get_option( 'wkmp_shipping_option', 'woocommerce' );
					$seller_details = apply_filters( 'wkmp_shipping_seller_id', $seller_ids );

					$seller_details = array_map( 'intval', $seller_details );
					$seller_id      = ( is_array( $seller_details ) && count( $seller_details ) > 0 ) ? $seller_details[0] : 0;

					if ( $seller_id > 0 ) {
						$seller_zones     = $manage_shipping->wkmp_get_zone_id_from_package( $package, $seller_id );
						$method           = false;
						$zone             = new WC_Shipping_Zone( $seller_zones ? $seller_zones : 0 );
						$shipping_methods = $zone->get_shipping_methods( true );

						foreach ( $shipping_methods as $sm_value ) {
							if ( 'mp_free_shipping' === $sm_value->id ) {
								$method = true;
							}
						}
					}

					if ( 'marketplace' !== $check && empty( $seller_zones ) ) {
						$seller_zones = 0;
					}

					if ( $method && ! empty( $seller_zones ) ) {
						if ( ! empty( $shipping_methods ) ) {
							foreach ( $shipping_methods as $shipping_method ) {
								if ( 'mp_free_shipping' === $shipping_method->id ) {
									if ( ! in_array( $seller_details, $ids, true ) ) {
										if ( empty( $seller_zones ) && 'marketplace' !== $check ) {
											$this->title = $shipping_method->title;
										}

										$ses_obj            = WC()->session->get( 'shipping_sess_cost', array() );
										$shipping_cost_list = WC()->session->get( 'shipping_cost_list', array() );

										$ses_obj[ $seller_id ] = array(
											'cost'  => 0,
											'title' => $shipping_method->id,
										);

										$shipping_cost_list[ $shipping_method->id ][ $seller_id ] = array(
											'cost'  => 0,
											'title' => $shipping_method->id,
										);

										WC()->session->set( 'shipping_sess_cost', $ses_obj );
										WC()->session->set( 'shipping_cost_list', $shipping_cost_list );

										$is_available = true;

										if ( ! empty( $shipping_method->requires ) && in_array( $shipping_method->requires, array( 'min_amount' ), true ) ) {
											$seller_cart_amount[ $seller_id ]['amount'] = $values['line_total'];
											$seller_cart_amount[ $seller_id ]['min']    = $shipping_method->min_amount;
										}
									} elseif ( ! empty( $seller_cart_amount[ $seller_id ]['amount'] ) ) {
											$seller_cart_amount[ $seller_id ]['amount'] += $values['line_total'];
									}
								}
							}
						} else {
							$is_available = false;
							break;
						}
					} else {
						$is_available = false;
						break;
					}

					$ids[] = $seller_details;
				}

				// Min cart amount requirement validation.
				if ( ! empty( $seller_cart_amount ) ) {
					foreach ( $seller_cart_amount as $cart_amount_data ) {
						if ( $cart_amount_data['min'] > $cart_amount_data['amount'] ) {
							$is_available = false;
							break;
						}
					}
				}

				if ( $is_available ) {
					// Send the final rate to the user.
					$rate = array(
						'id'    => $this->id,
						'label' => $this->title,
						'cost'  => 0,
					);

					$this->add_rate( $rate );
				}
			}
		}

		/**
		 * Retuning the setting array.
		 *
		 * @return array
		 */
		public function wkmp_get_free_shipping_settings() {
			$settings = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'wk-marketplace' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable WooCommerce Marketplace Free Shipping', 'wk-marketplace' ),
					'default' => 'yes',
				),
				'title'      => array(
					'title'       => __( 'Title', 'wk-marketplace' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
					'default'     => $this->method_title,
					'desc_tip'    => true,
				),
				'requires'   => array(
					'title'   => __( 'Free shipping requires...', 'wk-marketplace' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'default' => '',
					'options' => array(
						''           => __( 'N/A', 'wk-marketplace' ),
						'min_amount' => __( 'A minimum order amount', 'wk-marketplace' ),
					),
				),
				'min_amount' => array(
					'title'       => __( 'Minimum order amount', 'wk-marketplace' ),
					'type'        => 'price',
					'placeholder' => wc_format_localized_price( 0 ),
					'description' => __( 'Users will need to spend this amount to get free shipping (if enabled above).', 'wk-marketplace' ),
					'default'     => '0',
					'desc_tip'    => true,
				),
			);

			return $settings;
		}
	}
}
