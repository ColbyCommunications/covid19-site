<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Not Supported Document
 */
class Jet_Popup_Not_Supported extends \Elementor\Modules\Library\Documents\Not_Supported {

	/**
	 * Get document properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @access public
	 * @static
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['cpt'] = array(
			jet_popup()->post_type->slug(),
		);

		return $properties;
	}

	/**
	 * Get document name.
	 *
	 * Retrieve the document name.
	 *
	 * @access public
	 *
	 * @return string Document name.
	 */
	public function get_name() {
		return 'jet-popup-not-supported';
	}

	public function save_template_type() {
		// Do nothing.
	}
}
