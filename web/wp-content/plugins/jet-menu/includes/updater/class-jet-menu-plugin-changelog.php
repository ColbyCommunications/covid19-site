<?php
/**
 * Plugin Changelog class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define plugin changelog class.
 *
 * @since 1.0.0
 */
class Jet_Menu_Plugin_Changelog {

	private $transient_key = null;

	protected $api = array(
		'name'     => '',
		'slug'     => '',
		'version'  => '',
		'author'   => '',
		'homepage' => '',
		'requires' => '4.7',
		'tested'   => '',
		'banners'  => array(),
		'api_url'  => 'https://crocoblock.com/wp-content/uploads/jet-changelog/%s.json',
	);

	/**
	 * Init.
	 *
	 * @since  1.0.0
	 * @param  array $attr Input attributes array.
	 * @return void
	 */
	public function init( $attr = array() ) {

		$this->api = wp_parse_args( $attr, $this->api );

		$this->transient_key = $this->api['slug'] . '_plugin_info_data';

		add_filter( 'plugins_api',                           array( $this, 'plugins_api_filter' ), 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'delete_transients' ),  50 );

		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 3 );
	}

	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		if ( 'plugin_information' !== $_action ) {
			return $_data;
		}

		if ( ! isset( $_args->slug ) || ( $_args->slug !== $this->api['slug'] ) ) {
			return $_data;
		}

		$cache_key = $this->transient_key;

		$api_request_transient = get_site_transient( $cache_key );

		if ( empty( $api_request_transient ) ) {
			$api_response = $this->remote_query();

			if ( ! $api_response ) {
				return $_data;
			}

			$api_request_transient = new stdClass();

			$api_request_transient->name     = $this->api['name'];
			$api_request_transient->slug     = $this->api['slug'];
			$api_request_transient->author   = $this->api['author'];
			$api_request_transient->homepage = $this->api['homepage'];
			$api_request_transient->requires = $this->api['requires'];
			$api_request_transient->tested   = $this->api['tested'];
			$api_request_transient->banners  = $this->api['banners'];

			$api_request_transient->version  = $api_response->current_version;
			$api_request_transient->sections = array(
				'changelog' => $api_response->changelog,
			);

			// Expires in 1 day
			set_site_transient( $cache_key, $api_request_transient, DAY_IN_SECONDS );
		}

		$_data = $api_request_transient;

		return $_data;
	}

	public function delete_transients( $data ) {

		$plugin = $this->api['slug'] . '/' . $this->api['slug'] . '.php';

		if ( isset( $data->response[ $plugin ]->new_version ) && version_compare( $this->api['version'], $data->response[ $plugin ]->new_version, '<' ) ) {
			delete_site_transient( $this->transient_key );
		}

		return $data;
	}

	protected function remote_query() {

		$response = wp_remote_get( sprintf( $this->api['api_url'], $this->api['slug'] ) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' ) {
			return false;
		}

		$response = json_decode( $response['body'] );

		return $response;
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data ) {

		$plugin = $this->api['slug'] . '/' . $this->api['slug'] . '.php';

		if ( $plugin === $plugin_file && empty( $plugin_data['update'] ) ) {

			$plugin_meta['view-details'] = sprintf( '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
				esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->api['slug'] . '&TB_iframe=true&width=600&height=550' ) ),
				esc_attr( sprintf( __( 'More information about %s', 'jet-menu' ), $this->api['name'] ) ),
				esc_attr( $this->api['name'] ),
				esc_html__( 'View details', 'jet-menu' )
			);

		}

		return $plugin_meta;
	}

}

if ( ! function_exists( 'jet_menu_plugin_changelog' ) ) {
	function jet_menu_plugin_changelog() {
		return new Jet_Menu_Plugin_Changelog();
	}
}
