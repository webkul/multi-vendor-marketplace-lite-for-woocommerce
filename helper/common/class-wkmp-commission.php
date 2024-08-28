<?php
/**
 * WKMP seller commission queries
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use stdClass;
use WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Commission' ) ) {

	/**
	 * Seller Commission related queries class
	 */
	class WKMP_Commission {
		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb         = $wpdb;
			$this->order_db_obj = Admin\WKMP_Seller_Order_Data::get_instance();
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
		 * Save seller default commission on seller registration.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_set_seller_default_commission( $seller_id ) {
			$wpdb_obj = $this->wpdb;

			$wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpcommision',
				array(
					'seller_id'             => esc_attr( $seller_id ),
					'commision_on_seller'   => '',
					'admin_amount'          => 0,
					'seller_total_ammount'  => 0,
					'paid_amount'           => 0,
					'last_paid_ammount'     => 0,
					'last_com_on_total'     => 0,
					'total_refunded_amount' => 0,
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
		}

		/**
		 * Get seller commission info by seller id.
		 *
		 * @param int    $seller_id Seller ID.
		 * @param string $fields Fields.
		 * @param string $result_type Result type.
		 * @param bool   $single Single value.
		 *
		 * @return array|object $commission_info
		 */
		public function wkmp_get_seller_commission_info( $seller_id, $fields = '', $result_type = 'OBJECT', $single = false ) {
			$wpdb_obj        = $this->wpdb;
			$commission_info = array();

			$fields = empty( $fields ) ? '* ' : $fields;
			$sql    = $wpdb_obj->prepare( "SELECT %1s FROM {$wpdb_obj->prefix}mpcommision WHERE 1=1", esc_sql( $fields ) );

			if ( $seller_id > 0 ) {
				$sql .= $wpdb_obj->prepare( ' AND seller_id=%d', esc_sql( $seller_id ) );

				if ( $single ) {
					$commission_info = $wpdb_obj->get_var( $sql );
				} else {
					$commission_info = $wpdb_obj->get_row( $sql, $result_type );
				}
			} else {
				$commission_info = $wpdb_obj->get_results( $sql, $result_type );
			}

			if ( $seller_id > 0 && ! $single ) {
				if ( ! empty( $commission_info ) && is_iterable( $commission_info ) && 1 === count( $commission_info ) ) {
					$commission_info = $commission_info[0];
				}

				$commission_info = ( ! empty( $commission_info ) && count( (array) $commission_info ) > 0 ) ? $commission_info : $this->wkmp_get_default_commission_info( $seller_id );
			}

			return apply_filters( 'wkmp_get_seller_commission_info', $commission_info, $seller_id );
		}

		/**
		 * Get default seller commission info.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return object
		 */
		public function wkmp_get_default_commission_info( $seller_id ) {
			$info                        = new stdClass();
			$info->ID                    = 0;
			$info->seller_id             = $seller_id;
			$info->commision_on_seller   = '';
			$info->admin_amount          = 0;
			$info->seller_total_ammount  = 0;
			$info->paid_amount           = 0;
			$info->last_paid_ammount     = 0;
			$info->last_com_on_total     = 0;
			$info->total_refunded_amount = 0;
			$info->seller_payment_method = 0;
			$info->payment_id_desc       = 0;

			return $info;
		}

		/**
		 * Delete commission data.
		 *
		 * @param int $seller_id seller ID.
		 */
		public function wkmp_delete_commission( $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->delete( "{$wpdb_obj->prefix}mpcommision", array( 'seller_id' => esc_attr( $seller_id ) ), array( '%d' ) );
		}

		/**
		 * Update Seller Commission data.
		 *
		 * @param int $seller_id seller ID.
		 * @param int $order_id order ID.
		 */
		public function wkmp_update_seller_commission( $seller_id, $order_id ) {
			$result            = 0;
			$seller_order_data = $this->wkmp_get_seller_final_order_info( $order_id, $seller_id );

			$sel_pay_amount = $seller_order_data['total_seller_amount'];
			$response       = $this->wkmp_update( $seller_id, $sel_pay_amount );

			if ( 0 === intval( $response['error'] ) ) {
				$result = $sel_pay_amount;
			}

			return apply_filters( 'wkmp_update_seller_commission', $result, $seller_id, $order_id );
		}

		/**
		 * Updating seller commission .
		 *
		 * @param int $seller_id seller ID.
		 * @param int $pay_amount admin Commission Rate.
		 */
		public function wkmp_update( $seller_id, $pay_amount ) {
			$wpdb_obj = $this->wpdb;
			$result   = array(
				'error' => 1,
			);

			$seller_id = intval( $seller_id );

			if ( ! empty( $seller_id ) && ! empty( $pay_amount ) ) {
				$seller_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpcommision WHERE seller_id = %d", esc_attr( $seller_id ) ) );

				if ( ! empty( $seller_data ) ) {
					$paid_amount      = $seller_data[0]->paid_amount + $pay_amount;
					$last_paid_amount = $pay_amount;

					$res = $wpdb_obj->update(
						"{$wpdb_obj->prefix}mpcommision",
						array(
							'paid_amount'       => wc_format_decimal( $paid_amount, 2 ),
							'last_paid_ammount' => wc_format_decimal( $last_paid_amount, 2 ),
						),
						array( 'seller_id' => $seller_id ),
						array( '%f', '%f', '%f' ),
						array( '%d' )
					);
					if ( $res ) {
						$result = array(
							'error' => 0,
							'msg'   => esc_html__( 'Amount Transferred Successfully.!', 'wk-marketplace' ),
						);
					}
				}
			}

			return $result;
		}

		/**
		 * Calculate product commission.
		 *
		 * @param int   $product_id product id.
		 * @param int   $pro_qty product quantity.
		 * @param float $pro_price product price.
		 * @param int   $assigned_seller seller field.
		 * @param float $tax_amount tax amount.
		 *
		 * @return array
		 */
		public function wkmp_calculate_product_commission( $product_id, $pro_qty, $pro_price, $assigned_seller, $tax_amount = 0 ) {
			$data = array();

			if ( ! empty( $product_id ) ) {
				$seller_id = $assigned_seller;

				if ( empty( $seller_id ) ) {
					$parent_post_id = wp_get_post_parent_id( $product_id ); // In case variation get variable id to get correct seller id.
					$parent_post_id = empty( $parent_post_id ) ? $product_id : $parent_post_id;
					$seller_id      = get_post_field( 'post_author', $parent_post_id );
				}

				$commission_on_seller = $this->wkmp_get_seller_commission_info( $seller_id, 'commision_on_seller', ARRAY_A, true );
				$commission_on_seller = apply_filters( 'wkmp_alter_seller_commission', $commission_on_seller, $product_id, $pro_qty, $seller_id );

				$product_price      = $pro_price + $tax_amount;
				$comm_type          = 'percent';
				$commission_applied = 0;
				$admin_commission   = $product_price;

				if ( empty( $commission_on_seller ) ) {
					$default_commission = floatval( get_option( '_wkmp_default_commission', 0 ) );
					$admin_commission   = ( $product_price / 100 ) * $default_commission;
					$commission_applied = $default_commission;
				} else {
					$admin_commission   = ( $product_price / 100 ) * $commission_on_seller;
					$commission_applied = $commission_on_seller;
				}

				$data = array(
					'seller_id'          => $seller_id,
					'total_amount'       => $product_price,
					'admin_commission'   => $admin_commission,
					'seller_amount'      => $product_price - $admin_commission,
					'commission_applied' => $commission_applied,
					'commission_type'    => $comm_type,
				);
			}

			return apply_filters( 'wkmp_calculate_product_commission', $data );
		}

		/**
		 * Get seller ids regarding order id.
		 *
		 * @param int $order_id order id.
		 */
		public function wkmp_get_sellers_in_order( $order_id = '' ) {
			$wpdb_obj     = $this->wpdb;
			$seller_ids   = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT DISTINCT seller_id FROM {$wpdb_obj->prefix}mporders WHERE order_id = %d", esc_attr( $order_id ) ) );
			$seller_array = wp_list_pluck( $seller_ids, 'seller_id' );

			return apply_filters( 'wkmp_get_sellers_in_order', $seller_array, $order_id );
		}

		/**
		 * Returns final seller data according to order id.
		 *
		 * @param int $order_id order id.
		 * @param int $seller_id seller id.
		 *
		 * @return array
		 */
		public function wkmp_get_seller_final_order_info( $order_id, $seller_id ) {
			$wpdb_obj   = $this->wpdb;
			$sell_order = wc_get_order( $order_id );

			$data = array(
				'id'                  => $order_id . '-' . $seller_id,
				'order_id'            => $order_id,
				'product'             => array(),
				'quantity'            => 0,
				'product_total'       => 0,
				'total_seller_amount' => 0,
				'refunded_amount'     => 0,
				'total_commission'    => 0,
				'status'              => '',
				'shipping'            => 0,
				'method_id'           => '',
				'seller_id'           => '',
				'discount'            => array(),
				'action'              => '',
				'tax'                 => '',
			);

			if ( $sell_order instanceof \WC_Order ) {
				$or_status    = $sell_order->get_status();
				$sel_ord_data = $this->wkmp_get_seller_order_info( $order_id, $seller_id );

				$sel_amt   = 0;
				$admin_amt = 0;

				if ( ! empty( $sel_ord_data ) ) {
					$sel_amt   = $sel_ord_data['total_sel_amt'] + $sel_ord_data['ship_data'];
					$admin_amt = $sel_ord_data['total_comision'];
				}

				$seller_order_tax = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'seller_order_tax' ", $seller_id, $order_id ) );

				if ( ! empty( $seller_order_tax ) ) {
					$sel_amt += $seller_order_tax;
				}

				$paid_status = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );
				$act_status  = empty( $paid_status ) ? 'not_paid' : $paid_status;

				$seller_order_refund_data = $this->wkmp_get_seller_order_refund_data( $order_id, $seller_id );
				$refunded_amount          = empty( $seller_order_refund_data['refunded_amount'] ) ? 0 : $seller_order_refund_data['refunded_amount'];

				$data['product']       = $sel_ord_data['pro_info'];
				$data['quantity']      = $sel_ord_data['total_qty'];
				$data['product_total'] = $sel_ord_data['pro_total'];
				$data['shipping']      = $sel_ord_data['ship_data'];
				$data['method_id']     = $sel_ord_data['method_id'];
				$data['method_id']     = $sel_ord_data['method_id'];
				$data['seller_id']     = $sel_ord_data['seller_id'];
				$data['discount']      = $sel_ord_data['discount'];

				$data['total_seller_amount'] = $sel_amt;
				$data['refunded_amount']     = $refunded_amount;
				$data['total_commission']    = $admin_amt;
				$data['status']              = $or_status;
				$data['action']              = $act_status;
				$data['tax']                 = $seller_order_tax;
			}

			return apply_filters( 'wk_marketplace_final_seller_ord_info', $data );
		}

		/**
		 * Get seller refund data
		 *
		 * @param int    $order_id Order id.
		 * @param string $seller_id Seller id.
		 *
		 * @return array|mixed|string
		 */
		public function wkmp_get_seller_order_refund_data( $order_id, $seller_id = '' ) {
			$wpdb_obj = $this->wpdb;

			$seller_order_refund_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = %s", esc_attr( $seller_id ), esc_attr( $order_id ), esc_attr( '_wkmp_refund_status' ) ) );
			$return                   = ! empty( $seller_order_refund_data ) ? maybe_unserialize( $seller_order_refund_data ) : array();

			return apply_filters( 'wkmp_get_seller_order_refund_data', $return, $order_id, $seller_id );
		}

		/**
		 * Update seller data according to order id.
		 *
		 * @param int $order_id order id.
		 */
		public function wkmp_update_seller_order_info( $order_id ) {
			if ( $order_id ) {
				$wpdb_obj = $this->wpdb;
				$sellers  = $this->wkmp_get_sellers_in_order( $order_id );
				$order    = wc_get_order( $order_id );

				do_action( 'wkmp_manage_order_fee', $order_id );

				if ( ! empty( $sellers ) ) {
					foreach ( $sellers as $seller_id ) {
						$sel_ord_data = $this->wkmp_get_seller_order_info( $order_id, $seller_id );
						$sel_amt      = 0;
						$admin_amt    = 0;

						$sel_ord_data = apply_filters( 'wk_marketplace_manage_order_fee', $sel_ord_data, $order_id, $seller_id );

						if ( ! empty( $sel_ord_data ) ) {
							$sel_amt   = apply_filters( 'mpmc_get_default_price', $sel_ord_data['total_sel_amt'] + $sel_ord_data['ship_data'], $order->get_currency() );
							$admin_amt = apply_filters( 'mpmc_get_default_price', $sel_ord_data['total_comision'], $order->get_currency() );
						}

						$sel_com_data = $wpdb_obj->get_results( $wpdb_obj->prepare( " SELECT * FROM {$wpdb_obj->prefix}mpcommision WHERE seller_id = %d", esc_attr( $seller_id ) ) );

						if ( $sel_com_data ) {
							$sel_com_data  = $sel_com_data[0];
							$admin_amount  = ( floatval( $sel_com_data->admin_amount ) + $admin_amt );
							$seller_amount = ( floatval( $sel_com_data->seller_total_ammount ) + $sel_amt );

							$wpdb_obj->get_results( $wpdb_obj->prepare( " UPDATE {$wpdb_obj->prefix}mpcommision SET admin_amount = %f, seller_total_ammount = %f, last_com_on_total = %f WHERE seller_id = %d", esc_attr( $admin_amount ), esc_attr( $seller_amount ), esc_attr( $seller_amount ), esc_attr( $seller_id ) ) );
						} else {
							$wpdb_obj->insert(
								$wpdb_obj->prefix . 'mpcommision',
								array(
									'seller_id'            => $seller_id,
									'admin_amount'         => wc_format_decimal( $admin_amt, 2 ),
									'seller_total_ammount' => wc_format_decimal( $sel_amt, 2 ),
								)
							);
						}
					}
				}
			}
		}

		/**
		 * Returns seller data according to order id.
		 *
		 * @param int $order_id order id.
		 * @param int $seller_id seller id.
		 *
		 * @return array $data
		 */
		public function wkmp_get_seller_order_info( $order_id, $seller_id ) {
			$wpdb_obj = $this->wpdb;

			$discount = array(
				'seller' => 0,
				'admin'  => 0,
			);

			$product_info        = array();
			$quantity            = 0;
			$product_total       = 0;
			$total_seller_amount = 0;
			$total_commission    = 0;
			$shipping            = 0;
			$method_id           = '';

			$sel_order = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders WHERE seller_id = %d AND order_id = %d", esc_attr( $seller_id ), esc_attr( $order_id ) ) );

			if ( ! empty( $sel_order ) ) {
				foreach ( $sel_order as $ord_info ) {
					if ( ! empty( $ord_info->product_id ) ) {
						$product_info[ $ord_info->product_id ] = array(
							'id'         => $ord_info->product_id,
							'title'      => get_the_title( $ord_info->product_id ),
							'quantity'   => $ord_info->quantity,
							'line_total' => $ord_info->amount,
							'discount'   => $ord_info->discount_applied,
							'commission' => $ord_info->admin_amount,
						);
					}

					if ( ! empty( $ord_info->quantity ) ) {
						$quantity = $quantity + $ord_info->quantity;
					}

					if ( ! empty( $ord_info->amount ) ) {
						$product_total = $product_total + $ord_info->amount;
					}

					if ( ! empty( $ord_info->seller_amount ) ) {
						$total_seller_amount = $total_seller_amount + $ord_info->seller_amount;
					}

					if ( ! empty( $ord_info->admin_amount ) ) {
						$total_commission = $total_commission + $ord_info->admin_amount;
					}

					if ( ! empty( $ord_info->discount_applied ) ) {
						$discount_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'discount_code' ", $seller_id, $ord_info->order_id ) );

						if ( ! empty( $discount_data ) ) {
							$discount['seller'] = $discount['seller'] + $ord_info->discount_applied;
						} elseif ( $ord_info->discount_applied > 0 ) {
							$discount['admin'] = $discount['admin'] + $ord_info->discount_applied;
						}
					}

					$ship_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key IN ('shipping_cost', 'shipping_method_id') ", esc_attr( $seller_id ), esc_attr( $ord_info->order_id ) ) );

					if ( ! empty( $ship_data ) ) {
						$shipping_values = wp_list_pluck( $ship_data, 'meta_value' );
						$method_id       = count( $shipping_values ) > 0 ? $shipping_values[0] : $method_id;
						$shipping        = count( $shipping_values ) > 1 ? $shipping_values[1] : $shipping;
					}
				}
			}

			$data = array(
				'pro_info'       => $product_info,
				'total_qty'      => $quantity,
				'pro_total'      => $product_total,
				'total_sel_amt'  => $total_seller_amount,
				'total_comision' => $total_commission,
				'discount'       => $discount,
				'ship_data'      => $shipping,
				'method_id'      => $method_id,
				'seller_id'      => $seller_id,
			);

			return apply_filters( 'wkmp_get_seller_final_order_info', $data, $order_id, $seller_id );
		}

		/**
		 * Get seller commission
		 *
		 * @param int $order_id order id.
		 * @param int $seller_id seller id.
		 *
		 * @return array $data
		 */
		public function wkmp_get_sel_commission_via_order( $order_id, $seller_id ) {
			$wpdb_obj            = $this->wpdb;
			$data                = array();
			$i                   = 0;
			$ord_id              = array();
			$product             = array();
			$quantity            = array();
			$product_total       = array();
			$total_seller_amount = array();
			$total_commission    = array();
			$status              = array();
			$paid_status         = array();

			$sel_order = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders WHERE seller_id = %d AND order_id = %d", esc_attr( $seller_id ), esc_attr( $order_id ) ) );

			if ( ! empty( $sel_order ) ) {
				$order_arr      = array();
				$id             = array();
				$shipping       = array();
				$discount       = array();
				$action         = array();
				$final_discount = array();

				foreach ( $sel_order as $value ) {
					$discount_arr = array();
					$o_id         = $value->order_id;
					$order        = wc_get_order( $o_id );
					$product_id   = $value->product_id;

					if ( in_array( $o_id, $order_arr, true ) ) {
						$key                      = array_search( $o_id, $order_arr, true );
						$product_info             = get_the_title( $product_id ) . '( #' . $product_id . ' )';
						$quantity_info            = $value->quantity;
						$product_total_info       = $value->amount;
						$total_seller_amount_info = $value->seller_amount;
						$total_commission_info    = $value->admin_amount;
						$discount_by              = '';
						$o_discount               = 0;

						if ( 0 !== $value->discount_applied ) {
							$discount_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'discount_amt' ", esc_attr( $seller_id ), esc_attr( $o_id ) ) );

							if ( ! empty( $discount_data ) ) {
								$discount_by              = 'S';
								$total_seller_amount_info = $total_seller_amount_info - $value->discount_applied;
								$o_discount               = $value->discount_applied;
							} else {
								$discount_by           = 'A';
								$total_commission_info = $total_commission_info - $value->discount_applied;
								$o_discount            = $value->discount_applied;
							}
						}

						$discount_arr = $data[ $key ]['discount'];

						if ( '' !== $discount_by && 0 !== $o_discount ) {
							array_push(
								$discount_arr,
								array(
									'by'     => $discount_by,
									'amount' => $o_discount,
								)
							);
						}

						$data[ $key ]['product']             = $data[ $key ]['product'] . ' + ' . $product_info;
						$data[ $key ]['quantity']            = $data[ $key ]['quantity'] + $quantity_info;
						$data[ $key ]['discount']            = $discount_arr;
						$data[ $key ]['product_total']       = $data[ $key ]['product_total'] + $product_total_info;
						$data[ $key ]['total_seller_amount'] = $data[ $key ]['total_seller_amount'] + $total_seller_amount_info;
						$data[ $key ]['total_commission']    = $data[ $key ]['total_commission'] + $total_commission_info;

						continue;
					} else {
						$order_arr[ $i ] = $o_id;
					}

					$product_id            = $value->product_id;
					$id[]                  = $o_id . '-' . $seller_id;
					$ord_id[]              = $o_id;
					$product[]             = get_the_title( $product_id ) . '( #' . $product_id . ' )';
					$quantity[]            = $value->quantity;
					$product_total[]       = $value->amount;
					$total_seller_amount[] = $value->seller_amount;
					$total_commission[]    = $value->admin_amount;
					$status[]              = $order->get_status();

					$ship_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'shipping_cost' ", $seller_id, $o_id ) );

					if ( ! empty( $ship_data ) ) {
						$shipping[] = $ship_data[0]->meta_value;
					} else {
						$shipping[] = 0;
					}

					$discount_by = '';

					if ( 0 !== $value->discount_applied ) {
						$discount_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'discount_amt' ", $seller_id, $o_id ) );
						if ( ! empty( $discount_data ) ) {
							$discount_by = 'S';
							$discount[]  = $value->discount_applied;
						} else {
							$discount_by = 'A';
							$discount[]  = $value->discount_applied;
						}
					} else {
						$discount[] = 0;
					}

					$pay_data = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mporders_meta WHERE seller_id = %d AND order_id = %d AND meta_key = 'paid_status' ", esc_attr( $seller_id ), esc_attr( $o_id ) ) );

					if ( ! empty( $pay_data ) ) {
						$paid_status[] = $pay_data;
					} else {
						$paid_status[] = 'Not Paid';
					}

					if ( 'paid' === $paid_status[ $i ] ) {
						$action[] = '<button class="button button-primary" class="admin-order-pay" disabled>' . esc_html__( 'Paid', 'wk-marketplace' ) . '</button>';
					} else {
						$action[] = '<a href="javascript:void(0)" data-id="' . esc_attr( $id[ $i ] ) . '" class="page-title-action admin-order-pay">' . esc_html__( 'Pay', 'wk-marketplace' ) . '</a>';
					}

					if ( 'S' === $discount_by ) {
						$total_seller_amount[ $i ] = $total_seller_amount[ $i ] + $shipping[ $i ] - $discount[ $i ];
						$final_discount[]          = $discount[ $i ];
					} else {
						$total_seller_amount[ $i ] = $total_seller_amount[ $i ] + $shipping[ $i ];
					}

					if ( 'A' === $discount_by ) {
						$total_commission[ $i ] = $total_commission[ $i ] - $discount[ $i ];
						$final_discount[]       = $discount[ $i ];
					} else {
						$total_commission[ $i ] = $total_commission[ $i ];
					}

					if ( '' === $discount_by ) {
						$final_discount[] = $discount[ $i ];
					}

					if ( '' !== $discount_by && 0 !== $final_discount[ $i ] ) {
						array_push(
							$discount_arr,
							array(
								'by'     => $discount_by,
								'amount' => $final_discount[ $i ],
							)
						);
					}

					$data[] = array(
						'id'                  => $id[ $i ],
						'order_id'            => $ord_id[ $i ],
						'product'             => $product[ $i ],
						'quantity'            => $quantity[ $i ],
						'product_total'       => $product_total[ $i ],
						'total_seller_amount' => $total_seller_amount[ $i ],
						'total_commission'    => $total_commission[ $i ],
						'status'              => $status[ $i ],
						'shipping'            => $shipping[ $i ],
						'discount'            => $discount_arr,
						'paid_status'         => $paid_status[ $i ],
						'action'              => $action[ $i ],
					);
					++$i;
				}
			}

			return apply_filters( 'wkmp_get_sel_comission_via_order', $data[0], $order_id, $seller_id );
		}

		/**
		 * Update seller commission
		 *
		 * @param int $seller_id Seller ID.
		 * @param int $info Commission info.
		 *
		 * @return boolean
		 */
		public function wkmp_update_seller_commission_info( $seller_id, $info ) {
			if ( $seller_id ) {
				$data         = array();
				$where        = array( 'seller_id' => $seller_id );
				$format       = array();
				$where_format = array( '%d' );

				if ( isset( $info['commision_on_seller'] ) ) {
					$data['commision_on_seller'] = $info['commision_on_seller'];
					$format[]                    = '%f';
				}

				if ( isset( $info['admin_amount'] ) ) {
					$data['admin_amount'] = $info['admin_amount'];
					$format[]             = '%f';
				}

				if ( isset( $info['seller_total_ammount'] ) ) {
					$data['seller_total_ammount'] = $info['seller_total_ammount'];
					$format[]                     = '%f';
				}

				if ( isset( $info['paid_amount'] ) ) {
					$data['paid_amount'] = $info['paid_amount'];
					$format[]            = '%f';
				}

				if ( isset( $info['last_paid_ammount'] ) ) {
					$data['last_paid_ammount'] = $info['last_paid_ammount'];
					$format[]                  = '%f';
				}

				if ( isset( $info['last_com_on_total'] ) ) {
					$data['last_com_on_total'] = $info['last_com_on_total'];
					$format[]                  = '%f';
				}

				if ( isset( $info['total_refunded_amount'] ) ) {
					$data['total_refunded_amount'] = $info['total_refunded_amount'];
					$format[]                      = '%f';
				}

				if ( ! empty( $info['seller_payment_method'] ) ) {
					$data['seller_payment_method'] = $info['seller_payment_method'];
					$format[]                      = '%s';
				}

				if ( ! empty( $info['payment_id_desc'] ) ) {
					$data['payment_id_desc'] = $info['payment_id_desc'];
					$format[]                = '%s';
				}

				$query = $this->wpdb->update(
					$this->wpdb->prefix . 'mpcommision',
					$data,
					$where,
					$format,
					$where_format
				);
			}

			return isset( $query ) && $query ? true : false;
		}
	}
}
