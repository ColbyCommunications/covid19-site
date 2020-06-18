<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

use Elementor\Icons_Manager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Menu_Assets' ) ) {

	/**
	 * Define Jet_Menu_Assets class
	 */
	class Jet_Menu_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {

			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ), 99 );

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'editor_styles' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_public_assets' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ), 10 );

			add_action( 'wp_footer', array( $this, 'init_elementor_frontend_assets' ), 9 );

			add_action( 'wp_footer', array( $this, 'render_vue_template' ) );

			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_elementor_widget_scripts' ) );

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'icons_font_styles' ) );

			add_action( 'elementor/preview/enqueue_styles', array( $this, 'icons_font_styles' ) );

			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_scripts' ) );
		}

		/**
		 * Load admin assets
		 *
		 * @param  string $hook Current page hook.
		 * @return void
		 */
		public function register_admin_assets( $hook ) {

			wp_register_style(
				'font-awesome-all',
				jet_menu()->plugin_url( 'assets/public/lib/font-awesome/css/all.min.css' ),
				array(),
				'5.12.0'
			);

			wp_register_style(
				'font-awesome-v4-shims',
				jet_menu()->plugin_url( 'assets/public/lib/font-awesome/css/v4-shims.min.css' ),
				array(),
				'5.12.0'
			);

			wp_register_style(
				'jet-menu-admin',
				jet_menu()->plugin_url( 'assets/admin/css/admin.css' ),
				array( 'font-awesome-all', 'font-awesome-v4-shims' ),
				jet_menu()->get_version()
			);
		}

		/**
		 * Load editor styles
		 *
		 * @return void
		 */
		public function editor_styles() {

			if ( ! isset( $_REQUEST['context'] ) || 'jet-menu' !== $_REQUEST['context'] ) {
				return;
			}

			wp_enqueue_style(
				'jet-menu-editor',
				jet_menu()->plugin_url( 'assets/admin/css/editor.css' ),
				array(),
				jet_menu()->get_version()
			);

		}

		/**
		 * Enqueue icons font styles
		 *
		 * @return void
		 */
		public function icons_font_styles() {

			wp_enqueue_style(
				'jet-menu-icons',
				jet_menu()->plugin_url( 'assets/admin/css/editor-icons.css' ),
				array(),
				jet_menu()->get_version()
			);

		}

		/**
		 * Enqueue plugin scripts only with elementor scripts
		 *
		 * @return void
		 */
		public function editor_scripts() {

			wp_enqueue_script(
				'jet-menu-editor',
				jet_menu()->plugin_url( 'assets/editor/js/jet-menu-editor.js' ),
				array( 'jquery' ),
				jet_menu()->get_version(),
				true
			);
		}

		/**
		 * Load public assets
		 *
		 * @param  string $hook Current page hook.
		 * @return void
		 */
		public function register_public_assets() {

			$suffix = '.min';

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$suffix = '';
			}

			wp_register_style(
				'font-awesome-all',
				jet_menu()->plugin_url( 'assets/public/lib/font-awesome/css/all.min.css' ),
				array(),
				'5.12.0'
			);

			wp_register_style(
				'font-awesome-v4-shims',
				jet_menu()->plugin_url( 'assets/public/lib/font-awesome/css/v4-shims.min.css' ),
				array(),
				'5.12.0'
			);

			wp_register_style(
				'jet-menu-public',
				jet_menu()->plugin_url( 'assets/public/css/public.css' ),
				array( 'font-awesome-all', 'font-awesome-v4-shims' ),
				jet_menu()->get_version()
			);

			wp_register_script(
				'jet-menu-vue',
				jet_menu()->plugin_url( 'assets/public/js/vue' . $suffix . '.js' ),
				array(),
				'2.6.11',
				true
			);

			wp_register_script(
				'jet-menu-public',
				jet_menu()->plugin_url( 'assets/public/js/jet-menu-public-script.js' ),
				array( 'jquery', 'jet-menu-vue' ),
				jet_menu()->get_version(),
				true
			);

			$rest_api_url = apply_filters( 'jet-menu/rest/url', get_rest_url() );

			wp_localize_script( 'jet-menu-public', 'jetMenuPublicSettings', apply_filters(
				'jet-menu/assets/public/localize',
				array(
					'version'        => jet_menu()->get_version(),
					'ajaxUrl'        => esc_url( admin_url( 'admin-ajax.php' ) ),
					'isMobile'       => filter_var( Jet_Menu_Tools::is_phone(), FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false',
					'templateApiUrl' => $rest_api_url . 'jet-menu-api/v1/elementor-template',
					'menuItemsApiUrl'=> $rest_api_url . 'jet-menu-api/v1/get-menu-items',
					'devMode'        => is_user_logged_in() ? 'true' : 'false',
					'menuSettings'   => array(
						'jetMenuRollUp'            => jet_menu_option_page()->get_option( 'jet-menu-roll-up', 'false' ),
						'jetMenuMouseleaveDelay'   => jet_menu_option_page()->get_option( 'jet-menu-mouseleave-delay', 500 ),
						'jetMenuMegaWidthType'     => jet_menu_option_page()->get_option( 'jet-mega-menu-width-type', 'container' ),
						'jetMenuMegaWidthSelector' => jet_menu_option_page()->get_option( 'jet-mega-menu-selector-width-type', '' ),
						'jetMenuMegaOpenSubType'   => jet_menu_option_page()->get_option( 'jet-menu-open-sub-type', 'hover' ),
						'jetMenuMegaAjax'          => jet_menu_option_page()->get_option( 'jet-menu-mega-ajax-loading', 'false' ),
					),
				)
			) );
		}

		/**
		 * Enqueue public assets.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_public_assets() {
			wp_enqueue_style( 'jet-menu-public' );
			wp_enqueue_script( 'jet-menu-public' );
		}

		/**
		 * [init_elementor_frontend_assets description]
		 * @return [type] [description]
		 */
		public function init_elementor_frontend_assets() {

			// Init Elementor frontend essets if template loaded using ajax
			if ( ! \Elementor\Plugin::$instance->frontend->has_elementor_in_page() ) {
				\Elementor\Plugin::$instance->frontend->enqueue_styles();
				\Elementor\Plugin::$instance->frontend->enqueue_scripts();
			}
		}

		/**
		 * [render_vue_template description]
		 * @return [type] [description]
		 */
		public function render_vue_template() {

			$vue_templates = array(
				'mobile-menu',
				'mobile-menu-list',
				'mobile-menu-item',
			);

			foreach ( glob( jet_menu()->plugin_path() . 'templates/public/vue-templates/*.php' ) as $file ) {
				$path_info = pathinfo( $file );
				$template_name = $path_info['filename'];

				if ( in_array( $template_name, $vue_templates ) ) {?>
					<script type="text/x-template" id="<?php echo $template_name; ?>-template"><?php
						require $file; ?>
					</script><?php
				}
			}
		}

		/**
		 * Enqueue plugin scripts only with elementor scripts
		 *
		 * @return void
		 */
		public function enqueue_elementor_widget_scripts() {
			wp_enqueue_script(
				'jet-menu-widgets-scripts',
				jet_menu()->plugin_url( 'assets/public/js/jet-menu-widgets-scripts.js' ),
				array( 'jquery', 'elementor-frontend', 'jet-menu-public' ),
				jet_menu()->get_version(),
				true
			);
		}

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
	}

}

/**
 * Returns instance of Jet_Menu_Assets
 *
 * @return object
 */
function jet_menu_assets() {
	return Jet_Menu_Assets::get_instance();
}
