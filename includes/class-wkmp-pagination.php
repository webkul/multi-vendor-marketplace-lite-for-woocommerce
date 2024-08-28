<?php
/**
 * Marketplace Pagination class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Includes;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Pagination' ) ) {
	/**
	 * Pagination class
	 */
	class WKMP_Pagination {
		/**
		 * Total pages.
		 *
		 * @var int $total Total.
		 */
		public $total = 0;

		/**
		 * Page number.
		 *
		 * @var int $page Page.
		 */
		public $page = 1;

		/**
		 * Limit.
		 *
		 * @var int $limit Limit.
		 */
		public $limit = 20;

		/**
		 * Num Links.
		 *
		 * @var int $num_links Num links.
		 */
		public $num_links = 3;

		/**
		 * URL.
		 *
		 * @var string URL.
		 */
		public $url = '';

		/**
		 * Text first.
		 *
		 * @var string Text first.
		 */
		public $text_first = '|&lt;';

		/**
		 * Text last.
		 *
		 * @var string Test last.
		 */
		public $text_last = '&gt;|';

		/**
		 * Text next.
		 *
		 * @var string Text next.
		 */
		public $text_next = '&gt;';

		/**
		 * Text previous.
		 *
		 * @var string Text previous.
		 */
		public $text_prev = '&lt;';

		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

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
		 * Render.
		 *
		 * @return string
		 */
		public function wkmp_render() {
			$total = $this->total;

			$page  = ( $this->page < 1 ) ? 1 : intval( $this->page );
			$limit = empty( $this->limit ) ? 10 : intval( $this->limit );

			$num_links = intval( $this->num_links );
			$num_pages = ceil( $total / $limit );

			$this->url = str_replace( '%7Bpage%7D', '{page}', $this->url );

			$output  = '<nav class="woocommerce-pagination">';
			$output .= '<ul class="page-numbers">';

			if ( $page > 1 ) {
				$output .= '<li><a class="page-numbers" href="' . str_replace( array( '/page/{page}', 'page/{page}' ), '', $this->url ) . '">' . $this->text_first . '</a></li>';

				if ( 1 === ( $page - 1 ) ) {
					$output .= '<li><a class="prev page-numbers" href="' . str_replace( array( '/page/{page}', 'page/{page}' ), '', $this->url ) . '">' . $this->text_prev . '</a></li>';
				} else {
					$output .= '<li><a class="prev page-numbers" href="' . str_replace( '{page}', $page - 1, $this->url ) . '">' . $this->text_prev . '</a></li>';
				}
			}

			if ( $num_pages > 1 ) {
				if ( $num_pages <= $num_links ) {
					$start = 1;
					$end   = $num_pages;
				} else {
					$start = $page - floor( $num_links / 2 );
					$end   = $page + floor( $num_links / 2 );

					if ( $start < 1 ) {
						$end  += abs( $start ) + 1;
						$start = 1;
					}

					if ( $end > $num_pages ) {
						$start -= ( $end - $num_pages );
						$end    = $num_pages;
					}
				}

				for ( $i = $start; $i <= $end; $i++ ) {
					if ( absint( $i ) === absint( $page ) ) {
						$output .= '<li><span aria-current="page" class="page-numbers current">' . $i . '</span></li>';
					} elseif ( 1 === $i ) {
						$output .= '<li><a class="page-numbers" href="' . str_replace( array( '/page/{page}', 'page/{page}' ), '', $this->url ) . '">' . $i . '</a></li>';
					} else {
						$output .= '<li><a class="page-numbers" href="' . str_replace( '{page}', $i, $this->url ) . '">' . $i . '</a></li>';
					}
				}
			}

			if ( $page < $num_pages ) {
				$output .= '<li><a class="next page-numbers" href="' . str_replace( '{page}', $page + 1, $this->url ) . '">' . $this->text_next . '</a></li>';
				$output .= '<li><a class="page-numbers" href="' . str_replace( '{page}', $num_pages, $this->url ) . '">' . $this->text_last . '</a></li>';
			}

			$output .= '</ul>';
			$output .= '</nav>';

			return ( $num_pages > 1 ) ? $output : '';
		}
	}
}
