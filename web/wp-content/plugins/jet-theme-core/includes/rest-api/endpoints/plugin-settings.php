<?php
namespace Jet_Theme_Core\Endpoints;

use Jet_Theme_Core\Settings as Settings_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Posts class
 */
class Plugin_Settings extends Base {

	/**
	 * [get_method description]
	 * @return [type] [description]
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'plugin-settings';
	}

	/**
	 * [callback description]
	 * @param  [type]   $request [description]
	 * @return function          [description]
	 */
	public function callback( $request ) {

		$data = $request->get_params();

		$current = get_option( jet_theme_core()->settings->option_slug, array() );

		if ( is_wp_error( $current ) ) {
			return rest_ensure_response( array(
				'status'  => 'error',
				'message' => __( 'Server Error', 'jet-theme-core' ),
			) );
		}

		foreach ( $data as $key => $value ) {
			$current[ $key ] = is_array( $value ) ? $value : esc_attr( $value );
		}

		update_option( jet_theme_core()->settings->option_slug, $current );

		return rest_ensure_response( [
			'status'  => 'success',
			'message' => __( 'Settings have been saved', 'jet-theme-core' ),
		] );
	}

}
