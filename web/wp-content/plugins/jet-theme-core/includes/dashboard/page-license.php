<?php
/**
 * License page
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Theme_Core_Dashboard_License' ) ) {

	/**
	 * Define Jet_Theme_Core_Dashboard_License class
	 */
	class Jet_Theme_Core_Dashboard_License extends Jet_Theme_Core_Dashboard_Base {

		/**
		 * License key
		 *
		 * @var string
		 */
		public $license_slug = 'jet_theme_core_license';

		/**
		 * Page slug
		 *
		 * @return string
		 */
		public function get_slug() {
			return 'license';
		}

		/**
		 * Get icon
		 *
		 * @return string
		 */
		public function get_icon() {
			return 'dashicons dashicons-media-default';
		}

		/**
		 * Page name
		 *
		 * @return string
		 */
		public function get_name() {
			return esc_attr__( 'License', 'jet-theme-core' );
		}

		/**
		 * Custom initializtion
		 *
		 * @return void
		 */
		public function init() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_license_assets' ), 0 );
		}

		/**
		 * Retrieve license key
		 *
		 * @return string
		 */
		public function get_license() {
			return get_option( $this->license_slug );
		}

		/**
		 * LLicense assets
		 *
		 * @return [type] [description]
		 */
		public function enqueue_license_assets() {

			wp_enqueue_script(
				'jet-theme-core-settings',
				jet_theme_core()->plugin_url( 'assets/js/settings.js' ),
				array( 'jquery' ),
				jet_theme_core()->get_version(),
				true
			);

			wp_localize_script( 'jet-theme-core-settings', 'JetSettingsData', array(
				'messages' => array(
					'empty' => esc_html__( 'Please enter your license key.', 'jet-theme-core' ),
				),
				'activateUrl' => add_query_arg(
					array(
						'jet_action' => $this->get_slug(),
						'handle'     => 'activate_license',
						'key'        => '%license_key%',
					),
					esc_url( admin_url( 'admin.php' ) )
				),
				'deactivateUrl' => add_query_arg(
					array(
						'jet_action' => $this->get_slug(),
						'handle'     => 'deactivate_license',
					),
					esc_url( admin_url( 'admin.php' ) )
				),
			) );

		}

		/**
		 * Activate license key
		 *
		 * @return [type] [description]
		 */
		public function activate_license( $data ) {

			if ( empty( $data['key'] ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( ! jet_theme_core()->api->is_enabled() ) {
				return;
			}

			$license = esc_attr( $data['key'] );

			if ( true === jet_theme_core()->api->acitvate_license( $license ) ) {
				update_option( $this->license_slug, $license, 'no' );
				$redirect = $this->get_current_page_link();
				set_site_transient( 'update_plugins', null );
			} else {

				$redirect = add_query_arg(
					array( 'activation-error' => true ),
					$this->get_current_page_link()
				);

				set_transient(
					'jet_core_activation_errors',
					jet_theme_core()->api->get_activation_error(),
					HOUR_IN_SECONDS / 3
				);

			}

			wp_redirect( $redirect );
			die();

		}

		/**
		 * Deactivate saved license key
		 *
		 * @return [type] [description]
		 */
		public function deactivate_license() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$license = $this->get_license();

			if ( true === jet_theme_core()->api->deacitvate_license( $license ) ) {
				$redirect = $this->get_current_page_link();
				set_site_transient( 'update_plugins', null );
			} else {

				$redirect = add_query_arg(
					array( 'activation-error' => true ),
					$this->get_current_page_link()
				);

				set_transient(
					'jet_core_activation_errors',
					jet_theme_core()->api->get_activation_error(),
					HOUR_IN_SECONDS / 3
				);

			}

			delete_option( $this->license_slug );

			wp_redirect( $redirect );
			die();

		}

		/**
		 * Renderer callback
		 *
		 * @return void
		 */
		public function render_page() {

			$this->builder->register_form(
				array(
					'jet_theme_core_license_form' => array(
						'type'   => 'form',
						'action' => '',
					),
				)
			);

			$license          = $this->get_license();
			$formated_license = '';

			if ( ! $license ) {
				$action = 'activate';
			} else {
				$formated_license = substr_replace( $license, str_repeat( 'X', 16 ), 8, 16 );
				$action           = 'deactivate';
			}

			$error_message = '';

			if ( ! empty( $_GET['activation-error'] ) ) {
				$error_message = get_transient( 'jet_core_activation_errors' );
			}

			ob_start();
			include jet_theme_core()->get_template( 'dashboard/license/' . $action . '-license-control.php' );
			$license_control = ob_get_clean();

			$this->builder->register_html(
				array(
					'license' => array(
						'type'   => 'html',
						'parent' => 'jet_theme_core_license_form',
						'class'  => 'cx-component',
						'html'   => $license_control,
					),
				)
			);

			$this->builder->render();

		}

	}

}
