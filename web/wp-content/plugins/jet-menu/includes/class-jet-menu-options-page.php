<?php
/**
 * Option page Class
 */

// If class `Popups_Options_Page` doesn't exists yet.
if ( ! class_exists( 'Jet_Menu_Options_Page' ) ) {

	/**
	 * Jet_Menu_Options_Page class.
	 */
	class Jet_Menu_Options_Page {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Fonts loader instance
		 *
		 * @var object
		 */
		protected $fonts_loader = null;

		/**
		 * [$customizer description]
		 * @var null
		 */
		protected $customizer = null;

		/**
		 * [$current_options description]
		 * @var array
		 */
		public $current_options = array();

		/**
		 * Default options
		 *
		 * @var array
		 */
		public $default_options = array(
			'jet-menu-animation'             => 'fade',
			'jet-menu-mega-bg-type'          => 'fill-color',
			'jet-menu-mega-bg-color'         => '#fff',
			'jet-menu-mega-bg-color-opacity' => 100,
			'jet-menu-mega-bg-image'         => '',
			'jet-menu-mega-padding'          => array(
				'units'     => 'px',
				'is_linked' => true,
				'size'      => array(
					'top'       => '10',
					'right'     => '10',
					'bottom'    => '10',
					'left'      => '10',
				),
			),
		);

		/**
		 * Options cache
		 *
		 * @var boolean
		 */
		private $options = false;

		/**
		 * Slug DB option field.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private $options_slug = 'jet_menu_options';

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			$module_data = jet_menu()->module_loader->get_included_module_data( 'cherry-x-customizer.php' );

			$this->customizer = new CX_Customizer(
				array(
					'prefix'     => 'jet-menu',
					'options'    => array(),
					'path'       => $module_data['path'],
					'just_fonts' => true,
				)
			);

			$this->fonts_loader = new CX_Fonts_Manager(
				array(
					'prefix'    => $this->options_slug(),
					'type'      => 'option',
					'single'    => true,
					'get_fonts' => function() {
						return $fonts = $this->customizer->get_fonts();
					},
					'options'   => array(
						'main' => array(
							'family'  => 'jet-top-menu-font-family',
							'style'   => 'jet-top-menu-font-style',
							'weight'  => 'jet-top-menu-font-weight',
							'charset' => 'jet-top-menu-subset',
						),
						'main-desc' => array(
							'family'  => 'jet-top-menu-desc-font-family',
							'style'   => 'jet-top-menu-desc-font-style',
							'weight'  => 'jet-top-menu-desc-font-weight',
							'charset' => 'jet-top-menu-desc-subset',
						),
						'sub' => array(
							'family'  => 'jet-sub-menu-font-family',
							'style'   => 'jet-sub-menu-font-style',
							'weight'  => 'jet-sub-menu-font-weight',
							'charset' => 'jet-sub-menu-subset',
						),
						'sub-desc' => array(
							'family'  => 'jet-sub-menu-desc-font-family',
							'style'   => 'jet-sub-menu-desc-font-style',
							'weight'  => 'jet-sub-menu-desc-font-weight',
							'charset' => 'jet-sub-menu-desc-subset',
						),
						'top-badge' => array(
							'family'  => 'jet-menu-top-badge-font-family',
							'style'   => 'jet-menu-top-badge-font-style',
							'weight'  => 'jet-menu-top-badge-font-weight',
							'charset' => 'jet-menu-top-badge-subset',
						),
						'sub-badge' => array(
							'family'  => 'jet-menu-sub-badge-font-family',
							'style'   => 'jet-menu-sub-badge-font-style',
							'weight'  => 'jet-menu-sub-badge-font-weight',
							'charset' => 'jet-menu-sub-badge-subset',
						),
						'mobile-toggle-typo' => array(
							'family'  => 'jet-menu-mobile-toggle-text-font-family',
							'style'   => 'jet-menu-mobile-toggle-text-font-style',
							'weight'  => 'jet-menu-mobile-toggle-text-font-weight',
							'charset' => 'jet-menu-mobile-toggle-text-subset',
						),
						'mobile-back-typo' => array(
							'family'  => 'jet-menu-mobile-back-text-font-family',
							'style'   => 'jet-menu-mobile-back-text-font-style',
							'weight'  => 'jet-menu-mobile-back-text-font-weight',
							'charset' => 'jet-menu-mobile-back-text-subset',
						),
						'mobile-breadcrumbs-typo' => array(
							'family'  => 'jet-menu-mobile-breadcrumbs-text-font-family',
							'style'   => 'jet-menu-mobile-breadcrumbs-text-font-style',
							'weight'  => 'jet-menu-mobile-breadcrumbs-text-font-weight',
							'charset' => 'jet-menu-mobile-breadcrumbs-text-subset',
						),
						'mobile-label-typo' => array(
							'family'  => 'jet-mobile-items-label-font-family',
							'style'   => 'jet-mobile-items-label-font-style',
							'weight'  => 'jet-mobile-items-label-font-weight',
							'charset' => 'jet-mobile-items-label-subset',
						),
						'mobile-items-desc' => array(
							'family'  => 'jet-mobile-items-desc-font-family',
							'style'   => 'jet-mobile-items-desc-font-style',
							'weight'  => 'jet-mobile-items-desc-font-weight',
							'charset' => 'jet-mobile-items-desc-subset',
						),
						'mobile-badge-typo' => array(
							'family'  => 'jet-mobile-items-badge-font-family',
							'style'   => 'jet-mobile-items-badge-font-style',
							'weight'  => 'jet-mobile-items-badge-font-weight',
							'charset' => 'jet-mobile-items-badge-subset',
						),

					),
				)
			);

			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'process_export' ) );
				add_action( 'admin_init', array( $this, 'process_reset' ) );
			}

			// Load the admin menu.
			add_action( 'admin_menu', array( $this, 'add_menu_item' ), 99 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 99 );

			add_action( 'wp_ajax_jet_menu_import_options', array( $this, 'process_import' ) );

			add_filter( 'jet-data-importer/export/options-to-export', array( $this, 'export_menu_options' ) );
		}

		/**
		 * Return options db key.
		 *
		 * @return string
		 */

		public function options_slug() {
			return $this->options_slug;
		}

		/**
		 * Pass menu options key into exported options array
		 *
		 * @param  [type] $options [description]
		 * @return [type]          [description]
		 */
		public function export_menu_options( $options ) {
			$options[] = $this->options_slug;

			return $options;
		}

		/**
		 * Register the admin menu.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function add_menu_item() {

			add_submenu_page(
				'jet-dashboard',
				esc_html__( 'JetMenu Settings', 'jet-menu' ),
				esc_html__( 'JetMenu Settings', 'jet-menu' ),
				'manage_options',
				$this->options_slug . '_page',
				array( $this, 'render_page' )
			);
		}

		/**
		 * [render_page description]
		 * @return [type] [description]
		 */
		public function render_page() {
			include jet_menu()->get_template( 'admin/options-page.php' );
		}

		/**
		 * [admin_assets description]
		 * @return [type] [description]
		 */
		public function admin_assets() {

			if ( isset( $_REQUEST['page'] ) && $this->options_slug . '_page' === $_REQUEST['page'] ) {

				$module_data = jet_menu()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
				$ui          = new CX_Vue_UI( $module_data );

				$ui->enqueue_assets();

				wp_enqueue_style( 'jet-menu-admin' );

				wp_enqueue_script(
					'jet-menu-options-page-script',
					jet_menu()->plugin_url( 'assets/admin/js/options-page.js' ),
					array( 'cx-vue-ui' ),
					jet_menu()->get_version(),
					true
				);

				wp_localize_script(
					'jet-menu-options-page-script',
					'JetMenuOptionsPageConfig',
					apply_filters( 'jet-menu/admin/settings-page-config', $this->get_options_page_config() )
				);
			}
		}

		/**
		 * [get_options_page_config description]
		 * @return [type] [description]
		 */
		public function get_options_page_config() {

			$rest_api_url = apply_filters( 'jet-menu/rest/admin/url', get_rest_url() );

			return array(
				'optionsApiUrl'    => $rest_api_url . 'jet-menu-api/v1/plugin-settings',
				'rawOptionsData'   => $this->get_option(),
				'optionPresetList' => jet_menu_options_presets()->get_presets_select_options(),
				'importUrl'        => add_query_arg( array( 'jet-action' => 'import-options' ), esc_url( admin_url( 'admin.php' ) ) ),
				'exportUrl'        => add_query_arg( array( 'jet-action' => 'export-options' ), esc_url( admin_url( 'admin.php' ) ) ),
				'resetUrl'         => add_query_arg( array( 'jet-action' => 'reset-options' ), esc_url( admin_url( 'admin.php' ) ) ),
				'optionsPageUrl'   => add_query_arg( array( 'page' => 'jet_menu_options_page' ), esc_url( admin_url( 'admin.php' ) ) ),
				'optionsData'      => $this->get_options_data(),
				'arrowsIcons'      => jet_menu_tools()->get_arrows_icons(),
				'iconsFetchJson'   => jet_menu()->plugin_url( 'assets/public/lib/font-awesome/js/solid.js' ),
				'templateList'     => jet_menu_tools()->get_elementor_templates_select_options(),
			);
		}

		/**
		 * [get_options_data description]
		 * @return [type] [description]
		 */
		public function get_options_data() {

			$default_dimensions = array(
				'top'       => '',
				'right'     => '',
				'bottom'    => '',
				'left'      => '',
				'is_linked' => true,
				'units'     => 'px',
			);

			$this->add_option( 'svg-uploads', array(
				'value' => $this->get_option( 'svg-uploads', 'enabled' ),
			) );

			// General
			$this->add_option( 'jet-menu-animation', array(
				'value'   => $this->get_option( 'jet-menu-animation', 'fade' ),
				'options' => array(
					array(
						'label' => esc_html__( 'None', 'jet-menu' ),
						'value' => 'none',
					),
					array(
						'label' => esc_html__( 'Fade', 'jet-menu' ),
						'value' => 'fade',
					),
					array(
						'label' => esc_html__( 'Move Up', 'jet-menu' ),
						'value' => 'move-up',
					),
					array(
						'label' => esc_html__( 'Move Down', 'jet-menu' ),
						'value' => 'move-down',
					)
				),
			) );

			$this->add_option( 'jet-menu-roll-up', array(
				'value' => $this->get_option( 'jet-menu-roll-up', 'true' ),
			) );

			$this->add_option( 'jet-menu-show-for-device', array(
				'value'   => $this->get_option( 'jet-menu-show-for-device', 'both' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Desktop and mobile view', 'jet-menu' ),
						'value' => 'both',
					),
					array(
						'label' => esc_html__( 'Desktop view on all devices', 'jet-menu' ),
						'value' => 'desktop',
					),
					array(
						'label' => esc_html__( 'Mobile view on all devices', 'jet-menu' ),
						'value' => 'mobile',
					),
				),
			) );

			$this->add_option( 'jet-menu-mega-ajax-loading', array(
				'value' => $this->get_option( 'jet-menu-mega-ajax-loading', false ),
			) );

			$this->add_option( 'jet-menu-mouseleave-delay', array(
				'value' => $this->get_option( 'jet-menu-mouseleave-delay', 500 ),
			) );

			$this->add_option( 'jet-mega-menu-width-type', array(
				'value'   => $this->get_option( 'jet-mega-menu-width-type', 'container' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Width same as main container width', 'jet-menu' ),
						'value' => 'container',
					),
					array(
						'label' => esc_html__( 'Width same as total items width', 'jet-menu' ),
						'value' => 'items',
					),
					array(
						'label' => esc_html__( 'Width same as Custom css selector width', 'jet-menu' ),
						'value' => 'selector',
					)
				),
			) );

			$this->add_option( 'jet-mega-menu-selector-width-type', array(
				'value' => $this->get_option( 'jet-mega-menu-selector-width-type', '' ),
			) );

			$this->add_option( 'jet-menu-open-sub-type', array(
				'value' => $this->get_option( 'jet-menu-open-sub-type', 'hover' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Hover', 'jet-menu' ),
						'value' => 'hover',
					),
					array(
						'label' => esc_html__( 'Click', 'jet-menu' ),
						'value' => 'click',
					),
				),
			) );

			$this->add_option( 'jet-menu-disable-integration-' . get_template(), array(
				'value' => $this->get_option( 'jet-menu-disable-integration-' . get_template(), false ),
			) );

			$this->add_option( 'jet-menu-cache-css', array(
				'value' => $this->get_option( 'jet-menu-cache-css', false ),
			) );

			//Menu Container Styles
			$this->add_option( 'jet-menu-container-alignment', array(
				'value'   => $this->get_option( 'jet-menu-container-alignment', 'flex-end' ),
				'options' => $this->get_aligment_select_options(),
			) );

			$this->add_option( 'jet-menu-min-width', array(
				'value' => $this->get_option( 'jet-menu-min-width', 0 ),
			) );

			$this->add_option( 'jet-menu-mega-padding', array(
				'value' => $this->get_option( 'jet-menu-mega-padding', $default_dimensions ),
			) );

			$this->add_background_options( 'jet-menu-container' );

			$this->add_border_options( 'jet-menu-container' );

			$this->add_box_shadow_options( 'jet-menu-container' );

			$this->add_option( 'jet-menu-mega-border-radius', array(
				'value' => $this->get_option( 'jet-menu-mega-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-inherit-first-radius', array(
				'value' => $this->get_option( 'jet-menu-inherit-first-radius', false ),
			) );

			$this->add_option( 'jet-menu-inherit-last-radius', array(
				'value' => $this->get_option( 'jet-menu-inherit-last-radius', false ),
			) );

			// Sub Panels Settings
			$this->add_option( 'jet-menu-sub-panel-width-simple', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-width-simple', 200 ),
			) );

			$this->add_background_options( 'jet-menu-sub-panel-simple' );

			$this->add_border_options( 'jet-menu-sub-panel-simple' );

			$this->add_box_shadow_options( 'jet-menu-sub-panel-simple' );

			$this->add_option( 'jet-menu-sub-panel-border-radius-simple', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-border-radius-simple', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-panel-padding-simple', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-padding-simple', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-panel-margin-simple', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-margin-simple', $default_dimensions ),
			) );

			$this->add_background_options( 'jet-menu-sub-panel-mega' );

			$this->add_border_options( 'jet-menu-sub-panel-mega' );

			$this->add_box_shadow_options( 'jet-menu-sub-panel-mega' );

			$this->add_option( 'jet-menu-sub-panel-border-radius-mega', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-border-radius-mega', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-panel-padding-mega', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-padding-mega', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-panel-margin-mega', array(
				'value' => $this->get_option( 'jet-menu-sub-panel-margin-mega', $default_dimensions ),
			) );

			// Top Level Items
			$this->add_option( 'jet-menu-item-max-width', array(
				'value' => $this->get_option( 'jet-menu-item-max-width', '' ),
			) );

			$this->add_typography_options( 'jet-top-menu' );

			$this->add_option( 'jet-show-top-menu-desc', array(
				'value' => $this->get_option( 'jet-show-top-menu-desc', false ),
			) );

			$this->add_typography_options( 'jet-top-menu-desc' );

			$this->add_option( 'jet-menu-item-text-color', array(
				'value' => $this->get_option( 'jet-menu-item-text-color', '' ),
			) );

			$this->add_option( 'jet-menu-item-desc-color', array(
				'value' => $this->get_option( 'jet-menu-item-desc-color', '' ),
			) );

			$this->add_option( 'jet-menu-top-icon-color', array(
				'value' => $this->get_option( 'jet-menu-top-icon-color', '' ),
			) );

			$this->add_option( 'jet-menu-top-arrow-color', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-color', '' ),
			) );

			$this->add_background_options( 'jet-menu-item' );

			$this->add_border_options( 'jet-menu-item' );

			$this->add_border_options( 'jet-menu-first-item' );

			$this->add_border_options( 'jet-menu-last-item' );

			$this->add_box_shadow_options( 'jet-menu-item' );

			$this->add_option( 'jet-menu-item-border-radius', array(
				'value' => $this->get_option( 'jet-menu-item-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-padding', array(
				'value' => $this->get_option( 'jet-menu-item-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-margin', array(
				'value' => $this->get_option( 'jet-menu-item-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-text-color-hover', array(
				'value' => $this->get_option( 'jet-menu-item-text-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-item-desc-color-hover', array(
				'value' => $this->get_option( 'jet-menu-item-desc-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-top-icon-color-hover', array(
				'value' => $this->get_option( 'jet-menu-top-icon-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-top-arrow-color-hover', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-color-hover', '' ),
			) );

			$this->add_background_options( 'jet-menu-item-hover' );

			$this->add_border_options( 'jet-menu-item-hover' );

			$this->add_border_options( 'jet-menu-first-item-hover' );

			$this->add_border_options( 'jet-menu-last-item-hover' );

			$this->add_box_shadow_options( 'jet-menu-item-hover' );

			$this->add_option( 'jet-menu-item-border-radius-hover', array(
				'value' => $this->get_option( 'jet-menu-item-border-radius-hover', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-padding-hover', array(
				'value' => $this->get_option( 'jet-menu-item-padding-hover', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-margin-hover', array(
				'value' => $this->get_option( 'jet-menu-item-margin-hover', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-text-color-active', array(
				'value' => $this->get_option( 'jet-menu-item-text-color-active', '' ),
			) );

			$this->add_option( 'jet-menu-item-desc-color-active', array(
				'value' => $this->get_option( 'jet-menu-item-desc-color-active', '' ),
			) );

			$this->add_option( 'jet-menu-top-icon-color-active', array(
				'value' => $this->get_option( 'jet-menu-top-icon-color-active', '' ),
			) );

			$this->add_option( 'jet-menu-top-arrow-color-active', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-color-active', '' ),
			) );

			$this->add_background_options( 'jet-menu-item-active' );

			$this->add_border_options( 'jet-menu-item-active' );

			$this->add_border_options( 'jet-menu-first-item-active' );

			$this->add_border_options( 'jet-menu-last-item-active' );

			$this->add_box_shadow_options( 'jet-menu-item-active' );

			$this->add_option( 'jet-menu-item-border-radius-active', array(
				'value' => $this->get_option( 'jet-menu-item-border-radius-active', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-padding-active', array(
				'value' => $this->get_option( 'jet-menu-item-padding-active', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-item-margin-active', array(
				'value' => $this->get_option( 'jet-menu-item-margin-active', $default_dimensions ),
			) );

			// Sub Level Items
			$this->add_typography_options( 'jet-sub-menu' );

			$this->add_option( 'jet-show-sub-menu-desc', array(
				'value' => $this->get_option( 'jet-show-sub-menu-desc', false ),
			) );

			$this->add_typography_options( 'jet-sub-menu-desc' );

			$this->add_option( 'jet-menu-sub-text-color', array(
				'value' => $this->get_option( 'jet-menu-sub-text-color', '' ),
			) );

			$this->add_option( 'jet-menu-sub-desc-color', array(
				'value' => $this->get_option( 'jet-menu-sub-desc-color', '' ),
			) );

			$this->add_option( 'jet-menu-sub-icon-color', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-color', '' ),
			) );

			$this->add_option( 'jet-menu-sub-arrow-color', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-color', '' ),
			) );

			$this->add_background_options( 'jet-menu-sub' );

			$this->add_border_options( 'jet-menu-sub' );

			$this->add_border_options( 'jet-menu-sub-first' );

			$this->add_border_options( 'jet-menu-sub-last' );

			$this->add_box_shadow_options( 'jet-menu-sub' );

			$this->add_option( 'jet-menu-sub-border-radius', array(
				'value' => $this->get_option( 'jet-menu-sub-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-padding', array(
				'value' => $this->get_option( 'jet-menu-sub-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-margin', array(
				'value' => $this->get_option( 'jet-menu-sub-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-text-color', array(
				'value' => $this->get_option( 'jet-menu-sub-text-color', '' ),
			) );

			$this->add_option( 'jet-menu-sub-text-color-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-text-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-sub-desc-color-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-desc-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-sub-icon-color-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-sub-arrow-color-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-color-hover', '' ),
			) );

			$this->add_background_options( 'jet-menu-sub-hover' );

			$this->add_border_options( 'jet-menu-sub-hover' );

			$this->add_border_options( 'jet-menu-sub-first-hover' );

			$this->add_border_options( 'jet-menu-sub-last-hover' );

			$this->add_box_shadow_options( 'jet-menu-sub-hover' );

			$this->add_option( 'jet-menu-sub-border-radius-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-border-radius-hover', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-padding-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-padding-hover', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-margin-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-margin-hover', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-text-color-hover', array(
				'value' => $this->get_option( 'jet-menu-sub-text-color-hover', '' ),
			) );

			$this->add_option( 'jet-menu-sub-text-color-active', array(
				'value' => $this->get_option( 'jet-menu-sub-text-color-active', '' ),
			) );

			$this->add_option( 'jet-menu-sub-desc-color-active', array(
				'value' => $this->get_option( 'jet-menu-sub-desc-color-active', '' ),
			) );

			$this->add_option( 'jet-menu-sub-icon-color-active', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-color-active', '' ),
			) );

			$this->add_option( 'jet-menu-sub-arrow-color-active', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-color-active', '' ),
			) );

			$this->add_background_options( 'jet-menu-sub-active' );

			$this->add_border_options( 'jet-menu-sub-active' );

			$this->add_border_options( 'jet-menu-sub-first-active' );

			$this->add_border_options( 'jet-menu-sub-last-active' );

			$this->add_box_shadow_options( 'jet-menu-sub-active' );

			$this->add_option( 'jet-menu-sub-border-radius-active', array(
				'value' => $this->get_option( 'jet-menu-sub-border-radius-active', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-padding-active', array(
				'value' => $this->get_option( 'jet-menu-sub-padding-active', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-margin-active', array(
				'value' => $this->get_option( 'jet-menu-sub-margin-active', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-text-color-active', array(
				'value' => $this->get_option( 'jet-menu-sub-text-color-active', '' ),
			) );

			//Advanced Styles
			$this->add_option( 'jet-menu-top-icon-size', array(
				'value' => $this->get_option( 'jet-menu-top-icon-size', '' ),
			) );

			$this->add_option( 'jet-menu-top-icon-margin', array(
				'value' => $this->get_option( 'jet-menu-top-icon-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-top-icon-ver-position', array(
				'value' => $this->get_option( 'jet-menu-top-icon-ver-position', 'center' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-menu-top-icon-hor-position', array(
				'value' => $this->get_option( 'jet-menu-top-icon-hor-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
				),
			) );

			$this->add_option( 'jet-menu-top-icon-order', array(
				'value' => $this->get_option( 'jet-menu-top-icon-order', '' ),
			) );

			$this->add_option( 'jet-menu-sub-icon-size', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-size', '' ),
			) );

			$this->add_option( 'jet-menu-sub-icon-margin', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-icon-ver-position', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-ver-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-menu-sub-icon-hor-position', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-hor-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
				),
			) );

			$this->add_option( 'jet-menu-sub-icon-order', array(
				'value' => $this->get_option( 'jet-menu-sub-icon-order', '' ),
			) );

			// Badge Styles
			$this->add_option( 'jet-menu-top-badge-text-color', array(
				'value' => $this->get_option( 'jet-menu-top-badge-text-color', '' ),
			) );

			$this->add_typography_options( 'jet-menu-top-badge' );

			$this->add_background_options( 'jet-menu-top-badge-bg' );

			$this->add_border_options( 'jet-menu-top-badge' );

			$this->add_box_shadow_options( 'jet-menu-top-badge' );

			$this->add_option( 'jet-menu-top-badge-border-radius', array(
				'value' => $this->get_option( 'jet-menu-top-badge-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-top-badge-padding', array(
				'value' => $this->get_option( 'jet-menu-top-badge-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-top-badge-margin', array(
				'value' => $this->get_option( 'jet-menu-top-badge-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-top-badge-ver-position', array(
				'value' => $this->get_option( 'jet-menu-top-badge-ver-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-menu-top-badge-hor-position', array(
				'value' => $this->get_option( 'jet-menu-top-badge-hor-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
				),
			) );

			$this->add_option( 'jet-menu-top-badge-order', array(
				'value' => $this->get_option( 'jet-menu-top-badge-order', '' ),
			) );

			$this->add_option( 'jet-menu-top-badge-hide', array(
				'value' => $this->get_option( 'jet-menu-top-badge-hide', false ),
			) );

			$this->add_option( 'jet-menu-sub-badge-text-color', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-text-color', '' ),
			) );

			$this->add_typography_options( 'jet-menu-sub-badge' );

			$this->add_background_options( 'jet-menu-sub-badge-bg' );

			$this->add_border_options( 'jet-menu-sub-badge' );

			$this->add_box_shadow_options( 'jet-menu-sub-badge' );

			$this->add_option( 'jet-menu-sub-badge-border-radius', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-badge-padding', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-badge-margin', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-badge-ver-position', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-ver-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-menu-sub-badge-hor-position', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-hor-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
				),
			) );

			$this->add_option( 'jet-menu-sub-badge-order', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-order', '' ),
			) );

			$this->add_option( 'jet-menu-sub-badge-hide', array(
				'value' => $this->get_option( 'jet-menu-sub-badge-hide', false ),
			) );

			// Arrow
			$this->add_option( 'jet-menu-top-arrow', array(
				'value' => $this->get_option( 'jet-menu-top-arrow', 'fa-angle-down' ),
			) );

			$this->add_option( 'jet-menu-top-arrow-size', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-size', '' ),
			) );

			$this->add_option( 'jet-menu-top-arrow-margin', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-top-arrow-ver-position', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-ver-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-menu-top-arrow-hor-position', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-hor-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
				),
			) );

			$this->add_option( 'jet-menu-top-arrow-order', array(
				'value' => $this->get_option( 'jet-menu-top-arrow-order', '' ),
			) );

			$this->add_option( 'jet-menu-sub-arrow', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow', 'fa-angle-right' ),
			) );

			$this->add_option( 'jet-menu-sub-arrow-size', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-size', '' ),
			) );

			$this->add_option( 'jet-menu-sub-arrow-margin', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-sub-arrow-ver-position', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-ver-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-menu-sub-arrow-hor-position', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-hor-position', '' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
				),
			) );

			$this->add_option( 'jet-menu-sub-arrow-order', array(
				'value' => $this->get_option( 'jet-menu-sub-arrow-order', '' ),
			) );

			// Mobile Styles
			$this->add_option( 'jet-menu-mobile-layout', array(
				'value'   => $this->get_option( 'jet-menu-mobile-layout', 'slide-out' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Slide Out', 'jet-menu' ),
						'value' => 'slide-out',
					),
					array(
						'label' => esc_html__( 'Dropdown', 'jet-menu' ),
						'value' => 'dropdown',
					),
					array(
						'label' => esc_html__( 'Push', 'jet-menu' ),
						'value' => 'push',
					),
				),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-position', array(
				'value'   => $this->get_option( 'jet-menu-mobile-toggle-position', 'default' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Default', 'jet-menu' ),
						'value' => 'default',
					),
					array(
						'label' => esc_html__( 'Fixed to top-left screen corner', 'jet-menu' ),
						'value' => 'fixed-left',
					),
					array(
						'label' => esc_html__( 'Fixed to top-right screen corner', 'jet-menu' ),
						'value' => 'fixed-right',
					),
				),
			) );

			$this->add_option( 'jet-menu-mobile-container-position', array(
				'value'   => $this->get_option( 'jet-menu-mobile-container-position', 'right' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Right', 'jet-menu' ),
						'value' => 'right',
					),
					array(
						'label' => esc_html__( 'Left', 'jet-menu' ),
						'value' => 'left',
					),
				),
			) );

			$this->add_option( 'jet-menu-mobile-sub-trigger', array(
				'value'   => $this->get_option( 'jet-menu-mobile-sub-trigger', 'item' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Menu Item', 'jet-menu' ),
						'value' => 'item',
					),
					array(
						'label' => esc_html__( 'Sub Menu Icon', 'jet-menu' ),
						'value' => 'submarker',
					),
				),
			) );

			$this->add_option( 'jet-menu-mobile-header-template', array(
				'value'   => $this->get_option( 'jet-menu-mobile-header-template', '' ),
				'options' => jet_menu_tools()->get_elementor_templates_select_options(),
			) );

			$this->add_option( 'jet-menu-mobile-before-template', array(
				'value'   => $this->get_option( 'jet-menu-mobile-before-template', '' ),
				'options' => jet_menu_tools()->get_elementor_templates_select_options(),
			) );

			$this->add_option( 'jet-menu-mobile-after-template', array(
				'value'   => $this->get_option( 'jet-menu-mobile-after-template', '' ),
				'options' => jet_menu_tools()->get_elementor_templates_select_options(),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-icon', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-icon', 'fa-bars' ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-opened-icon', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-opened-icon', 'fa-times' ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-text', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-text', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-loader', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-loader', 'true' ),
			) );

			$this->add_option( 'jet-menu-mobile-back-text', array(
				'value' => $this->get_option( 'jet-menu-mobile-back-text', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-use-breadcrumb', array(
				'value' => $this->get_option( 'jet-menu-mobile-use-breadcrumb', 'true' ),
			) );

			$this->add_option( 'jet-menu-mobile-breadcrumb-icon', array(
				'value' => $this->get_option( 'jet-menu-mobile-breadcrumb-icon', 'fa-angle-right' ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-color', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-color', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-size', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-size', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-text-color', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-text-color', '' ),
			) );

			$this->add_typography_options( 'jet-menu-mobile-toggle-text' );

			$this->add_typography_options( 'jet-menu-mobile-back-text' );

			$this->add_option( 'jet-menu-mobile-toggle-bg', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-bg', '' ),
			) );

			$this->add_border_options( 'jet-menu-mobile-toggle' );

			$this->add_box_shadow_options( 'jet-menu-mobile-toggle' );

			$this->add_option( 'jet-menu-mobile-toggle-border-radius', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-mobile-toggle-padding', array(
				'value' => $this->get_option( 'jet-menu-mobile-toggle-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-mobile-container-width', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-width', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-breadcrumbs-text-color', array(
				'value' => $this->get_option( 'jet-menu-mobile-breadcrumbs-text-color', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-breadcrumbs-icon-color', array(
				'value' => $this->get_option( 'jet-menu-mobile-breadcrumbs-icon-color', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-breadcrumbs-icon-size', array(
				'value' => $this->get_option( 'jet-menu-mobile-breadcrumbs-icon-size', '' ),
			) );

			$this->add_typography_options( 'jet-menu-mobile-breadcrumbs-text' );

			$this->add_option( 'jet-menu-mobile-container-bg', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-bg', '' ),
			) );

			$this->add_border_options( 'jet-menu-mobile-container' );

			$this->add_box_shadow_options( 'jet-menu-mobile-container' );

			$this->add_option( 'jet-menu-mobile-container-padding', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-mobile-container-border-radius', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-menu-mobile-cover-bg', array(
				'value' => $this->get_option( 'jet-menu-mobile-cover-bg', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-container-close-icon', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-close-icon', 'fa-times' ),
			) );

			$this->add_option( 'jet-menu-mobile-container-back-icon', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-back-icon', 'fa-angle-left' ),
			) );

			$this->add_option( 'jet-menu-mobile-container-close-color', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-close-color', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-container-back-text-color', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-back-text-color', '' ),
			) );

			$this->add_option( 'jet-menu-mobile-container-close-size', array(
				'value' => $this->get_option( 'jet-menu-mobile-container-close-size', '' ),
			) );

			$this->add_option( 'jet-mobile-items-label-color', array(
				'value' => $this->get_option( 'jet-mobile-items-label-color', '' ),
			) );

			$this->add_option( 'jet-mobile-items-label-color-active', array(
				'value' => $this->get_option( 'jet-mobile-items-label-color-active', '' ),
			) );

			$this->add_typography_options( 'jet-mobile-items-label' );

			$this->add_option( 'jet-mobile-items-desc-enable', array(
				'value' => $this->get_option( 'jet-mobile-items-desc-enable', false ),
			) );

			$this->add_option( 'jet-mobile-items-desc-color', array(
				'value' => $this->get_option( 'jet-mobile-items-desc-color', '' ),
			) );

			$this->add_option( 'jet-mobile-items-desc-color-active', array(
				'value' => $this->get_option( 'jet-mobile-items-desc-color-active', '' ),
			) );

			$this->add_typography_options( 'jet-mobile-items-desc' );

			$this->add_option( 'jet-mobile-items-divider-enabled', array(
				'value' => $this->get_option( 'jet-mobile-items-divider-enabled', false ),
			) );

			$this->add_option( 'jet-mobile-items-divider-color', array(
				'value' => $this->get_option( 'jet-mobile-items-divider-color', '' ),
			) );

			$this->add_option( 'jet-mobile-items-divider-width', array(
				'value' => $this->get_option( 'jet-mobile-items-divider-width', '1' ),
			) );

			$this->add_option( 'jet-mobile-items-icon-enabled', array(
				'value' => $this->get_option( 'jet-mobile-items-icon-enabled', 'true' ),
			) );

			$this->add_option( 'jet-mobile-items-icon-color', array(
				'value' => $this->get_option( 'jet-mobile-items-icon-color', '' ),
			) );

			$this->add_option( 'jet-mobile-items-icon-size', array(
				'value' => $this->get_option( 'jet-mobile-items-icon-size', '' ),
			) );

			$this->add_option( 'jet-mobile-items-icon-ver-position', array(
				'value' => $this->get_option( 'jet-mobile-items-icon-ver-position', 'center' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-mobile-items-icon-margin', array(
				'value' => $this->get_option( 'jet-mobile-items-icon-margin', $default_dimensions ),
			) );

			$this->add_option( 'jet-mobile-items-badge-enabled', array(
				'value' => $this->get_option( 'jet-mobile-items-badge-enabled', 'true' ),
			) );

			$this->add_option( 'jet-mobile-items-badge-color', array(
				'value' => $this->get_option( 'jet-mobile-items-badge-color', '' ),
			) );

			$this->add_typography_options( 'jet-mobile-items-badge' );

			$this->add_option( 'jet-mobile-items-badge-bg-color', array(
				'value' => $this->get_option( 'jet-mobile-items-badge-bg-color', '' ),
			) );

			$this->add_option( 'jet-mobile-items-badge-ver-position', array(
				'value' => $this->get_option( 'jet-mobile-items-badge-ver-position', 'top' ),
				'options' => array(
					array(
						'label' => esc_html__( 'Top', 'jet-menu' ),
						'value' => 'top',
					),
					array(
						'label' => esc_html__( 'Center', 'jet-menu' ),
						'value' => 'center',
					),
					array(
						'label' => esc_html__( 'Bottom', 'jet-menu' ),
						'value' => 'bottom',
					),
				),
			) );

			$this->add_option( 'jet-mobile-items-badge-padding', array(
				'value' => $this->get_option( 'jet-mobile-items-badge-padding', $default_dimensions ),
			) );

			$this->add_option( 'jet-mobile-items-badge-border-radius', array(
				'value' => $this->get_option( 'jet-mobile-items-badge-border-radius', $default_dimensions ),
			) );

			$this->add_option( 'jet-mobile-items-dropdown-icon', array(
				'value' => $this->get_option( 'jet-mobile-items-dropdown-icon', 'fa-angle-right' ),
			) );

			$this->add_option( 'jet-mobile-items-dropdown-color', array(
				'value' => $this->get_option( 'jet-mobile-items-dropdown-color', '' ),
			) );

			$this->add_option( 'jet-mobile-items-dropdown-size', array(
				'value' => $this->get_option( 'jet-mobile-items-dropdown-size', '' ),
			) );

			$this->add_option( 'jet-mobile-loader-color', array(
				'value' => $this->get_option( 'jet-mobile-loader-color', '#3a3a3a' ),
			) );

			return $this->current_options;
		}

		/**
		 * [add_option description]
		 * @param boolean $slug [description]
		 * @param array   $args [description]
		 */
		public function add_option( $slug = false, $args = array() ) {

			if ( ! $slug || empty( $args ) ) {
				return false;
			}

			$this->current_options[ $slug ] = $args;
		}

		/**
		 * [add_option description]
		 * @param boolean $slug [description]
		 * @param array   $args [description]
		 */
		public function add_icon_option( $slug = false, $default = false, $prefix = 'fa' ) {

			if ( ! $slug ) {
				return false;
			}

			$value = $this->get_option( $slug, $default );

			if ( $value ) {
				$value = $prefix . ' ' . $value;
			}

			$this->current_options[ $slug ] = array(
				'value' => $value,
			);
		}

		/**
		 * [render_box_shadow_options description]
		 * @return [type] [description]
		 */
		public function render_background_options( $args ) {

			$args = wp_parse_args( $args, array(
				'label'    => '',
				'name'     => '',
				'defaults' => array(),
			) );

			include jet_menu()->get_template( 'admin/background-vue-group.php' );
		}

		/**
		 * [add_background_options description]
		 * @param [type] $args [description]
		 */
		public function add_background_options( $slug = false ) {

			if ( ! $slug ) {
				return false;
			}

			$background_options = array(
				$slug . '-switch' => array(
					'value' => $this->get_option( $slug . '-switch', 'false' ),
				),

				$slug . '-color' => array(
					'value' => $this->get_option( $slug . '-color', '#ffffff' ),
				),

				$slug . '-gradient-switch' => array(
					'value' => $this->get_option( $slug . '-gradient-switch', false ),
				),

				$slug . '-second-color' => array(
					'value' => $this->get_option( $slug . '-second-color', '' ),
				),

				$slug . '-direction' => array(
					'value'   => $this->get_option( $slug . '-direction', 'right' ),
					'options' => $this->get_direction_select_options(),
				),

				$slug . '-image' => array(
					'value' => $this->get_option( $slug . '-image', '' ),
				),

				$slug . '-position' => array(
					'value'   => $this->get_option( $slug . '-position', '' ),
					'options' => $this->get_position_select_options(),
				),

				$slug . '-attachment' => array(
					'value'   => $this->get_option( $slug . '-attachment', '' ),
					'options' => $this->get_attachment_select_options(),
				),

				$slug . '-repeat' => array(
					'value'   => $this->get_option( $slug . '-repeat', '' ),
					'options' => $this->get_repeat_select_options(),
				),

				$slug . '-size' => array(
					'value'   => $this->get_option( $slug . '-size', '' ),
					'options' => $this->get_size_select_options(),
				),
			);

			$this->current_options = array_merge( $this->current_options, $background_options );

		}

		/**
		 * [render_box_shadow_options description]
		 * @return [type] [description]
		 */
		public function render_border_options( $args ) {

			$args = wp_parse_args( $args, array(
				'label'    => '',
				'name'     => '',
				'defaults' => array(),
			) );

			include jet_menu()->get_template( 'admin/border-vue-group.php' );
		}

		/**
		 * [add_border_options description]
		 * @param boolean $slug [description]
		 */
		public function add_border_options( $slug = false ) {

			if ( ! $slug ) {
				return false;
			}

			$default_dimensions = array(
				'top'       => '',
				'right'     => '',
				'bottom'    => '',
				'left'      => '',
				'is_linked' => true,
				'units'     => 'px',
			);

			$border_options = array(
				$slug . '-border-switch' => array(
					'value' => $this->get_option( $slug . '-border-switch', 'false' ),
				),

				$slug . '-border-style' => array(
					'value'   => $this->get_option( $slug . '-border-style', '' ),
					'options' => $this->get_border_style_select_options(),
				),

				$slug . '-border-width' => array(
					'value' => $this->get_option( $slug . '-border-width', $default_dimensions ),
				),

				$slug . '-border-color' => array(
					'value' => $this->get_option( $slug . '-border-color', '' ),
				),
			);

			$this->current_options = array_merge( $this->current_options, $border_options );

		}

		/**
		 * [render_box_shadow_options description]
		 * @return [type] [description]
		 */
		public function render_box_shadow_options( $args ) {

			$args = wp_parse_args( $args, array(
				'label'    => '',
				'name'     => '',
				'defaults' => array(),
			) );

			include jet_menu()->get_template( 'admin/box-shadow-vue-group.php' );
		}

		/**
		 * [add_box_shadow_options description]
		 * @param boolean $slug [description]
		 */
		public function add_box_shadow_options( $slug = false ) {

			if ( ! $slug ) {
				return false;
			}

			$border_options = array(
				$slug . '-box-shadow-switch' => array(
					'value' => $this->get_option( $slug . '-box-shadow-switch', false ),
				),

				$slug . '-box-shadow-inset' => array(
					'value' => $this->get_option( $slug . '-box-shadow-inset', false ),
				),

				$slug . '-box-shadow-color' => array(
					'value' => $this->get_option( $slug . '-box-shadow-color', '' ),
				),

				$slug . '-box-shadow-h' => array(
					'value' => $this->get_option( $slug . '-box-shadow-h', '' ),
				),

				$slug . '-box-shadow-v' => array(
					'value' => $this->get_option( $slug . '-box-shadow-v', '' ),
				),

				$slug . '-box-shadow-blur' => array(
					'value' => $this->get_option( $slug . '-box-shadow-blur', '' ),
				),

				$slug . '-box-shadow-spread' => array(
					'value' => $this->get_option( $slug . '-box-shadow-spread', '' ),
				),
			);

			$this->current_options = array_merge( $this->current_options, $border_options );

		}

		/**
		 * [render_typography_options description]
		 * @param  [type] $args [description]
		 * @return [type]       [description]
		 */
		public function render_typography_options( $args ) {

			$args = wp_parse_args( $args, array(
				'label'    => '',
				'name'     => '',
				'defaults' => array(),
			) );

			include jet_menu()->get_template( 'admin/typography-vue-group.php' );
		}

		/**
		 * [add_typography_options description]
		 * @param boolean $slug [description]
		 */
		public function add_typography_options( $slug = false ) {

			if ( ! $slug ) {
				return false;
			}

			$typography_options = array(
				$slug . '-switch' => array(
					'value' => $this->get_option( $slug . '-switch', false ),
				),

				$slug . '-font-family' => array(
					'value'   => $this->get_option( $slug . '-font-family', '' ),
					'options' => $this->get_fonts_select_options(),
				),

				$slug . '-subset' => array(
					'value'   => $this->get_option( $slug . '-subset', '' ),
					'options' => $this->get_font_subset_select_options(),
				),

				$slug . '-font-size' => array(
					'value'   => $this->get_option( $slug . '-font-size', '' ),
				),

				$slug . '-line-height' => array(
					'value'   => $this->get_option( $slug . '-line-height', '' ),
				),

				$slug . '-font-weight' => array(
					'value'   => $this->get_option( $slug . '-font-weight', '' ),
					'options' => $this->get_font_weight_select_options(),
				),

				$slug . '-text-transform' => array(
					'value'   => $this->get_option( $slug . '-text-transform', '' ),
					'options' => $this->get_text_transform_select_options(),
				),

				$slug . '-font-style' => array(
					'value'   => $this->get_option( $slug . '-font-style', '' ),
					'options' => $this->get_font_style_select_options(),
				),

				$slug . '-letter-spacing' => array(
					'value' => $this->get_option( $slug . '-letter-spacing', '' ),
				),
			);

			$this->current_options = array_merge( $this->current_options, $typography_options );

		}

		/**
		 * [get_aligment_select_options description]
		 * @return [type] [description]
		 */
		public function get_aligment_select_options() {
			return array(
				array(
					'label' => esc_html__( 'Start', 'jet-menu' ),
					'value' => 'flex-start',
				),
				array(
					'label' => esc_html__( 'Center', 'jet-menu' ),
					'value' => 'center',
				),
				array(
					'label' => esc_html__( 'End', 'jet-menu' ),
					'value' => 'flex-end',
				),
				array(
					'label' => esc_html__( 'Stretch', 'jet-menu' ),
					'value' => 'stretch',
				),
			);
		}

		/**
		 * [get_direction_select_options description]
		 * @return [type] [description]
		 */
		public function get_direction_select_options() {
			return array(
				array(
					'label' => esc_html__( 'From Left to Right', 'jet-menu' ),
					'value' => 'right',
				),
				array(
					'label' => esc_html__( 'From Right to Left', 'jet-menu' ),
					'value' => 'left',
				),
				array(
					'label' => esc_html__( 'From Top to Bottom', 'jet-menu' ),
					'value' => 'bottom',
				),
				array(
					'label' => esc_html__( 'From Bottom to Top', 'jet-menu' ),
					'value' => 'top',
				),
			);
		}

		/**
		 * [get_position_select_options description]
		 * @return [type] [description]
		 */
		public function get_position_select_options() {
			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( 'Top Left', 'jet-menu' ),
					'value' => 'top left',
				),
				array(
					'label' => esc_html__( 'Top Center', 'jet-menu' ),
					'value' => 'top center',
				),
				array(
					'label' => esc_html__( 'Top Right', 'jet-menu' ),
					'value' => 'top right',
				),
				array(
					'label' => esc_html__( 'Center Left', 'jet-menu' ),
					'value' => 'center left',
				),
				array(
					'label' => esc_html__( 'Center Center', 'jet-menu' ),
					'value' => 'center center',
				),
				array(
					'label' => esc_html__( 'Center Right', 'jet-menu' ),
					'value' => 'center right',
				),
				array(
					'label' => esc_html__( 'Bottom Left', 'jet-menu' ),
					'value' => 'bottom left',
				),
				array(
					'label' => esc_html__( 'Bottom Center', 'jet-menu' ),
					'value' => 'bottom center',
				),
				array(
					'label' => esc_html__( 'Bottom Right', 'jet-menu' ),
					'value' => 'bottom right',
				),
			);
		}

		/**
		 * [get_attachment_select_options description]
		 * @return [type] [description]
		 */
		public function get_attachment_select_options() {
			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( 'Scroll', 'jet-menu' ),
					'value' => 'scroll',
				),
				array(
					'label' => esc_html__( 'Fixed', 'jet-menu' ),
					'value' => 'fixed',
				),
			);
		}

		/**
		 * [get_repeat_select_options description]
		 * @return [type] [description]
		 */
		public function get_repeat_select_options() {
			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( 'No Repeat', 'jet-menu' ),
					'value' => 'no-repeat',
				),
				array(
					'label' => esc_html__( 'Repeat', 'jet-menu' ),
					'value' => 'repeat',
				),
				array(
					'label' => esc_html__( 'Repeat X', 'jet-menu' ),
					'value' => 'repeat-x',
				),
				array(
					'label' => esc_html__( 'Repeat Y', 'jet-menu' ),
					'value' => 'repeat-y',
				),
			);
		}

		/**
		 * [get_size_select_options description]
		 * @return [type] [description]
		 */
		public function get_size_select_options() {
			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( 'Auto', 'jet-menu' ),
					'value' => 'auto',
				),
				array(
					'label' => esc_html__( 'Cover', 'jet-menu' ),
					'value' => 'cover',
				),
				array(
					'label' => esc_html__( 'Contain', 'jet-menu' ),
					'value' => 'contain',
				),
			);
		}

		/**
		 * [get_border_style_select_options description]
		 * @return [type] [description]
		 */
		public function get_border_style_select_options() {
			return array(
				array(
					'label' => esc_html__( 'None', 'jet-menu' ),
					'value' => 'none',
				),
				array(
					'label' => esc_html__( 'Solid', 'jet-menu' ),
					'value' => 'solid',
				),
				array(
					'label' => esc_html__( 'Double', 'jet-menu' ),
					'value' => 'double',
				),
				array(
					'label' => esc_html__( 'Dotted', 'jet-menu' ),
					'value' => 'dotted',
				),
				array(
					'label' => esc_html__( 'Dashed', 'jet-menu' ),
					'value' => 'dashed',
				),
			);
		}

		/**
		 * [get_font_weight_select_options description]
		 * @return [type] [description]
		 */
		public function get_font_weight_select_options() {
			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( '100', 'jet-menu' ),
					'value' => '100',
				),
				array(
					'label' => esc_html__( '200', 'jet-menu' ),
					'value' => '200',
				),
				array(
					'label' => esc_html__( '300', 'jet-menu' ),
					'value' => '300',
				),
				array(
					'label' => esc_html__( '400', 'jet-menu' ),
					'value' => '400',
				),
				array(
					'label' => esc_html__( '500', 'jet-menu' ),
					'value' => '500',
				),
				array(
					'label' => esc_html__( '600', 'jet-menu' ),
					'value' => '600',
				),
				array(
					'label' => esc_html__( '700', 'jet-menu' ),
					'value' => '700',
				),
				array(
					'label' => esc_html__( '800', 'jet-menu' ),
					'value' => '800',
				),
				array(
					'label' => esc_html__( '900', 'jet-menu' ),
					'value' => '900',
				),
			);
		}

		/**
		 * [get_text_transform_select_options description]
		 * @return [type] [description]
		 */
		public function get_text_transform_select_options() {

			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( 'Normal', 'jet-menu' ),
					'value' => 'none',
				),
				array(
					'label' => esc_html__( 'Uppercase', 'jet-menu' ),
					'value' => 'uppercase',
				),
				array(
					'label' => esc_html__( 'Lowercase', 'jet-menu' ),
					'value' => 'lowercase',
				),
				array(
					'label' => esc_html__( 'Capitalize', 'jet-menu' ),
					'value' => 'capitalize',
				),
			);
		}

		/**
		 * [get_font_style_select_options description]
		 * @return [type] [description]
		 */
		public function get_font_style_select_options() {

			return array(
				array(
					'label' => esc_html__( 'Default', 'jet-menu' ),
					'value' => '',
				),
				array(
					'label' => esc_html__( 'Normal', 'jet-menu' ),
					'value' => 'normal',
				),
				array(
					'label' => esc_html__( 'Italic', 'jet-menu' ),
					'value' => 'italic',
				),
				array(
					'label' => esc_html__( 'Oblique', 'jet-menu' ),
					'value' => 'oblique',
				),
			);
		}

		/**
		 * [get_fonts_select_options description]
		 * @return [type] [description]
		 */
		public function get_fonts_select_options() {

			$fonts_list = jet_menu_dynmic_css()->get_fonts_list();

			$fonts_select_options = [];

			if ( ! empty( $fonts_list ) ) {

				foreach ( $fonts_list as $font_name => $font_slug ) {

					if ( 0 !== $font_name ) {
						$fonts_select_options[] = array(
							'label' => $font_name,
							'value' => $font_name,
						);
					} else {
						$fonts_select_options[] = array(
							'label' => $font_slug,
							'value' => $font_name,
						);
					}
				}
			}

			return $fonts_select_options;
		}

		/**
		 * [get_font_subset_select_options description]
		 * @return [type] [description]
		 */
		public function get_font_subset_select_options() {

			return array(
				array(
					'label' => esc_html__( 'Latin', 'jet-menu' ),
					'value' => 'latin',
				),
				array(
					'label' => esc_html__( 'Greek', 'jet-menu' ),
					'value' => 'greek',
				),
				array(
					'label' => esc_html__( 'Cyrillic', 'jet-menu' ),
					'value' => 'cyrillic',
				),
			);
		}

		/**
		 * Build export URL
		 *
		 * @return string
		 */
		public function export_url() {
			return add_query_arg(
				array(
					'jet-action' => 'export-options',
				),
				esc_url( admin_url( 'admin.php' ) )
			);
		}

		/**
		 * Process options reset
		 *
		 * @return void
		 */
		public function process_reset() {

			if ( ! isset( $_GET['jet-action'] ) || 'reset-options' !== $_GET['jet-action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				die();
			}

			$this->save_options( $this->options_slug, $this->default_options );

			wp_redirect(
				add_query_arg(
					array( 'page' => 'jet_menu_options_page' ),
					esc_url( admin_url( 'admin.php' ) )
				)
			);

			die();
		}

		/**
		 * Process settings export
		 *
		 * @return void
		 */
		public function process_export() {

			if ( ! isset( $_GET['jet-action'] ) || 'export-options' !== $_GET['jet-action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				die();
			}

			$options = $this->get_option();

			if ( ! $options ) {
				$options = array();
			}

			$file = 'jet-menu-options-' . date( 'm-d-Y' ) . '.json';
			$data = json_encode( array(
				'jet_menu' => true,
				'options'  => $options,
			) );

			session_write_close();

			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: public' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . $file . '"' );
			header( 'Content-Transfer-Encoding: binary' );

			echo $data;

			die();
		}

		/**
		 * Process settings import
		 *
		 * @return void
		 */
		public function process_import() {

			if ( ! current_user_can( 'manage_options' ) ) {
				die();
			}

			$options = isset( $_POST['data'] ) ? $_POST['data'] : array();

			if ( empty( $options['jet_menu'] ) || empty( $options['options'] ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Incorrect data in options file', 'jet-menu' ),
				) );
			}

			$this->save_options( $this->options_slug, $options['options'] );

			wp_send_json_success( array(
				'message' => esc_html__( 'Options successfully imported. Page will be reloaded.', 'jet-menu' ),
			) );
		}

		/**
		 * Options field exist DB check
		 *
		 * @since 1.0.0
		 */
		public function is_db_options_exist( $option_name ) {

			( false == get_option( $option_name ) ) ? $is_exist = false : $is_exist = true;

			return $is_exist;
		}

		/**
		 *
		 * Save options to DB
		 *
		 * @since 1.0.0
		 */
		public function save_options( $option_name, $options ) {

			$options = array_merge( $this->default_options, $options );

			update_option( $option_name, $options );

			$this->fonts_loader->reset_fonts_cache();

			do_action( 'jet-menu/options-page/save' );
		}

		/**
		 * Set options externaly.
		 *
		 * @param  array  $options Options array to set.
		 * @return void
		 */
		public function pre_set_options( $options = array() ) {

			if ( empty( $options ) ) {
				$this->options = false;
			} else {
				$this->options = $options;
			}
		}

		/**
		 * Get option value
		 *
		 * @param string $options Option name.
		 * @since 1.0.0
		 */
		public function get_option( $option_name = null, $default = false ) {

			if ( empty( $this->options ) ) {
				$this->options = get_option( $this->options_slug, $this->default_options );
			}

			if ( ! $option_name && ! empty( $this->options ) ) {
				return $this->options;
			}

			return isset( $this->options[ $option_name ] ) ? $this->options[ $option_name ] : $default;
		}

		/**
		 * [set_option description]
		 * @param [type]  $option_name [description]
		 * @param boolean $value       [description]
		 */
		public function set_option( $option_name = null, $value = false ) {
			$current = get_option( jet_menu_option_page()->options_slug(), array() );

			if ( isset( $current[ $option_name ] ) ) {
				$current[ $option_name ] = $value;
			} else {
				$new_option[ $option_name ] = $value;

				$current = array_merge( $current, $new_option );
			}

			$this->save_options( $this->options_slug(), $current );
		}

		/**
		 * Create db options field if this is not exist
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function create_db_options_field() {

			if ( ! $this->is_db_options_exist( $this->options_slug ) ) {
				$this->save_options( $this->options_slug, $this->default_options );
			}

			if ( ! $this->is_db_options_exist( $this->options_slug . '_default' ) ) {
				$this->save_options( $this->options_slug . '_default', $this->default_options );
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

if ( ! function_exists( 'jet_menu_option_page' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function jet_menu_option_page() {
		return Jet_Menu_Options_Page::get_instance();
	}
}
