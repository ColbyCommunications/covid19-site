<?php
namespace Jet_Dashboard\Modules\License;

use Jet_Dashboard\Base\Page_Module as Page_Module_Base;
use Jet_Dashboard\Dashboard as Dashboard;
use Jet_Dashboard\Utils as Utils;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Module extends Page_Module_Base {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_page_slug() {
		return 'license-page';
	}

	/**
	 * [get_subpage_slug description]
	 * @return [type] [description]
	 */
	public function get_parent_slug() {
		return false;
	}

	/**
	 * [get_page_name description]
	 * @return [type] [description]
	 */
	public function get_page_name() {
		return esc_html__( 'Plugin Manager', 'jet-dashboard' );
	}

	/**
	 * [get_category description]
	 * @return [type] [description]
	 */
	public function get_category() {
		return false;
	}

	/**
	 * [get_page_link description]
	 * @return [type] [description]
	 */
	public function get_page_link() {
		return Dashboard::get_instance()->get_dashboard_page_url( $this->get_page_slug(), $this->get_parent_slug() );
	}

	/**
	 * Enqueue module-specific assets
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {
		wp_enqueue_script(
			'jet-dashboard-license-page',
			Dashboard::get_instance()->get_dashboard_url() . 'assets/js/license-page.js',
			array( 'cx-vue-ui' ),
			Dashboard::get_instance()->get_dashboard_version(),
			true
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

		$config['pageModule']    = $this->get_page_slug();
		$config['allJetPlugins'] = Dashboard::get_instance()->plugin_manager->get_plugin_data_list();

		return $config;
	}

	/**
	 * [page_templates description]
	 * @param  array  $templates [description]
	 * @param  string $subpage   [description]
	 * @return [type]            [description]
	 */
	public function page_templates( $templates = array(), $page = false, $subpage = false ) {

		$templates['license-page']          = Dashboard::get_instance()->get_view( 'license/main' );
		$templates['license-item']          = Dashboard::get_instance()->get_view( 'license/license-item' );
		$templates['plugin-item-installed'] = Dashboard::get_instance()->get_view( 'license/plugin-item-installed' );
		$templates['plugin-item-avaliable'] = Dashboard::get_instance()->get_view( 'license/plugin-item-avaliable' );
		$templates['plugin-item-more']      = Dashboard::get_instance()->get_view( 'license/plugin-item-more' );
		$templates['responce-info']         = Dashboard::get_instance()->get_view( 'license/responce-info' );

		return $templates;
	}
}
