<?php
/**
 * Is front page condition
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Conditions_Advanced_Roles' ) ) {

	class Jet_Popup_Conditions_Advanced_Roles extends Jet_Popup_Conditions_Base {

		/**
		 * Condition slug
		 *
		 * @return string
		 */
		public function get_id() {
			return 'roles';
		}

		/**
		 * Condition label
		 *
		 * @return string
		 */
		public function get_label() {
			return __( 'Roles', 'jet-popup' );
		}

		/**
		 * Condition group
		 *
		 * @return string
		 */
		public function get_group() {
			return 'advanced';
		}

		/**
		 * [get_control description]
		 * @return [type] [description]
		 */
		public function get_control() {
			return [
				'type'        => 'f-select',
				'placeholder' => __( 'Select roles', 'jet-popup' ),
			];
		}

		/**
		 * [get_avaliable_options description]
		 * @return [type] [description]
		 */
		public function get_avaliable_options() {

			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$options = [];

			foreach ( get_editable_roles() as $role_slug => $role_data ) {
				$options[] = [
					'label' => $role_data['name'],
					'value' => $role_slug,
				];
			}

			if ( ! empty( $options ) ) {
				$options[] = [
					'label' => esc_html__( 'Guest', 'jet-popup' ),
					'value' => 'guest',
				];
			}

			return $options;
		}

		/**
		 * [ajax_action description]
		 * @return [type] [description]
		 */
		public function ajax_action() {
			return false;
		}

		/**
		 * [get_label_by_value description]
		 * @param  string $value [description]
		 * @return [type]        [description]
		 */
		public function get_label_by_value( $value = '' ) {

			$roles_string = '';

			if ( ! empty( $value ) && is_array( $value ) ) {
				$roles_string = implode( ', ', $value );
			}

			return $roles_string;
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

			if ( Jet_Popup_Utils::is_avaliable_for_user( $arg ) ) {
				return true;
			}

			return false;
		}

	}

}
