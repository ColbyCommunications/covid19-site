<?php
/**
 * Polylang compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Polylang_Package' ) ) {

	class Jet_Popup_Polylang_Package {

		public function __construct() {
			add_filter( 'jet-popup/get_conditions/template_id', array( $this, 'set_translated_popup' ) );
		}

		/**
		 * Set translated popup ID to show
		 *
		 * @param int|string $popup_id Popup ID
		 *
		 * @return false|int|null
		 */
		public function set_translated_popup( $popup_id ) {

			if ( function_exists( 'pll_get_post' ) ) {

				$translation_popup_id = pll_get_post( $popup_id );

				if ( null === $translation_popup_id ) {
					// the current language is not defined yet
					return $popup_id;
				} elseif ( false === $translation_popup_id ) {
					//no translation yet
					return $popup_id;
				} elseif ( $translation_popup_id > 0 ) {
					// return translated post id
					return $translation_popup_id;
				}
			}

			return $popup_id;
		}

	}

}

new Jet_Popup_Polylang_Package();
