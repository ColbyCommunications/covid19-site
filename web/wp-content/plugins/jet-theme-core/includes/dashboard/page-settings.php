<?php
/**
 * Settings page
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Theme_Core_Dashboard_Settings' ) ) {

	/**
	 * Define Jet_Theme_Core_Dashboard_Settings class
	 */
	class Jet_Theme_Core_Dashboard_Settings extends Jet_Theme_Core_Dashboard_Base {

		/**
		 * [$has_license description]
		 * @var null
		 */
		private $has_license = null;

		/**
		 * Page slug
		 *
		 * @return string
		 */
		public function get_slug() {
			return 'settings';
		}

		/**
		 * Get icon
		 *
		 * @return string
		 */
		public function get_icon() {
			return 'dashicons dashicons-admin-settings';
		}

		/**
		 * Page name
		 *
		 * @return string
		 */
		public function get_name() {
			return esc_attr__( 'Settings', 'jet-theme-core' );
		}

		/**
		 * Disable builder instance initialization
		 *
		 * @return bool
		 */
		public function use_builder() {
			return false;
		}

		/**
		 * Renderer callback
		 *
		 * @return void
		 */
		public function render_page() {
			$this->render_actions();

			jet_theme_core()->settings->render_page();
		}

		public function save_settings( $data ) {
			jet_theme_core()->settings->save( $data );
		}

		/**
		 * Render plugins actions
		 * @return [type] [description]
		 */
		public function render_actions() {

			if ( null === $this->has_license ) {
				$this->has_license = Jet_Theme_Core_Utils::get_theme_core_license();
			}

			if ( empty( $this->has_license ) ) {
				return;
			}

			echo '<div class="jet-plugins-actions">';

			printf(
				'<a href="%2$s" class="cx-button cx-button-success-style">%1$s</a>',
				__( 'Synchronize Templates Library', 'jet-theme-core' ),
				add_query_arg(
					array(
						'jet_action' => $this->get_slug(),
						'handle'     => 'sync_library',
					),
					admin_url( 'admin.php' )
				)
			);

			echo '</div>';

		}

		/**
		 * Clear templates Jet API cache
		 *
		 * @return void
		 */
		public function sync_library() {

			$api_source = jet_theme_core()->templates_manager->get_source( 'jet-api' );
			$redirect   = $this->get_current_page_link();

			if ( ! $api_source ) {
				wp_safe_redirect( $redirect );
				die();
			}

			$api_source->delete_templates_cache();
			$api_source->delete_categories_cache();
			$api_source->delete_keywords_cache();

			wp_safe_redirect( $redirect );
			die();

		}

	}

}
