<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Jet_Theme_Core_Not_Supported extends Elementor\Modules\Library\Documents\Not_Supported {

	public function get_name() {
		return 'jet-theme-core-not-supported';
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['cpt'] = array( jet_theme_core()->templates->post_type );

		return $properties;
	}

	public function save_template_type() {
		// Do nothing.
	}
}
