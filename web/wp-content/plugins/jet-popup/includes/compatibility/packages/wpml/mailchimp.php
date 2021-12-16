<?php

/**
 * Class Jet_Popup_WPML_Mailchimp
 */
class Jet_Popup_WPML_Mailchimp extends WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'additional_fields';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'field_merge_label', 'field_merge_placeholder' );
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {
			case 'field_merge_label':
				return esc_html__( 'Mailchimp: Field Label', 'jet-popup' );

			case 'field_merge_placeholder':
				return esc_html__( 'Mailchimp: Field Placeholder', 'jet-popup' );

			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch( $field ) {
			case 'field_merge_label':
			case 'field_merge_placeholder':
				return 'LINE';

			default:
				return '';
		}
	}

}
