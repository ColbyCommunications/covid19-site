<?php
/**
 * Plugin Name: JetMenu
 * Plugin URI: https://crocoblock.com/plugins/jetmenu/
 * Description: A top-notch mega menu addon for Elementor. Use it to create a fully responsive mega menu with drop-down items, rich in content modules, and change your menu style according to your vision without any coding knowledge!
 * Version:     2.0.4
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-menu
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_Menu` doesn't exists yet.
if ( ! class_exists( 'Jet_Menu' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_Menu {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of cherry framework core class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private $core = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '2.0.4';

		/**
		 * Plugin slug
		 *
		 * @var string
		 */
		public $plugin_slug = 'jet-menu';

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Dynamic CSS module instance
		 *
		 * @var object
		 */
		private $dynamic_css = null;

		/**
		 * Dirname holder for plugins integration loader
		 *
		 * @var string
		 */
		private $dir = null;

		/**
		 * Framework component
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    object
		 */
		public $module_loader = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Load the CX Loader.
			add_action( 'after_setup_theme', array( $this, 'module_loader' ), -20 );

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), -999 );

			// Load files.
			add_action( 'init', array( $this, 'init' ), -999 );

			// Jet Dashboard Init
			add_action( 'init', array( $this, 'jet_dashboard_init' ), -999 );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Load the theme modules.
		 *
		 * @since  1.0.0
		 */
		public function module_loader() {
			require $this->plugin_path( 'includes/modules/loader.php' );

			$this->module_loader = new Jet_Menu_CX_Loader(
				array(
					$this->plugin_path( 'includes/modules/vue-ui/cherry-x-vue-ui.php' ),
					$this->plugin_path( 'includes/modules/dynamic-css/cherry-x-dynamic-css.php' ),
					$this->plugin_path( 'includes/modules/customizer/cherry-x-customizer.php' ),
					$this->plugin_path( 'includes/modules/fonts-manager/cherry-x-fonts-manager.php' ),
					$this->plugin_path( 'includes/modules/jet-dashboard/jet-dashboard.php' ),
					$this->plugin_path( 'includes/modules/db-updater/cx-db-updater.php' ),
				)
			);
		}

		/**
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {

			$this->load_files();

			$this->dynamic_css = new CX_Dynamic_CSS( array(
				'parent_handles' => array(
					'js'  => 'jet-menu-public',
				),
			) );

			jet_menu_assets()->init();
			jet_menu_post_type()->init();
			jet_menu_css_file()->init();
			jet_menu_public_manager()->init();
			jet_menu_integration()->init();
			jet_menu_option_page();
			jet_menu_options_presets()->init();
			jet_menu_svg_manager()->init();

			$this->include_integration_theme_file();
			$this->include_integration_plugin_file();

			//Init Rest Api
			new \Jet_Menu\Rest_Api();

			jet_menu_settings_nav()->init();

			if ( is_admin() ) {

				if ( ! $this->has_elementor() ) {
					$this->required_plugins_notice();
				}

				// Init DB upgrader
				new Jet_Menu_DB_Upgrader();
			}
		}

		/**
		 * [jet_dashboard_init description]
		 * @return [type] [description]
		 */
		public function jet_dashboard_init() {

			if ( is_admin() ) {

				$jet_dashboard_module_data = $this->module_loader->get_included_module_data( 'jet-dashboard.php' );

				$jet_dashboard = \Jet_Dashboard\Dashboard::get_instance();

				$jet_dashboard->init( array(
					'path'           => $jet_dashboard_module_data['path'],
					'url'            => $jet_dashboard_module_data['url'],
					'cx_ui_instance' => array( $this, 'jet_dashboard_ui_instance_init' ),
					'plugin_data'    => array(
						'slug'    => 'jet-menu',
						'file'    => 'jet-menu/jet-menu.php',
						'version' => $this->get_version(),
					),
				) );
			}
		}

		/**
		 * [jet_dashboard_ui_instance_init description]
		 * @return [type] [description]
		 */
		public function jet_dashboard_ui_instance_init() {
			$cx_ui_module_data = $this->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );

			return new CX_Vue_UI( $cx_ui_module_data );
		}

		/**
		 * Return dynamic CSS instance
		 *
		 * @return object
		 */
		public function dynamic_css() {
			return $this->dynamic_css;
		}

		/**
		 * Show recommended plugins notice.
		 *
		 * @return void
		 */
		public function required_plugins_notice() {
			require $this->plugin_path( 'includes/lib/class-tgm-plugin-activation.php' );
			add_action( 'tgmpa_register', array( $this, 'register_required_plugins' ) );
		}

		/**
		 * Register required plugins
		 *
		 * @return void
		 */
		public function register_required_plugins() {

			$plugins = array(
				array(
					'name'     => 'Elementor',
					'slug'     => 'elementor',
					'required' => true,
				),
			);

			$config = array(
				'id'           => 'jet-menu',
				'default_path' => '',
				'menu'         => 'tgmpa-install-plugins',
				'parent_slug'  => 'plugins.php',
				'capability'   => 'manage_options',
				'has_notices'  => true,
				'dismissable'  => true,
				'dismiss_msg'  => '',
				'is_automatic' => false,
				'strings'      => array(
					'notice_can_install_required'     => _n_noop(
						'JetMenu for Elementor requires the following plugin: %1$s.',
						'JetMenu for Elementor requires the following plugins: %1$s.',
						'jet-menu'
					),
					'notice_can_install_recommended'  => _n_noop(
						'JetMenu for Elementor recommends the following plugin: %1$s.',
						'JetMenu for Elementor recommends the following plugins: %1$s.',
						'jet-menu'
					),
				),
			);

			tgmpa( $plugins, $config );

		}

		/**
		 * Check if theme has elementor
		 *
		 * @return boolean
		 */
		public function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		/**
		 * [elementor description]
		 * @return [type] [description]
		 */
		public function elementor() {
			return \Elementor\Plugin::$instance;
		}

		/**
		 * Load required files.
		 *
		 * @return void
		 */
		public function load_files() {
			require $this->plugin_path( 'includes/class-jet-menu-assets.php' );
			require $this->plugin_path( 'includes/class-jet-menu-dynamic-css.php' );
			require $this->plugin_path( 'includes/class-jet-menu-settings-nav.php' );
			require $this->plugin_path( 'includes/class-jet-menu-post-type.php' );
			require $this->plugin_path( 'includes/class-jet-menu-tools.php' );
			require $this->plugin_path( 'includes/class-jet-menu-integration.php' );
			require $this->plugin_path( 'includes/walkers/class-jet-menu-main-walker.php' );
			require $this->plugin_path( 'includes/walkers/class-jet-menu-widget-walker.php' );
			require $this->plugin_path( 'includes/class-jet-menu-public-manager.php' );
			require $this->plugin_path( 'includes/class-jet-menu-options-page.php' );
			require $this->plugin_path( 'includes/class-jet-menu-options-presets.php' );
			require $this->plugin_path( 'includes/class-jet-menu-css-file.php' );
			require $this->plugin_path( 'includes/class-jet-menu-db-upgrader.php' );
			require $this->plugin_path( 'includes/class-jet-menu-svg-manager.php' );

			// Rest Api
			require $this->plugin_path( 'includes/rest-api/rest-api.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/base.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/elementor-template.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/plugin-settings.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/get-menu-items.php' );
		}

		/**
		 * Include integration theme file
		 *
		 * @return void
		 */
		public function include_integration_theme_file() {

			$template = get_template();
			$disabled = jet_menu_option_page()->get_option( 'jet-menu-disable-integration-' . $template, 'false' );
			$disabled = filter_var( $disabled, FILTER_VALIDATE_BOOLEAN );

			if ( is_readable( $this->plugin_path( "integration/themes/{$template}/functions.php" ) ) && ! $disabled ) {
				require $this->plugin_path( "integration/themes/{$template}/functions.php" );
			}

		}

		/**
		 * Include plugin integrations file
		 *
		 * @return [type] [description]
		 */
		public function include_integration_plugin_file() {

			$active_plugins = get_option( 'active_plugins' );

			foreach ( glob( $this->plugin_path( 'integration/plugins/*' ) ) as $path ) {

				if ( ! is_dir( $path ) ) {
					continue;
				}

				$this->dir = basename( $path );

				$matched_plugins = array_filter( $active_plugins, array( $this, 'is_plugin_active' ) );

				if ( ! empty( $matched_plugins ) ) {
					require "{$path}/functions.php";
				}

			}

		}

		/**
		 * Callback to check if plugin is active
		 * @param  [type]  $plugin [description]
		 * @return boolean         [description]
		 */
		public function is_plugin_active( $plugin ) {
			return ( false !== strpos( $plugin, $this->dir . '/' ) );
		}

		/**
		 * Returns URL for current theme in theme-integration directory
		 *
		 * @param  string $file Path to file inside theme folder
		 * @return [type]       [description]
		 */
		public function get_theme_url( $file ) {

			$template = get_template();

			return $this->plugin_url( "integration/themes/{$template}/{$file}" );
		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}
		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-menu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-menu/template-path', 'jet-menu/' );
		}

		/**
		 * Returns path to template file.
		 *
		 * @return string|bool
		 */
		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			$template = apply_filters( 'jet-menu/get-template/found', $template, $name );

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function activation() {
			require $this->plugin_path( 'includes/class-jet-menu-post-type.php' );
			jet_menu_post_type()->init();
			flush_rewrite_rules();
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function deactivation() {
			flush_rewrite_rules();
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

if ( ! function_exists( 'jet_menu' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function jet_menu() {
		return Jet_Menu::get_instance();
	}
}

jet_menu();
