<?php
namespace Jet_Theme_Core_Dashboard;

/**
 * API controller class
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Define Controller class
 */
class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	// Here initialize our namespace and resource name.
	public function __construct() {
		//add_action( 'admin_menu', array( $this, 'register_page' ), 99 );

		add_filter( 'jet-dashboard/page-modules', array( $this, 'add_page_modules' ) );
	}

	/**
	 * [add_page_modules description]
	 * @param [type] $modules [description]
	 */
	public function add_page_modules( $modules ) {

		$modules['jet-theme-core'] = '\\Jet_Dashboard\\Modules\\Jet_Theme_Core\\Module';

		return $modules;
	}

	/**
	 * Register add/edit page
	 *
	 * @return void
	 */
	public function register_page() {

		add_submenu_page(
			'jet-dashboard',
			esc_html__( 'JetThemeCore', 'jet-theme-core' ),
			esc_html__( 'JetThemeCore', 'jet-theme-core' ),
			'manage_options',
			\Jet_Dashboard\Dashboard::get_instance()->get_dashboard_page_url( 'jet-theme-core' )
		);
	}
}

