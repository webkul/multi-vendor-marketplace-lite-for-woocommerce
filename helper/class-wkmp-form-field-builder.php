<?php
/**
 * Form fields Trait
 *
 * @package Multi-Vendor Marketplace Lite for WooCommerce
 *
 * @since 5.0.0
 */

namespace WkMarketplace\Helper;

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

use WkMarketplace\Templates\Admin as AdminTemplates;

if ( ! class_exists( 'WKMP_Form_Field_Builder' ) ) {
	/**
	 * Form field builder class
	 *
	 * Class WKMP_Form_Field_Builder
	 *
	 * @package WkMarketplace\Helper
	 */
	class WKMP_Form_Field_Builder {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * WKMP_Form_Field_Builder constructor.
		 */
		public function __construct() {
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
		 * Form field generator
		 *
		 * @param array $fields Fields data.
		 *
		 * @return void
		 */
		public function wkmp_form_field_builder( $fields ) {
			$fields = apply_filters( 'wkmp_form_fields', $fields );
			$fields = is_array( $fields ) ? $fields : array();

			foreach ( $fields as $fieldset_key => $fieldset ) {
				?>
				<table class="form-table" id="<?php echo esc_attr( 'fieldset-' . $fieldset_key ); ?>">
					<?php
					foreach ( $fieldset['fields'] as $field_key => $field_data ) {
						$field_type = isset( $field_data['type'] ) ? $field_data['type'] : '';
						switch ( $field_type ) {
							case 'section_start':
								$this->section_start( $field_data );
								break;

							case 'select':
								$this->select( $field_data, $field_key );
								break;

							case 'multi-select':
								$this->multi_select( $field_data, $field_key );
								break;

							case 'checkbox':
								$this->checkbox( $field_data, $field_key );
								break;

							case 'textarea':
								$this->textarea( $field_data, $field_key );
								break;

							default:
								$this->input( $field_data, $field_key );
								break;
						}
					}
					?>
				</table>
				<?php
			}
		}

		/**
		 * Section start filed.
		 *
		 * @param array $field Field data.
		 */
		public function section_start( $field ) {
			?>
			<tr class="wkmp-section">
				<td colspan="2">
					<hr/>
				</td>
			</tr>
			<tr class="wkmp-section">
				<td colspan="2"><h2><?php echo esc_html( $field['title'] ); ?></h2></td>
			</tr>
			<?php
			if ( ! empty( $field['description'] ) ) {
				?>
				<tr>
					<td colspan="2"><p class="description"><?php echo esc_html( $field['description'] ); ?></p></td>
				</tr>
				<?php
			}
		}

		/**
		 * Multi Select filed.
		 *
		 * @param array  $field Field data.
		 * @param string $f_key Field key.
		 */
		public function multi_select( $field, $f_key ) {
			$this->required_description( $field );
			?>
			<select name="<?php echo esc_attr( $f_key ); ?>[]" multiple="true" id="<?php echo esc_attr( $f_key ); ?>" data-placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>" class="wkmp-select2 regular-text <?php echo ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : ''; ?>">
				<?php foreach ( $field['options'] as $key => $value ) : ?>
					<?php if ( $field['value'] ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array( $key, $field['value'], true ) ); ?>><?php echo esc_html( $value ); ?></option>
					<?php else : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
			</td>
			</tr>
			<?php
		}

		/**
		 * Select filed.
		 *
		 * @param array  $field Field data.
		 * @param string $f_key Field key.
		 */
		public function select( $field, $f_key ) {
			$this->required_description( $field );
			?>
			<select name="<?php echo esc_attr( $f_key ); ?>" id="<?php echo esc_attr( $f_key ); ?>" class="regular-text <?php echo ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : ''; ?>">
				<?php
				$selected = ! empty( $field['value'] ) ? $field['value'] : '';
				foreach ( $field['options'] as $option_key => $option_value ) :
					?>
					<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_html( $option_value ); ?></option>
					<?php
				endforeach;
				?>
			</select>
			</td>
			</tr>
			<?php
		}

		/**
		 * Checkbox filed.
		 *
		 * @param array  $field Field data.
		 * @param string $f_key Field key.
		 */
		public function checkbox( $field, $f_key ) {
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$this->required_description( $field );
			?>
			<input type="checkbox" name="<?php echo esc_attr( $f_key ); ?>" id="<?php echo esc_attr( $f_key ); ?>" value="1" class="regular-text <?php echo ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : ''; ?>" <?php checked( $field['value'], 1, true ); ?> <?php echo empty( $field['show_lock'] ) ? '' : 'onclick="return false"'; ?> />
			<?php empty( $field['show_lock'] ) ? '' : $template_functions->wkmp_show_upgrade_lock_icon(); ?>
			</td>
			</tr>
			<?php
		}

		/**
		 * Text Area filed.
		 *
		 * @param array  $field Field data.
		 * @param string $f_key Field key.
		 */
		public function textarea( $field, $f_key ) {
			$this->required_description( $field );
			?>
			<textarea name="<?php echo esc_attr( $f_key ); ?>" rows="4" class="regular-text wkmp-textarea <?php echo empty( $field['class'] ) ? '' : esc_attr( $field['class'] ); ?>"><?php echo empty( $field['value'] ) ? '' : esc_attr( $field['value'] ); ?></textarea>
			</td>
			</tr>
			<?php
		}

		/**
		 * Input filed.
		 *
		 * @param array  $field Field data.
		 * @param string $f_key Field key.
		 */
		public function input( $field, $f_key ) {
			$template_functions = AdminTemplates\WKMP_Admin_Template_Functions::get_instance();
			$this->required_description( $field );
			?>
			<input type="<?php echo empty( $field['type'] ) ? 'text' : esc_attr( $field['type'] ); ?>" step="<?php echo empty( $field['step'] ) ? '' : esc_attr( $field['step'] ); ?>" min="<?php echo empty( $field['min'] ) ? 0 : esc_attr( $field['min'] ); ?>" max="<?php echo empty( $field['max'] ) ? '' : esc_attr( $field['max'] ); ?>" name="<?php echo esc_attr( $f_key ); ?>" id="<?php echo esc_attr( $f_key ); ?>" <?php echo isset( $field['readonly'] ) ? esc_attr( $field['readonly'] ) : ''; ?> value="<?php echo empty( $field['value'] ) ? '' : esc_attr( $field['value'] ); ?>" class="regular-text <?php echo empty( $field['class'] ) ? '' : esc_attr( $field['class'] ); ?>"/>
			<?php empty( $field['show_lock'] ) ? '' : $template_functions->wkmp_show_upgrade_lock_icon(); ?>
			</td>
			</tr>
			<?php
		}

		/***
		 * Required description.
		 *
		 * @param array $field Field data.
		 */
		public function required_description( $field ) {
			$required = empty( $field['required'] ) ? '' : '<span class="required"> *</span>';
			?>
			<tr>
			<th>
				<label for="<?php echo esc_attr( $field['label'] ); ?>"><?php echo esc_html( $field['label'] ); ?>
					<?php
					echo wp_kses( $required, array( 'span' => array( 'class' => array() ) ) );
					?>
				</label>
			</th>
			<td>
			<?php
			if ( ! empty( $field['description'] ) ) {
				echo wp_kses(
					wc_help_tip( wp_kses_post( $field['description'] ), true ),
					array(
						'span' => array(
							'tabindex'   => array(),
							'aria-label' => array(),
							'data-tip'   => array(),
							'class'      => array(),
						),
					)
				);
			}
		}
	}
}
