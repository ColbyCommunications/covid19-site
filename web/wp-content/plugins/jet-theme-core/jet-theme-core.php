<?php
/**
 * Plugin Name: JetThemeCore
 * Plugin URI:  https://crocoblock.com/plugins/jetthemecore/
 * Description: Most powerful plugin created to make building websites super easy
 * Version:     1.2.2
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-theme-core
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_Theme_Core` doesn't exists yet.
if ( ! class_exists( 'Jet_Theme_Core' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_Theme_Core {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    Jet_Theme_Core
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
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.2.2';

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Plugin base name
		 *
		 * @var string
		 */
		public $plugin_name = null;

		/**
		 * Components
		 */
		public $module_loader;

		/**
		 * [$assets description]
		 * @var [type]
		 */
		public $assets;

		/**
		 * [$settings description]
		 * @var [type]
		 */
		public $settings;

		/**
		 * [$settings_manager description]
		 * @var [type]
		 */
		public $settings_manager;

		/**
		 * [$dashboard description]
		 * @var [type]
		 */
		public $dashboard;

		/**
		 * [$dashboard_module description]
		 * @var [type]
		 */
		public $dashboard_module;

		/**
		 * @var Jet_Theme_Core_Templates_Post_Type
		 */
		public $templates;
		public $templates_manager;
		/**
		 * @var Jet_Theme_Core_Config
		 */
		public $config;
		public $locations;
		public $structures;
		/**
		 * @var Jet_Theme_Core_Conditions_Manager
		 */
		public $conditions;
		/**
		 * @var Jet_Theme_Core_API
		 */
		public $api;
		public $compatibility;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			$this->plugin_name = plugin_basename( __FILE__ );

			// Load framework
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
		 * Load framework modules
		 *
		 * @return [type] [description]
		 */
		public function module_loader() {

			require $this->plugin_path( 'includes/modules/loader.php' );

			$this->module_loader = new Jet_Theme_Core_CX_Loader(
				array(
					$this->plugin_path( 'includes/modules/interface-builder/cherry-x-interface-builder.php' ),
					$this->plugin_path( 'includes/modules/vue-ui/cherry-x-vue-ui.php' ),
					$this->plugin_path( 'includes/modules/jet-dashboard/jet-dashboard.php' ),
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

			$this->config            = new Jet_Theme_Core_Config();
			$this->assets            = new Jet_Theme_Core_Assets();
			$this->api               = new Jet_Theme_Core_API();
			$this->settings          = new Jet_Theme_Core_Settings();
			$this->templates         = new Jet_Theme_Core_Templates_Post_Type();
			$this->locations         = new Jet_Theme_Core_Locations();
			$this->structures        = new Jet_Theme_Core_Structures();
			$this->conditions        = new Jet_Theme_Core_Conditions_Manager();
			$this->compatibility     = new Jet_Theme_Core_Compatibility();

			new Jet_Theme_Core_Elementor_Integration();

			//Init Rest Api
			new \Jet_Theme_Core\Rest_Api();

			if ( is_admin() ) {

				$this->dashboard         = new Jet_Theme_Core_Dashboard();
				$this->templates_manager = new Jet_Theme_Core_Templates_Manager();

				new \Jet_Theme_Core\Settings();

				new Jet_Theme_Core_Ajax_Handlers();
			}

			do_action( 'jet-theme-core/init', $this );

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
						'slug'    => 'jet-theme-core',
						'file'    => 'jet-theme-core/jet-theme-core.php',
						'version' => $this->get_version(),
						'plugin_links' => array(
							array(
								'label'  => esc_html__( 'Theme Builder', 'jet-theme-core' ),
								'url'    => add_query_arg( array( 'post_type' => 'jet-theme-core' ), admin_url( 'edit.php' ) ),
								'target' => '_self',
							),
							array(
								'label'  => esc_html__( 'Kava Theme', 'jet-theme-core' ),
								'url'    => add_query_arg(
									array(
										'page'    => 'jet-dashboard-settings-page',
										'subpage' => 'jet-theme-core-general-settings'
									),
									admin_url( 'admin.php' )
								),
								'target' => '_self',
							),
							array(
								'label'  => esc_html__( 'Settings', 'jet-theme-core' ),
								'url'    => add_query_arg(
									array(
										'page'    => 'jet-dashboard-settings-page',
										'subpage' => 'jet-theme-core-general-settings'
									),
									admin_url( 'admin.php' )
								),
								'target' => '_self',
							),
						),
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
		 * Load required files
		 *
		 * @return void
		 */
		public function load_files() {

			// Global
			require $this->plugin_path( 'includes/assets.php' );
			require $this->plugin_path( 'includes/settings.php' );
			require $this->plugin_path( 'includes/settings/manager.php' );
			require $this->plugin_path( 'includes/config.php' );
			require $this->plugin_path( 'includes/api.php' );
			require $this->plugin_path( 'includes/ajax-handlers.php' );
			require $this->plugin_path( 'includes/rest-api/rest-api.php' );
			require $this->plugin_path( 'includes/elementor-integration.php' );
			require $this->plugin_path( 'includes/utils.php' );
			require $this->plugin_path( 'includes/locations.php' );
			require $this->plugin_path( 'includes/compatibility.php' );

			// Dashboard
			require $this->plugin_path( 'includes/dashboard/manager.php' );

			// Templates
			require $this->plugin_path( 'includes/templates/post-type.php' );
			require $this->plugin_path( 'includes/templates/manager.php' );

			// Structures
			require $this->plugin_path( 'includes/structures/manager.php' );

			// Conditions
			require $this->plugin_path( 'includes/conditions/manager.php' );

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
		 * Check if theme has elementor
		 *
		 * @return boolean
		 */
		public function has_elementor_pro() {
			return defined( 'ELEMENTOR_PRO_VERSION' );
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
			load_plugin_textdomain( 'jet-theme-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-theme-core/template-path', 'jet-theme-core/' );
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
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function deactivation() {
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return Jet_Theme_Core
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

if ( ! function_exists( 'jet_theme_core' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return Jet_Theme_Core
	 */
	function jet_theme_core() {
		return Jet_Theme_Core::get_instance();
	}
}

jet_theme_core();
