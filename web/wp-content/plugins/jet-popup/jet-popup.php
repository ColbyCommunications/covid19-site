<?php
/**
 * Plugin Name: JetPopup
 * Plugin URI:  https://crocoblock.com/plugins/jetpopup/
 * Description: The advanced plugin for creating popups with Elementor
 * Version:     1.5.5
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-popup
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package jet-popup
 * @author  Zemez
 * @version 1.0.0
 * @license GPL-2.0+
 * @copyright  2018, Zemez
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_Popup` doesn't exists yet.
if ( ! class_exists( 'Jet_Popup' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_Popup {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */

		private $version = '1.5.5';

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
		 * Framework component
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    object
		 */
		public $module_loader = null;

		/**
		 * [$assets description]
		 * @var [type]
		 */
		public $assets = null;

		/**
		 * [$post_type description]
		 * @var [type]
		 */
		public $post_type = null;

		/**
		 * [$settings description]
		 * @var null
		 */
		public $settings = null;

		/**
		 * [$popup_library description]
		 * @var null
		 */
		public $popup_library = null;

		/**
		 * [$export_import description]
		 * @var null
		 */
		public $export_import = null;

		/**
		 * [$conditions description]
		 * @var [type]
		 */
		public $conditions = null;

		/**
		 * [$extensions description]
		 * @var null
		 */
		public $extensions = null;

		/**
		 * [$integration description]
		 * @var null
		 */
		public $integration = null;

		/**
		 * [$generator description]
		 * @var null
		 */
		public $generator = null;

		/**
		 * [$ajax_handlers description]
		 * @var null
		 */
		public $ajax_handlers = null;

		/**
		 * [$elementor_finder description]
		 * @var null
		 */
		public $elementor_finder = null;

		/**
		 * Holder for compatibility module
		 *
		 * @var null
		 */
		public $compatibility = null;

		/**
		 * Holder for the Admin Bar module
		 *
		 * @var null
		 */
		public $admin_bar = null;

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

			// Check if Elementor installed and activated
			if ( ! did_action( 'elementor/loaded' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
				return;
			}

			// Register activation  hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );

			// Register deactivation hook.
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
		 * Load framework modules
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public function module_loader() {
			require $this->plugin_path( 'includes/modules/loader.php' );

			$this->module_loader = new Jet_Popup_CX_Loader(
				[
					$this->plugin_path( 'includes/modules/vue-ui/cherry-x-vue-ui.php' ),
					$this->plugin_path( 'includes/modules/jet-dashboard/jet-dashboard.php' ),
					$this->plugin_path( 'includes/modules/jet-elementor-extension/jet-elementor-extension.php' ),
					$this->plugin_path( 'includes/modules/db-updater/cx-db-updater.php' ),
					$this->plugin_path( 'includes/modules/admin-bar/jet-admin-bar.php' ),
				]
			);
		}

		/**
		 * [jet_dashboard_init description]
		 * @return [type] [description]
		 */
		public function jet_dashboard_init() {

			if ( is_admin() ) {

				$cx_ui_module_data         = $this->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
				$jet_dashboard_module_data = $this->module_loader->get_included_module_data( 'jet-dashboard.php' );

				$jet_dashboard = \Jet_Dashboard\Dashboard::get_instance();

				$jet_dashboard->init( array(
					'path'           => $jet_dashboard_module_data['path'],
					'url'            => $jet_dashboard_module_data['url'],
					'cx_ui_instance' => array( $this, 'jet_dashboard_ui_instance_init' ),
					'plugin_data'    => array(
						'slug'    => 'jet-popup',
						'file'    => 'jet-popup/jet-popup.php',
						'version' => $this->get_version(),
						'plugin_links' => array(
							array(
								'label'  => esc_html__( 'All Popups', 'jet-popup' ),
								'url'    => add_query_arg( array( 'post_type' => 'jet-popup' ), admin_url( 'edit.php' ) ),
								'target' => '_self',
							),
							array(
								'label'  => esc_html__( 'New Popup', 'jet-popup' ),
								'url'    => add_query_arg( array( 'post_type' => 'jet-popup' ), admin_url( 'post-new.php' ) ),
								'target' => '_self',
							),
							array(
								'label'  => esc_html__( 'Preset Library', 'jet-popup' ),
								'url'    => add_query_arg(
									array(
										'post_type' => 'jet-popup',
										'page' => 'jet-popup-library',
									),
									admin_url( 'edit.php' )
								),
								'target' => '_self',
							),
							array(
								'label'  => esc_html__( 'Settings', 'jet-popup' ),
								'url'    => add_query_arg(
									array(
										'page' => 'jet-dashboard-settings-page',
										'subpage' => 'jet-popup-integrations'
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
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {

			if ( ! $this->has_elementor() ) {
				return;
			}

			$this->load_files();

			$this->assets = new Jet_Popup_Assets();

			$this->post_type = new Jet_Popup_Post_Type();

			$this->settings = new Jet_Popup_Settings();

			$this->popup_library = new Jet_Popup_Library();

			$this->export_import = new Jet_Export_Import();

			$this->conditions = new Jet_Popup_Conditions_Manager();

			$this->extensions = new Jet_Popup_Element_Extensions();

			$this->integration = new Jet_Popup_Integration();

			$this->generator = new Jet_Popup_Generator();

			$this->ajax_handlers = new Jet_Popup_Ajax_Handlers();

			$this->elementor_finder = new Jet_Elementor_Finder();

			$this->compatibility = new Jet_Popup_Compatibility();

			$this->admin_bar = Jet_Admin_Bar::get_instance();

			if ( is_admin() ) {

				// Init Admin Ajax Handlers
				new Jet_Popup_Admin_Ajax_Handlers();

				// Init Rest Api
				new \Jet_Popup\Settings();

				// Init DB upgrader
				new Jet_Popup_DB_Upgrader();
			}

		}

		/**
		 * Load required files.
		 *
		 * @return void
		 */
		public function load_files() {
			require $this->plugin_path( 'includes/assets.php' );
			require $this->plugin_path( 'includes/admin-ajax-handlers.php' );
			require $this->plugin_path( 'includes/ajax-handlers.php' );
			require $this->plugin_path( 'includes/post-type.php' );
			require $this->plugin_path( 'includes/settings.php' );
			require $this->plugin_path( 'includes/settings/manager.php' );
			require $this->plugin_path( 'includes/popup-library.php' );
			require $this->plugin_path( 'includes/export-import.php' );
			require $this->plugin_path( 'includes/utils.php' );
			require $this->plugin_path( 'includes/conditions/manager.php' );
			require $this->plugin_path( 'includes/extension.php' );
			require $this->plugin_path( 'includes/integration.php' );
			require $this->plugin_path( 'includes/generator.php' );
			require $this->plugin_path( 'includes/db-upgrader.php' );
			require $this->plugin_path( 'includes/elementor-finder/elementor-finder.php' );
			require $this->plugin_path( 'includes/compatibility/manager.php' );

			// Lib
			if ( ! class_exists( 'Mobile_Detect' ) ) {
				require $this->plugin_path( 'includes/lib/class-mobile-detect.php' );
			}
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
			load_plugin_textdomain( 'jet-popup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-popup/template-path', 'jet-popup/' );
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
		 * [admin_notice_missing_main_plugin description]
		 * @return [type] [description]
		 */
		public function admin_notice_missing_main_plugin() {

			if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

			$elementor_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url() . 'plugin-install.php?s=elementor&tab=search&type=term',
				'<strong>' . esc_html__( 'Elementor', 'jet-popup' ) . '</strong>'
			);

			$message = sprintf(
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'jet-popup' ),
				'<strong>' . esc_html__( 'JetPopup', 'jet-popup' ) . '</strong>',
				$elementor_link
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function activation() {

			//Register jet-popup post type on activation hook
			require $this->plugin_path( 'includes/post-type.php' );

			Jet_Popup_Post_Type::register_post_type();

			flush_rewrite_rules();
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

if ( ! function_exists( 'jet_popup' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function jet_popup() {
		return Jet_Popup::get_instance();
	}
}

jet_popup();
