<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Library' ) ) {

	/**
	 * Define Jet_Popup_Library class
	 */
	class Jet_Popup_Library {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * [$key description]
		 * @var string
		 */
		public $key = 'jet-popup-library';

		/**
		 * Init page
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'add_popup_library_page' ], 90 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_library_assets' ), 11 );

			add_action( 'admin_footer', [ $this, 'render_vue_template' ] );
		}

		/**
		 * [get_settings_page description]
		 * @return [type] [description]
		 */
		public function get_library_page_url() {
			return admin_url( 'admin.php?&page=' . $this->key );
		}

		/**
		 * [add_popup_library_page description]
		 */
		public function add_popup_library_page() {

			add_submenu_page(
				'jet-dashboard',
				__( 'JetPopup Library', 'jet-popup' ),
				__( 'JetPopup Library', 'jet-popup' ),
				'edit_pages',
				$this->key,
				[ $this, 'library_page_render'],
				61
			);
		}

		/**
		 * [library_page_render description]
		 * @return [type] [description]
		 */
		public function library_page_render() {
			$crate_action = add_query_arg(
				array(
					'action' => 'jet_popup_create_from_preset',
				),
				esc_url( admin_url( 'admin.php' ) )
			);

			require jet_popup()->plugin_path( 'templates/vue-templates/preset-page.php' );
		}

		/**
		 * [enqueue_admin_library_assets description]
		 * @return [type] [description]
		 */
		public function enqueue_admin_library_assets() {

			if ( isset( $_REQUEST['page'] ) && $this->key === $_REQUEST['page'] ) {

				$module_data = jet_popup()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
				$cx_vue_ui   = new CX_Vue_UI( $module_data );

				$cx_vue_ui->enqueue_assets();

				wp_enqueue_style(
					'jet-popup-admin',
					jet_popup()->plugin_url( 'assets/css/jet-popup-admin.css' ),
					[],
					jet_popup()->get_version()
				);

				wp_enqueue_script(
					'jet-popup-admin',
					jet_popup()->plugin_url( 'assets/js/jet-popup-admin.js' ),
					[
						'jquery',
						'jet-axios',
						'cx-vue-ui',
					],
					jet_popup()->get_version(),
					true
				);

				$localize_data['version'] = jet_popup()->get_version();
				$localize_data['requiredPluginData'] = [
					'jet-elements' => [
						'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jetelements.svg',
						'link'  => 'https://crocoblock.com/plugins/jetelements/',
					],
					'jet-blocks'   => [
						'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jetblocks.svg',
						'link'  => 'https://crocoblock.com/plugins/jetblocks/',
					],
					'jet-tricks'   => [
						'badge' => 'https://account.crocoblock.com/free-download/images/jetlogo/jettricks.svg',
						'link'  => 'https://crocoblock.com/plugins/jettricks/',
					],
					'cf7'          => [
						'badge' => jet_popup()->plugin_url( 'assets/image/cf7-badge.png' ),
						'link'  => 'https://wordpress.org/plugins/contact-form-7/',
					],
				];

				$localize_data['libraryPresetsUrl'] = 'https://jetpopup.zemez.io/wp-json/croco/v1/presets';
				$localize_data['libraryPresetsCategoryUrl'] = 'https://jetpopup.zemez.io/wp-json/croco/v1/presets-categories';

				$localize_data = apply_filters( 'jet-popup/admin/localized-data', $localize_data );

				wp_localize_script(
					'jet-popup-admin',
					'jetPopupData',
					$localize_data
				);
			}
		}

		/**
		 * [render_vue_template description]
		 * @return [type] [description]
		 */
		public function render_vue_template() {

			$vue_templates = [
				'preset-library',
				'preset-filters',
				'preset-list',
				'preset-item',
			];

			foreach ( glob( jet_popup()->plugin_path( 'templates/vue-templates/' ) . '*.php' ) as $file ) {
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
