<?php
namespace Jet_Dashboard\Modules\Jet_Theme_Core;

use Jet_Dashboard\Base\Module as Module_Base;
use Jet_Dashboard\Dashboard as Dashboard;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Module extends Module_Base {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_slug() {
		return 'jet-theme-core';
	}

	/**
	 * Enqueue module-specific assets
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {

		wp_enqueue_script(
			'jet-dashboard-jet-theme-core',
			jet_theme_core()->plugin_url( 'includes/jet-dashboard-pages/modules/jet-theme-core/js/jet-theme-core.js' ),
			array( 'cx-vue-ui' ),
			jet_theme_core()->get_version(),
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
	public function page_config( $config = array(), $subpage = '' ) {

		$config['headerTitle'] = esc_attr__( 'Jet Theme Core', 'jet-theme-core' );
		$config['page']        = 'jet-theme-core';
		$config['wrapperCss']  = 'jet-theme-core';

		return $config;
	}

	/**
	 * Add welcome component template
	 *
	 * @param  array  $templates [description]
	 * @param  string $subpage   [description]
	 * @return [type]            [description]
	 */
	public function page_templates( $templates = array(), $subpage = '' ) {

		$templates['jet-theme-core'] = jet_theme_core()->plugin_path( 'includes/jet-dashboard-pages/modules/jet-theme-core/view/main.php' );

		return $templates;
	}
}
