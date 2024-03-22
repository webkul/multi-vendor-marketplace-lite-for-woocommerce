<?php
/**
 * Seller Category filter class.
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Product;

defined( 'ABSPATH' ) || exit; // Exit if access directly.

if ( ! class_exists( 'WKMP_Category_Filter' ) ) {
	/**
	 * Seller Add / Edit Product class
	 */
	class WKMP_Category_Filter extends \Walker {
		/**
		 * Walker type.
		 *
		 * @var string
		 */
		public $tree_type = 'category';

		/**
		 * Database fields.
		 *
		 * @var array
		 */
		public $db_fields = array(
			'parent' => 'parent',
			'id'     => 'term_id',
		);

		/**
		 * Constructor of the class.
		 *
		 * @param array $allowed_cat allowed cat.
		 */
		public function __construct( $allowed_cat = array() ) {
			$this->allowed_categories = $allowed_cat;
		}

		/**
		 * Start element.
		 *
		 * @param string $output Output.
		 * @param object $category Category.
		 * @param int    $depth Depth.
		 * @param array  $args Args.
		 * @param int    $id Id.
		 */
		public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			$pad      = str_repeat( '&nbsp;', $depth * 3 );
			$cat_name = apply_filters( 'list_cats', $category->name, $category );
			if ( $this->allowed_categories ) {
				if ( in_array( $category->slug, $this->allowed_categories, true ) ) {
					$output .= "\t<option class=\"level-$depth\" value=\"" . $category->slug . '"';

					if ( in_array( $category->slug, $args['selected'], true ) ) {
						$output .= ' selected="selected"';
					}

					$output .= '>';
					$output .= $pad . $cat_name;
					$output .= "</option>\n";
				}
			} else {
				$output .= "\t<option class=\"level-$depth\" value=\"" . $category->slug . '"';
				if ( in_array( $category->slug, $args['selected'], true ) ) {
					$output .= ' selected="selected"';
				}
				$output .= '>';
				$output .= $pad . $cat_name;
				$output .= "</option>\n";
			}
		}

		/**
		 * Display Element.
		 *
		 * @param object $element Element.
		 * @param array  $children_elements Children Elements.
		 * @param int    $max_depth Max depth.
		 * @param int    $depth Depth.
		 * @param array  $args Arguments.
		 * @param string $output Output.
		 */
		public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
			if ( ! $element ) {
				return;
			}

			$id_field = ( isset( $this->db_fields['id'] ) ) ? $this->db_fields['id'] : '';
			$id       = $element->$id_field;

			// Display this element.
			$this->has_children = ! empty( $children_elements[ $id ] );
			if ( isset( $args[0] ) && is_array( $args[0] ) ) {
				$args[0]['has_children'] = $this->has_children; // Back-compat.
			}

			$this->start_el( $output, $element, $depth, ...array_values( $args ) );

			// Descend only when the depth is right and there are children for this element.
			if ( ( 0 === $max_depth || $max_depth > $depth + 1 ) && isset( $children_elements[ $id ] ) ) {
				foreach ( $children_elements[ $id ] as $child ) {
					if ( ! isset( $newlevel ) ) {
						$newlevel = true;
						// Start the child delimiter.
						$this->start_lvl( $output, $depth, ...array_values( $args ) );
					}
					$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
				}
				unset( $children_elements[ $id ] );
			}

			if ( isset( $newlevel ) && $newlevel ) {
				// End the child delimiter.
				$this->end_lvl( $output, $depth, ...array_values( $args ) );
			}

			// End this element.
			$this->end_el( $output, $element, $depth, ...array_values( $args ) );
		}

		/**
		 * Walk function.
		 *
		 * @param array $elements Elements.
		 * @param int   $max_depth Max depth.
		 * @param mixed ...$args Arguments.
		 *
		 * @return string
		 */
		public function walk( $elements, $max_depth, ...$args ) {
			$output = '';

			// Invalid parameter or nothing to walk.
			if ( $max_depth < - 1 || empty( $elements ) ) {
				return $output;
			}

			$parent_field = ( isset( $this->db_fields['parent'] ) ) ? $this->db_fields['parent'] : '';

			// Flat display.
			if ( - 1 === $max_depth ) {
				$empty_array = array();
				foreach ( $elements as $e ) {
					$this->display_element( $e, $empty_array, 1, 0, $args, $output );
				}

				return $output;
			}

			/*
			 * Need to display in hierarchical order.
			 * Separate elements into two buckets: top level and children elements.
			 * Children_elements is two dimensional array, eg.
			 * Children_elements[10][] contains all sub-elements whose parent is 10.
			 */
			$top_level_elements = array();
			$children_elements  = array();
			foreach ( $elements as $e ) {
				if ( empty( $e->$parent_field ) ) {
					$top_level_elements[] = $e;
				} else {
					$children_elements[ $e->$parent_field ][] = $e;
				}
			}

			/*
			 * When none of the elements is top level.
			 * Assume the first one must be root of the sub elements.
			 */
			if ( empty( $top_level_elements ) ) {
				$first = array_slice( $elements, 0, 1 );
				$root  = $first[0];

				$top_level_elements = array();
				$children_elements  = array();
				foreach ( $elements as $e ) {
					if ( $root->$parent_field === $e->$parent_field ) {
						$top_level_elements[] = $e;
					} else {
						$children_elements[ $e->$parent_field ][] = $e;
					}
				}
			}

			foreach ( $top_level_elements as $e ) {
				$this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );
			}

			/*
			 * If we are displaying all levels, and remaining children_elements is not empty,
			 * then we got orphans, which should be displayed regardless.
			 */
			if ( ( $max_depth < 1 ) && count( $children_elements ) > 0 ) {
				$empty_array = array();
				foreach ( $children_elements as $orphans ) {
					foreach ( $orphans as $op ) {
						$this->display_element( $op, $empty_array, 1, 0, $args, $output );
					}
				}
			}

			return $output;
		}
	}
}
