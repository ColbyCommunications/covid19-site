<?php
/**
 * Is front page condition
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Conditions_Singular_Page_Child' ) ) {

	/**
	 * Define Jet_Popup_Conditions_Singular_Page_Child class
	 */
	class Jet_Popup_Conditions_Singular_Page_Child extends Jet_Popup_Conditions_Base {

		/**
		 * Condition slug
		 *
		 * @return string
		 */
		public function get_id() {
			return 'singular-page-child';
		}

		/**
		 * Condition label
		 *
		 * @return string
		 */
		public function get_label() {
			return __( 'Page, Child of', 'jet-popup' );
		}

		/**
		 * Condition group
		 *
		 * @return string
		 */
		public function get_group() {
			return 'singular';
		}

		/**
		 * [get_control description]
		 * @return [type] [description]
		 */
		public function get_control() {
			return [
				'type'        => 'select',
				'placeholder' => __( 'Select page', 'jet-popup' ),
			];
		}

		/**
		 * [ajax_action description]
		 * @return [type] [description]
		 */
		public function ajax_action() {
			return 'jet_popup_search_pages';
		}

		/**
		 * [get_label_by_value description]
		 * @param  string $value [description]
		 * @return [type]        [description]
		 */
		public function get_label_by_value( $value = '' ) {

			return get_the_title( $value );
		}

		/**
		 * Condition check callback
		 *
		 * @return bool
		 */
		public function check( $arg = '' ) {

			if ( empty( $arg ) ) {
				return false;
			}

			if ( ! is_page() ) {
				return false;
			}

			global $post;

			$parent = intval( $arg );

			return $post->post_parent === $parent;
		}

	}

}
