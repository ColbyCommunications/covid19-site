<?php
namespace Jet_Theme_Core\Settings;

use Jet_Dashboard\Base\Page_Module as Page_Module_Base;
use Jet_Dashboard\Dashboard as Dashboard;
use Jet_Theme_Core\Settings as Settings_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Kava_Theme extends Page_Module_Base {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_page_slug() {
		return 'jet-theme-core-kava-theme';
	}

	/**
	 * [get_subpage_slug description]
	 * @return [type] [description]
	 */
	public function get_parent_slug() {
		return 'settings-page';
	}

	/**
	 * [get_page_name description]
	 * @return [type] [description]
	 */
	public function get_page_name() {
		return esc_html__( 'Kava Theme', 'jet-theme-core' );
	}

	/**
	 * [get_category description]
	 * @return [type] [description]
	 */
	public function get_category() {
		return 'jet-theme-core-settings';
	}

	/**
	 * [get_page_link description]
	 * @return [type] [description]
	 */
	public function get_page_link() {
		return Dashboard::get_instance()->get_dashboard_page_url( $this->get_parent_slug(), $this->get_page_slug() );
	}

	/**
	 * Enqueue module-specific assets
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {

		wp_enqueue_style(
			'jet-theme-core-admin-css',
			jet_theme_core()->plugin_url( 'assets/css/admin.css' ),
			false,
			jet_theme_core()->get_version()
		);

		wp_enqueue_script(
			'jet-theme-core-admin-vue-components',
			jet_theme_core()->plugin_url( 'assets/js/admin-vue-components.js' ),
			array( 'cx-vue-ui' ),
			jet_theme_core()->get_version(),
			true
		);

		$settings_config_data = Settings_Manager::get_instance()->get_settings_config_data();

		wp_localize_script(
			'jet-theme-core-admin-vue-components',
			'jetThemeCoreSettingsConfig',
			$settings_config_data
		);

	}

	/**
	 * License page config
	 *
	 * @param  array  $config  [description]
	 * @param  string $subpage [description]
	 * @return [type]          [description]
	 */
	public function page_config( $config = array(), $page = false, $subpage = false ) {

		$config['pageModule'] = $this->get_parent_slug();
		$config['subPageModule'] = $this->get_page_slug();

		return $config;
	}

	/**
	 * [page_templates description]
	 * @param  array  $templates [description]
	 * @param  string $subpage   [description]
	 * @return [type]            [description]
	 */
	public function page_templates( $templates = array(), $page = false, $subpage = false ) {

		$templates['jet-theme-core-kava-theme'] = jet_theme_core()->plugin_path( 'templates/admin/setting-templates/kava-theme.php' );

		return $templates;
	}
}
