<?php
/**
 * WKMP order refund data query
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

use WkMarketplace\Helper\Admin;
use WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

if ( ! class_exists( 'WKMP_Order_Refund' ) ) {
	/**
	 * MP_Order_Refund Class.
	 */
	class WKMP_Order_Refund {
		/**
		 * Refund args.
		 *
		 * @var array $refund_args Refund args.
		 */
		protected $refund_args = array();

		/**
		 * Seller id.
		 *
		 * @var array|int seller id.
		 */
		protected $seller_id = array();

		/**
		 * WPDB Objet.
		 *
		 * @var \QM_DB|string|\wpdb WPDB Object.
		 */
		protected $wpdb = '';

		/**
		 * Meta table.
		 *
		 * @var string meta table.
		 */
		protected $mporders_meta_table = '';

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
		 * Constructor of the class.
		 *
		 * WKMP_Order_Refund constructor.
		 *
		 * @param array $args Args.
		 */
		public function __construct( $args = array() ) {
			global $wpdb;
			$this->wpdb                = $wpdb;
			$this->refund_args         = $args;
			$this->seller_id           = get_current_user_id();
			$this->mporders_meta_table = $wpdb->prefix . 'mporders_meta';
			$this->order_db_obj        = Admin\WKMP_Seller_Order_Data::get_instance();
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
		 * Seller refund process
		 *
		 * @throws \Exception \WC_REST_Exception WooCommerce Exception.
		 */
		public function wkmp_process_refund() {
			// Create the refund object.
			$refund = wc_create_refund( $this->refund_args );

			if ( ! empty( $refund ) && is_wp_error( $refund ) ) {
				if ( is_admin() ) {
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php echo esc_html( $refund->get_error_message() ); ?></p>
					</div>
					<?php
				} else {
					wc_print_notice( $refund->get_error_message(), 'error' );
				}
			} else {
				$this->wkmp_set_seller_order_refund_data();
				$order        = wc_get_order( $this->refund_args['order_id'] );
				$seller_email = $this->wkmp_get_seller_email();
				$msg          = esc_html__( 'Refunded successfully.', 'wk-marketplace' );

				do_action( 'wkmp_seller_order_refunded', $order->get_items(), $seller_email, $this->refund_args );

				if ( is_admin() ) {
					?>
					<div class='notice notice-success is-dismissible'>
						<p><?php echo esc_html( $msg ); ?></p>
					</div>
					<?php
				} else {
					wc_print_notice( $msg, 'success' );
				}
			}
		}

		/**
		 * Set refund arguments
		 *
		 * @param array $args Args.
		 *
		 * @return void
		 */
		public function wkmp_set_refund_args( $args = array() ) {
			$this->refund_args = $args;
		}

		/**
		 * Set Seller id
		 *
		 * @param int $seller_id User id.
		 *
		 * @return void
		 */
		public function wkmp_set_seller_id( $seller_id = '' ) {
			$this->seller_id = $seller_id;
		}

		/**
		 * Get seller email
		 *
		 * @return string $email
		 */
		public function wkmp_get_seller_email() {
			return get_userdata( $this->seller_id )->user_email;
		}

		/**
		 * Set seller order refund data
		 *
		 * @return void
		 */
		public function wkmp_set_seller_order_refund_data() {
			$wpdb_obj        = $this->wpdb;
			$order_id        = empty( $this->refund_args['order_id'] ) ? 0 : $this->refund_args['order_id'];
			$this->seller_id = apply_filters( 'wkmp_modify_order_refund_user_id', $this->seller_id, $order_id );

			$commission_helper = Common\WKMP_Commission::get_instance();

			$seller_order_refund_data = $commission_helper->wkmp_get_seller_order_refund_data( $order_id, $this->seller_id );

			if ( empty( $seller_order_refund_data ) ) {
				$seller_order_refund_data = array(
					'line_items'      => $this->refund_args['line_items'],
					'refunded_amount' => round( $this->refund_args['amount'], 2 ),
				);

				$wpdb_obj->insert(
					$this->mporders_meta_table,
					array(
						'seller_id'  => $this->seller_id,
						'order_id'   => $order_id,
						'meta_key'   => '_wkmp_refund_status',
						'meta_value' => maybe_serialize( $seller_order_refund_data ),
					),
					array( '%d', '%d', '%s', '%s' )
				);
			} else {
				$seller_order_refund_data = maybe_unserialize( $seller_order_refund_data );

				foreach ( $this->refund_args['line_items'] as $line_item_id => $line_items ) {
					if ( array_key_exists( $line_item_id, $seller_order_refund_data['line_items'] ) ) {
						if ( empty( $seller_order_refund_data['line_items'][ $line_item_id ]['qty'] ) ) {
							$seller_order_refund_data['line_items'][ $line_item_id ]['qty'] = 0;
						}

						if ( empty( $seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] ) ) {
							$seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] = 0;
						}

						$seller_order_refund_data['line_items'][ $line_item_id ]['qty']          += $line_items['qty'];
						$seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] += round( $line_items['refund_total'], 2 );
					} else {
						$seller_order_refund_data['line_items'][ $line_item_id ]['qty']          = $line_items['qty'];
						$seller_order_refund_data['line_items'][ $line_item_id ]['refund_total'] = round( $line_items['refund_total'], 2 );
					}

					if ( isset( $line_items['refund_tax'] ) && is_iterable( $line_items['refund_tax'] ) ) {
						foreach ( $line_items['refund_tax'] as $tax_key => $tax_value ) {
							if ( isset( $seller_order_refund_data['line_items'][ $line_item_id ]['refund_tax'][ $tax_key ] ) ) {
								$seller_order_refund_data['line_items'][ $line_item_id ]['refund_tax'][ $tax_key ] += $line_items['refund_tax'][ $tax_key ];
							} else {
								$seller_order_refund_data['line_items'][ $line_item_id ]['refund_tax'][ $tax_key ] = $line_items['refund_tax'][ $tax_key ];
							}
						}
					}
				}

				if ( ! isset( $seller_order_refund_data['fully_refunded'] ) ) {
					$seller_order_refund_data['refunded_amount'] += round( $this->refund_args['amount'], 2 );
				}

				$wpdb_obj->update(
					$this->mporders_meta_table,
					array( 'meta_value' => maybe_serialize( $seller_order_refund_data ) ),
					array(
						'seller_id' => $this->seller_id,
						'order_id'  => $order_id,
						'meta_key'  => '_wkmp_refund_status',
					),
					array( '%s' ),
					array( '%d', '%d', '%s' )
				);
			}
			$this->wkmp_update_refund_data_in_seller_sales();
		}

		/**
		 * Set refund arguments
		 *
		 * @return void
		 */
		public function wkmp_update_refund_data_in_seller_sales() {
			$commission_helper = Common\WKMP_Commission::get_instance();

			$sales_data = $commission_helper->wkmp_get_seller_commission_info( $this->seller_id, 'seller_total_ammount, paid_amount, total_refunded_amount', ARRAY_A );
			$order_id   = $this->refund_args['order_id'];

			$paid_status     = $this->order_db_obj->wkmp_get_order_pay_status( $this->seller_id, $order_id );
			$exchange_rate   = apply_filters( 'wkmp_order_currency_exchange_rate', 1, $order_id );
			$exchange_rate   = empty( $exchange_rate ) ? 1 : $exchange_rate;
			$refunded_amount = $this->refund_args['amount'] / $exchange_rate;

			$seller_total_ammount  = floatval( $sales_data['seller_total_ammount'] - round( $refunded_amount, 2 ) );
			$total_refunded_amount = floatval( $sales_data['total_refunded_amount'] + round( $refunded_amount, 2 ) );

			$commission_data = array(
				'seller_total_ammount'  => $seller_total_ammount,
				'total_refunded_amount' => $total_refunded_amount,
			);

			if ( 'paid' === $paid_status ) {
				$paid_amount                    = floatval( $sales_data['paid_amount'] - round( $refunded_amount, 2 ) );
				$commission_data['paid_amount'] = $paid_amount;
			}

			$commission_helper->wkmp_update_seller_commission_info(
				$this->seller_id,
				$commission_data
			);
		}
	}
}
