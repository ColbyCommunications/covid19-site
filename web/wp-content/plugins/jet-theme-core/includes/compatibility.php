<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Theme_Core_Compatibility' ) ) {

	/**
	 * Define Jet_Theme_Core_Compatibility class
	 */
	class Jet_Theme_Core_Compatibility {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			// WPML compatibility
			if ( defined( 'WPML_ST_VERSION' ) ) {
				add_filter( 'jet-theme-core/get_location_templates/template_id', array( $this, 'set_wpml_translated_location_id' ) );
			}

			// Polylang compatibility
			if ( class_exists( 'Polylang' ) ) {
				add_filter( 'jet-theme-core/get_location_templates/template_id', array( $this, 'set_pll_translated_location_id' ) );
			}
		}

		/**
		 * Set WPML translated location.
		 *
		 * @param $post_id
		 *
		 * @return mixed|void
		 */
		public function set_wpml_translated_location_id( $post_id ) {
			$location_type = get_post_type( $post_id );

			return apply_filters( 'wpml_object_id', $post_id, $location_type, true );
		}

		/**
		 * set_pll_translated_location_id
		 *
		 * @param $post_id
		 *
		 * @return false|int|null
		 */
		public function set_pll_translated_location_id( $post_id ) {

			if ( function_exists( 'pll_get_post' ) ) {

				$translation_post_id = pll_get_post( $post_id );

				if ( null === $translation_post_id ) {
					// the current language is not defined yet
					return $post_id;
				} elseif ( false === $translation_post_id ) {
					//no translation yet
					return $post_id;
				} elseif ( $translation_post_id > 0 ) {
					// return translated post id
					return $translation_post_id;
				}
			}

			return $post_id;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
