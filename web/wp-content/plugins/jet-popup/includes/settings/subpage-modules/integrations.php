<?php
namespace Jet_Popup\Settings;

use Jet_Dashboard\Base\Page_Module as Page_Module_Base;
use Jet_Dashboard\Dashboard as Dashboard;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Integrations extends Page_Module_Base {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_page_slug() {
		return 'jet-popup-integrations';
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
		return esc_html__( 'Integrations', 'jet-popup' );
	}

	/**
	 * [get_category description]
	 * @return [type] [description]
	 */
	public function get_category() {
		return 'jet-popup-settings';
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
			'jet-popup-admin',
			jet_popup()->plugin_url( 'assets/css/jet-popup-admin.css' ),
			[],
			jet_popup()->get_version()
		);

		wp_enqueue_script(
			'jet-popup-admin-vue-components',
			jet_popup()->plugin_url( 'assets/js/admin-vue-components.js' ),
			array( 'jquery' ),
			jet_popup()->get_version(),
			true
		);

		wp_localize_script(
			'jet-popup-admin-vue-components', 'jetPopupSettingsConfig', jet_popup()->settings->get_settings_page_config()
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

		$templates['mailchimp-list-item']   = jet_popup()->plugin_path( 'templates/vue-templates/admin/mailchimp-list-item.php' );
		$templates['jet-popup-integrations'] = jet_popup()->plugin_path( 'templates/vue-templates/admin/settings-templates/integrations.php' );

		return $templates;
	}
}
