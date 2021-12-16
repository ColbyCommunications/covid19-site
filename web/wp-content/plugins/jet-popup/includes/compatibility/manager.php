<?php
/**
 * Compatibility class
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Compatibility' ) ) {

	/**
	 * Define Jet_Popup_Compatibility class
	 */
	class Jet_Popup_Compatibility {

		/**
		 * Constructor for the class
		 */
		function __construct() {
			add_action( 'init', array( $this, 'load_compat_packages' ) );
		}

		/**
		 * Load compatibility packages
		 *
		 * @return void
		 */
		public function load_compat_packages() {

			$whitelist = array(
				'wpml.php' => array(
					'cb'   => 'defined',
					'args' => 'WPML_ST_VERSION',
				),
				'polylang.php' => array(
					'cb'   => 'class_exists',
					'args' => 'Polylang',
				),
			);

			foreach ( $whitelist as $file => $condition ) {
				if ( true === call_user_func( $condition['cb'], $condition['args'] ) ) {
					require jet_popup()->plugin_path( 'includes/compatibility/packages/' . $file );
				}
			}

		}

	}

}
