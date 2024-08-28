<?php
/**
 * Front hooks template.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @version 5.0.0
 */

namespace WkMarketplace\Includes\Common;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WkMarketplace\Helper\Common;
use WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Notification_Functions' ) ) {
	/**
	 * Class WKMP_Notification_Functions.
	 *
	 * @package WkMarketplace\Includes\Common
	 */
	class WKMP_Notification_Functions {
		/**
		 * Instance variable.
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * DB Object.
		 *
		 * @var Common\WKMP_Seller_Notification
		 */
		private $db_obj;

		/**
		 * Order db object.
		 *
		 * @var Admin\WKMP_Seller_Order_Data $order_db_obj Order db object.
		 */
		private $order_db_obj;

		/**
		 * WKMP_Notification_Functions constructor.
		 */
		public function __construct() {
			$this->db_obj       = Common\WKMP_Seller_Notification::get_instance();
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
		 * Send mail notifications to seller on Block based (Store API) checkout order processed.
		 *
		 * @param object $order WC_Order Object.
		 *
		 * @hooked 'woocommerce_store_api_checkout_order_processed' action hook.
		 *
		 * @return void
		 */
		public function wkmp_send_email_on_block_based_order_processed( $order ) {
			if ( $order instanceof \WC_Order ) {
				$order_id = $order->get_id();
				$this->wkmp_send_email_on_order_processed( $order_id );
			}
		}

		/**
		 * Send mail notifications to seller on checkout order processed.
		 *
		 * @param int $order_id Order id.
		 *
		 * @hooked woocommerce_checkout_order_processed
		 */
		public function wkmp_send_email_on_order_processed( $order_id ) {
			$seller_ids           = array();
			$order                = wc_get_order( $order_id );
			$send_mail_to_seller  = apply_filters( 'wkmp_send_notification_mail_to_seller_for_new_order', true, $order );
			$send_product_ordered = apply_filters( 'wkmp_send_product_ordered_mail_to_seller', true, $order );

			if ( $send_mail_to_seller && $send_product_ordered ) {
				$this->wkmp_send_mail_to_inform_seller( $order );
			}

			$items       = $order->get_items();
			$customer_id = $order->get_customer_id();

			foreach ( $items as $item ) {
				$product      = get_post( $item['product_id'] );
				$seller_ids[] = $product->post_author;
			}

			$now     = new \DateTime( 'now' );
			$content = 'wkmp_new_order_by_customer_id_' . $customer_id;

			foreach ( $seller_ids as $seller_id ) {
				$order_approval_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_order_approval', true );
				$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

				if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
					continue;
				}

				$data = array(
					'type'      => 'order',
					'author_id' => $seller_id,
					'context'   => $order_id,
					'content'   => $content,
					'read_flag' => 0,
					'timestamp' => $now->format( 'Y-m-d H:i:s' ),
				);

				$this->db_obj->wkmp_add_new_notification( $data );
			}
		}

		/**
		 * Save on product update.
		 *
		 * @param string   $new_status New status.
		 * @param string   $old_status Old status.
		 * @param \WP_Post $post Post object.
		 *
		 * @hooked transition_post_status
		 */
		public function wkmp_save_on_product_update( $new_status, $old_status, $post ) {
			if ( 'publish' !== $old_status && 'publish' === $new_status && ! empty( $post->ID ) && in_array( $post->post_type, array( 'product' ), true ) ) {
				$product_data = get_post( $post->ID );
				$content      = 'wkmp_product_approved';
				$now          = new \DateTime( 'now' );

				$data = array(
					'type'      => 'product',
					'author_id' => $product_data->post_author,
					'context'   => $post->ID,
					'content'   => $content,
					'read_flag' => 0,
					'timestamp' => $now->format( 'Y-m-d H:i:s' ),
				);

				$this->db_obj->wkmp_add_new_notification( $data );
			}
		}

		/**
		 * Save seller review notification.
		 *
		 * @param array $data Data.
		 * @param int   $review_id Review id.
		 *
		 * @hooked wkmp_save_seller_review_notification action hook.
		 */
		public function wkmp_after_seller_review_saved( $data, $review_id ) {
			$content = 'wkmp_new_review_received_' . $review_id;
			$now     = new \DateTime( 'now' );

			$args = array(
				'type'      => 'seller',
				'author_id' => $data['seller_id'],
				'context'   => $data['user_id'],
				'content'   => $content,
				'read_flag' => 0,
				'timestamp' => $now->format( 'Y-m-d H:i:s' ),
			);
			$this->db_obj->wkmp_add_new_notification( $args );
		}

		/**
		 * Low stock.
		 *
		 * @param \WC_Product $product Product object.
		 *
		 * @hooked woocommerce_low_stock_notification
		 */
		public function wkmp_low_stock( $product ) {
			$product_data = get_post( $product->get_id() );
			$author_id    = $product_data->post_author;
			$content      = 'wkmp_low_stock_' . $product->get_stock_quantity();
			$now          = new \DateTime( 'now' );

			$data = array(
				'type'      => 'product',
				'author_id' => $author_id,
				'context'   => $product->get_id(),
				'content'   => $content,
				'read_flag' => 0,
				'timestamp' => $now->format( 'Y-m-d H:i:s' ),
			);
			$this->db_obj->wkmp_add_new_notification( $data );
		}

		/**
		 * No stock.
		 *
		 * @param \WC_Product $product Product Object.
		 */
		public function wkmp_no_stock( $product ) {
			$product_data = get_post( $product->get_id() );
			$author_id    = $product_data->post_author;
			$content      = 'wkmp_out_of_stock';
			$now          = new \DateTime( 'now' );

			$args = array(
				'type'      => 'product',
				'author_id' => $author_id,
				'context'   => $product->get_id(),
				'content'   => $content,
				'read_flag' => 0,
				'timestamp' => $now->format( 'Y-m-d H:i:s' ),
			);

			$this->db_obj->wkmp_add_new_notification( $args );
		}

		/**
		 * Order processing.
		 *
		 * @param int $order_id Order id.
		 */
		public function wkmp_order_processing_notification( $order_id ) {
			$seller_ids = array();
			$order      = wc_get_order( $order_id );
			$items      = $order->get_items();

			if ( ! empty( $items ) ) {
				foreach ( $items as $item ) {
					if ( isset( $item['product_id'] ) && $item['product_id'] > 0 ) {
						$product      = get_post( $item['product_id'] );
						$seller_ids[] = $product->post_author;
					}
				}

				if ( ! empty( $seller_ids ) ) {
					$content = 'wkmp_order_processing';

					foreach ( $seller_ids as $seller_id ) {
						$order_approval_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_order_approval', true );
						$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

						if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
							continue;
						}

						$now  = new \DateTime( 'now' );
						$data = array(
							'type'      => 'order',
							'author_id' => $seller_id,
							'context'   => $order_id,
							'content'   => $content,
							'read_flag' => 0,
							'timestamp' => $now->format( 'Y-m-d H:i:s' ),
						);

						$this->db_obj->wkmp_add_new_notification( $data );
					}
				}
			}
		}

		/**
		 * Order completed notification.
		 *
		 * @param int $order_id Order Id.
		 */
		public function wkmp_order_completed_notification( $order_id ) {
			$seller_ids = array();
			$order      = wc_get_order( $order_id );
			$items      = $order->get_items();

			if ( ! empty( $items ) ) {
				foreach ( $items as $item ) {
					if ( isset( $item['product_id'] ) && $item['product_id'] > 0 ) {
						$product      = get_post( $item['product_id'] );
						$seller_ids[] = $product->post_author;
					}
				}

				if ( ! empty( $seller_ids ) ) {
					$content = 'wkmp_order_complete';

					foreach ( $seller_ids as $seller_id ) {
						$order_approval_enabled = get_user_meta( $seller_id, '_wkmp_enable_seller_order_approval', true );
						$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $seller_id, $order_id );

						if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
							continue;
						}

						$now  = new \DateTime( 'now' );
						$data = array(
							'type'      => 'order',
							'author_id' => $seller_id,
							'context'   => $order_id,
							'content'   => $content,
							'read_flag' => 0,
							'timestamp' => $now->format( 'Y-m-d H:i:s' ),
						);
						$this->db_obj->wkmp_add_new_notification( $data );
					}
				}
			}
		}

		/**
		 * Seller Information.
		 *
		 * @param \WC_Order $order Order Object.
		 */
		public function wkmp_send_mail_to_inform_seller( $order ) {
			global $wkmarketplace;
			$items    = $order->get_items();
			$order_id = $order->get_id();
			$sellers  = array();

			foreach ( $items as $item ) {
				$item_id   = $item['product_id'];
				$author_id = get_post_field( 'post_author', $item_id );
				$is_seller = $wkmarketplace->wkmp_user_is_seller( $author_id );

				if ( ! $is_seller ) {
					continue;
				}

				$order_approval_enabled = get_user_meta( $author_id, '_wkmp_enable_seller_order_approval', true );
				$paid_status            = $this->order_db_obj->wkmp_get_order_pay_status( $author_id, $order_id );

				if ( $order_approval_enabled && ! in_array( $paid_status, array( 'approved', 'paid' ), true ) ) {
					continue;
				}

				$send_to               = $this->db_obj->wkmp_get_author_email_by_item_id( $item_id );
				$sellers[ $send_to ][] = $item;
			}

			foreach ( $sellers as $email => $items ) {
				do_action( 'wkmp_seller_product_ordered', $order_id, $items, $email );
			}
		}
	}
}
