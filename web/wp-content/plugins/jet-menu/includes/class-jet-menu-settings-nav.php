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

if ( ! class_exists( 'Jet_Menu_Settings_Nav' ) ) {

	/**
	 * Define Jet_Menu_Settings_Nav class
	 */
	class Jet_Menu_Settings_Nav {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Holder for current menu ID
		 * @var integer
		 */
		protected $current_menu_id = null;

		/**
		 * Jet Menu settings page
		 *
		 * @var string
		 */
		protected $meta_key = 'jet_menu_settings';

		/**
		 * Constructor for the class
		 */
		public function init() {

			add_action( 'admin_head-nav-menus.php', array( $this, 'register_nav_meta_box' ), 9 );

			add_action( 'wp_ajax_jet_save_settings', array( $this, 'save_menu_settings' ) );

			add_action( 'wp_ajax_jet_get_nav_item_settings', array( $this, 'get_nav_item_settings' ) );

			add_action( 'wp_ajax_jet_save_nav_item_settings', array( $this, 'save_nav_item_settings' ) );

			add_filter( 'get_user_option_metaboxhidden_nav-menus', array( $this, 'force_metabox_visibile' ), 10 );

			add_action( 'admin_footer', array( $this, 'print_menu_settings_vue_template' ), 10 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 99 );

		}

		/**
		 * Register nav menus page metabox with mega menu settings.
		 *
		 * @return void
		 */
		public function register_nav_meta_box() {

			global $pagenow;

			if ( 'nav-menus.php' !== $pagenow ) {
				return;
			}

			add_meta_box(
				'jet-menu-settings',
				esc_html__( 'JetMenu Locations Settings', 'jet-menu' ),
				array( $this, 'render_metabox' ),
				'nav-menus',
				'side',
				'high'
			);

		}

		/**
		 * Print tabs templates
		 *
		 * @return void
		 */
		public function print_menu_settings_vue_template() {

			$screen = get_current_screen();

			if ( 'nav-menus' !== $screen->base ) {
				return;
			}

			include jet_menu()->get_template( 'admin/menu-settings-nav.php' );
		}

		/**
		 * [admin_assets description]
		 * @return [type] [description]
		 */
		public function admin_assets() {

			$screen = get_current_screen();

			if ( 'nav-menus' !== $screen->base ) {
				return;
			}

			$module_data = jet_menu()->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );
			$ui          = new CX_Vue_UI( $module_data );

			$ui->enqueue_assets();

			wp_enqueue_style( 'jet-menu-admin' );

			wp_enqueue_script(
				'jet-menu-nav-settings-script',
				jet_menu()->plugin_url( 'assets/admin/js/nav-settings.js' ),
				array( 'cx-vue-ui' ),
				jet_menu()->get_version(),
				true
			);

			wp_localize_script(
				'jet-menu-nav-settings-script',
				'JetMenuNavSettingsConfig',
				apply_filters( 'jet-menu/admin/nav-settings-config', array(
					'labels'        => array(
						'itemTriggerLabel' => __( 'JetMenu', 'jet-menu' ),
					),
					'currentMenuId' => $this->get_selected_menu_id(),
					'editURL'       => add_query_arg(
						array(
							'jet-open-editor' => 1,
							'item'            => '%id%',
							'menu'            => '%menuid%',
						),
						esc_url( admin_url( '/' ) )
					),
					'optionMenuList'   => $this->get_menu_select_options(),
					'optionPresetList' => jet_menu_options_presets()->get_presets_select_options(),
					'controlData'      => $this->default_nav_item_controls_data(),
					'locationSettings' => $this->get_nav_location_data(),
					'iconsFetchJson'   => jet_menu()->plugin_url( 'assets/public/lib/font-awesome/js/solid.js' ),
				) )
			);
		}

		/**
		 * [get_nav_settings_localize_data description]
		 * @return [type] [description]
		 */
		public function get_nav_location_data() {
			$menu_id         = $this->get_selected_menu_id();
			$theme_locations = get_registered_nav_menus();
			$saved_settings  = $this->get_settings( $menu_id );

			$location_list = array();

			foreach ( $theme_locations as $location => $name ) {

				if ( isset( $saved_settings[ $location ] ) ) {

					$location_list[ $location ] = array(
						'label'   => $name,
						'enabled' => isset( $saved_settings[ $location ]['enabled'] ) ? $saved_settings[ $location ]['enabled'] : false,
						'preset'  => isset( $saved_settings[ $location ]['preset'] ) ? $saved_settings[ $location ]['preset'] : '',
						'mobile'  => isset( $saved_settings[ $location ]['mobile'] ) ? $saved_settings[ $location ]['mobile'] : '',
					);
				} else {
					$location_list[ $location ] = array(
						'label'   => $name,
						'enabled' => false,
						'preset'  => '',
						'mobile'  => '',
					);
				}

			}

			return $location_list;
		}

		/**
		 * [get_controls_localize_data description]
		 * @return [type] [description]
		 */
		public function default_nav_item_controls_data() {
			return array(
				'enabled' => array(
					'value' => false,
				),
				'custom_mega_menu_position' => array(
					'value'   => 'default',
					'options' => array(
						array(
							'label' => esc_html__( 'Default', 'jet-menu' ),
							'value' => 'default',
						),
						array(
							'label' => esc_html__( 'Relative the menu item', 'jet-menu' ),
							'value' => 'relative-item',
						)
					),
				),
				'custom_mega_menu_width' => array(
					'value' => '',
				),
				'menu_icon_type' => array(
					'value'   => 'icon',
					'options' => array(
						array(
							'label' => esc_html__( 'Icon', 'jet-menu' ),
							'value' => 'icon',
						),
						array(
							'label' => esc_html__( 'Svg', 'jet-menu' ),
							'value' => 'svg',
						)
					),
				),
				'menu_icon' => array(
					'value' => '',
				),
				'menu_svg' => array(
					'value' => '',
				),
				'icon_color' => array(
					'value' => '',
				),
				'icon_size' => array(
					'value' => '',
				),
				'menu_badge' => array(
					'value' => '',
				),
				'badge_color' => array(
					'value' => '',
				),
				'badge_bg_color' => array(
					'value' => '',
				),
				'hide_item_text' => array(
					'value' => '',
				),
				'item_padding' => array(
					'value' => array(
						'top'       => '',
						'right'     => '',
						'bottom'    => '',
						'left'      => '',
						'is_linked' => true,
						'units'     => 'px',
					),
				),
				'mega_menu_width' => array(
					'value' => '',
				),
				'vertical_mega_menu_position' => array(
					'value'   => 'default',
					'options' => array(
						array(
							'label' => esc_html__( 'Relative the menu item', 'jet-menu' ),
							'value' => 'default',
						),
						array(
							'label' => esc_html__( 'Relative the menu container', 'jet-menu' ),
							'value' => 'top',
						)
					),
				),
			);
		}

		/**
		 * [get_nav_item_settings description]
		 * @return [type] [description]
		 */
		public function get_nav_item_settings() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$data = isset( $_POST['data'] ) ? $_POST['data'] : false;

			if ( ! $data ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Incorrect input data', 'jet-menu' ),
				) );
			}

			$default_settings = array();

			foreach ( $this->default_nav_item_controls_data() as $key => $value ) {
				$default_settings[ $key ] = $value['value'];
			}

			$current_settings = $this->get_item_settings( absint( $data['itemId'] ) );

			$current_settings = wp_parse_args( $current_settings, $default_settings );

			wp_send_json_success( array(
				'message'  => esc_html__( 'Success!', 'jet-menu' ),
				'settings' => $current_settings,
			) );
		}

		/**
		 * [save_nav_item_settings description]
		 * @return [type] [description]
		 */
		public function save_nav_item_settings() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$data = isset( $_POST['data'] ) ? $_POST['data'] : false;

			if ( ! $data ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Incorrect input data', 'jet-menu' ),
				) );
			}

			$item_id = $data['itemId'];
			$settings = $data['itemSettings'];

			$sanitized_settings = array();

			foreach ( $settings as $key => $value ) {
				$sanitized_settings[ $key ] = $this->sanitize_field( $key, $value );
			}

			$current_settings = $this->get_item_settings( $item_id );

			$new_settings = array_merge( $current_settings, $sanitized_settings );

			$this->set_item_settings( $item_id, $new_settings );

			do_action( 'jet-menu/item-settings/save' );

			wp_send_json_success( array(
				'message' => esc_html__( 'Item settings have been saved', 'jet-menu' ),
			) );
		}

		/**
		 * Save menu settings
		 *
		 * @return void
		 */
		public function save_menu_settings() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$data = isset( $_POST['data'] ) ? $_POST['data'] : false;

			if ( ! $data ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Incorrect input data', 'jet-menu' ),
				) );
			}

			$menu_id = isset( $data['menuId'] ) ? absint( $data['menuId'] ) : false;
			$settings = isset( $data['settings'] ) ? $data['settings'] : false;

			if ( ! $menu_id || ! $settings ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Required data is missed', 'jet-menu' ),
				) );
			}

			$current_settings = $this->get_settings( $menu_id );

			if ( ! $current_settings ) {
				$current_settings = array();
			}

			$new_settings = array_merge( $current_settings, $settings );

			$this->update_settings( $menu_id, $new_settings );

			wp_send_json_success( array(
				'message' => esc_html__( 'Menu settings have been saved', 'jet-menu' ),
			) );
		}

		/**
		 * Get settings from DB
		 *
		 * @param  [type] $menu_id [description]
		 * @return [type]          [description]
		 */
		public function get_settings( $menu_id ) {
			return get_term_meta( $menu_id, $this->meta_key, true );
		}

		/**
		 * Update menu item settings
		 *
		 * @param integer $id       [description]
		 * @param array   $settings [description]
		 */
		public function update_settings( $menu_id = 0, $settings = array() ) {
			update_term_meta( $menu_id, $this->meta_key, $settings );
		}

		/**
		 * Returns menu item settings
		 *
		 * @param  [type] $id [description]
		 * @return [type]     [description]
		 */
		public function get_item_settings( $id ) {
			$settings = get_post_meta( $id, $this->meta_key, true );

			return ! empty( $settings ) ? $settings : array();
		}

		/**
		 * Update menu item settings
		 *
		 * @param integer $id       [description]
		 * @param array   $settings [description]
		 */
		public function set_item_settings( $id = 0, $settings = array() ) {
			update_post_meta( $id, $this->meta_key, $settings );
		}

		/**
		 * Force nav menu metabox with JetMenu settings to be allways visible.
		 *
		 * @param  array $result
		 * @return array
		 */
		public function force_metabox_visibile( $result ) {

			if ( ! is_array( $result ) ) {
				return $result;
			}

			if ( in_array( 'jet-menu-settings', $result ) ) {
				$result = array_diff( $result, array( 'jet-menu-settings' ) );
			}
			return $result;
		}

		/**
		 * [get_menu_select_options description]
		 * @return [type] [description]
		 */
		public function get_menu_select_options() {

			$menu_select_options = array();

			$nav_menus = wp_get_nav_menus();

			if ( ! $nav_menus || empty( $nav_menus ) ) {
				return $menu_select_options;
			}

			$menu_select_options[] = array(
				'label' => esc_html( 'None', 'jet-menu' ),
				'value' => '',
			);

			foreach ( $nav_menus as $key => $menu_data ) {
				$menu_select_options[] = array(
					'label' => $menu_data->name,
					'value' => $menu_data->term_id,
				);
			}

			return $menu_select_options;
		}

		/**
		 * Render nav menus metabox
		 *
		 * @return void
		 */
		public function render_metabox() {

			$menu_id               = $this->get_selected_menu_id();
			$tagged_menu_locations = $this->get_tagged_theme_locations_for_menu_id( $menu_id );
			$theme_locations       = get_registered_nav_menus();
			$saved_settings        = $this->get_settings( $menu_id );

			if ( ! count( $theme_locations ) ) {
				$this->no_locations_message();
			} else if ( ! count ( $tagged_menu_locations ) ) {
				$this->empty_location_message();
			} else {

				include jet_menu()->get_template( 'admin/settings-nav.php' );
			}

		}

		/**
		 * Notice if no menu locations registered in theme
		 *
		 * @return void
		 */
		public function no_locations_message() {
			printf( '<p>%s</p>', esc_html__( 'This theme does not register any menu locations.', 'jet-menu' ) );
			printf( '<p>%s</p>', esc_html__( 'You will need to create a new menu location to use the JetMenu on your site.', 'jet-menu' ) );
		}

		/**
		 * Notice if no menu locations registered in theme
		 *
		 * @return void
		 */
		public function empty_location_message() {
			printf( '<p>%s</p>', esc_html__( 'Please assign this menu to a theme location to enable the JetMenu settings.', 'jet-menu' ) );
			printf( '<p>%s</p>', esc_html__( 'To assign this menu to a theme location, scroll to the bottom of this page and tag the menu to a \'Display location\'.', 'jet-menu' ) );
		}

		/**
		 * Return the locations that a specific menu ID has been tagged to.
		 *
		 * @author Tom Hemsley (https://wordpress.org/plugins/megamenu/)
		 * @param  $menu_id    int
		 * @return array
		 */
		public function get_tagged_theme_locations_for_menu_id( $menu_id ) {

			$locations          = array();
			$nav_menu_locations = get_nav_menu_locations();

			foreach ( get_registered_nav_menus() as $id => $name ) {

				if ( isset( $nav_menu_locations[ $id ] ) && $nav_menu_locations[ $id ] == $menu_id )
					$locations[ $id ] = $name;
				}

				return $locations;
			}

		/**
		 * Get the current menu ID.
		 *
		 * @author Tom Hemsley (https://wordpress.org/plugins/megamenu/)
		 * @return int
		 */
		public function get_selected_menu_id() {

			if ( null !== $this->current_menu_id ) {
				return $this->current_menu_id;
			}

			$nav_menus            = wp_get_nav_menus( array('orderby' => 'name') );
			$menu_count           = count( $nav_menus );
			$nav_menu_selected_id = isset( $_REQUEST['menu'] ) ? (int) $_REQUEST['menu'] : 0;
			$add_new_screen       = ( isset( $_GET['menu'] ) && 0 == $_GET['menu'] ) ? true : false;

			$this->current_menu_id = $nav_menu_selected_id;

			// If we have one theme location, and zero menus, we take them right into editing their first menu
			$page_count = wp_count_posts( 'page' );
			$one_theme_location_no_menus = ( 1 == count( get_registered_nav_menus() ) && ! $add_new_screen && empty( $nav_menus ) && ! empty( $page_count->publish ) ) ? true : false;

			// Get recently edited nav menu
			$recently_edited = absint( get_user_option( 'nav_menu_recently_edited' ) );
			if ( empty( $recently_edited ) && is_nav_menu( $this->current_menu_id ) ) {
				$recently_edited = $this->current_menu_id;
			}

			// Use $recently_edited if none are selected
			if ( empty( $this->current_menu_id ) && ! isset( $_GET['menu'] ) && is_nav_menu( $recently_edited ) ) {
				$this->current_menu_id = $recently_edited;
			}

			// On deletion of menu, if another menu exists, show it
			if ( ! $add_new_screen && 0 < $menu_count && isset( $_GET['action'] ) && 'delete' == $_GET['action'] ) {
				$this->current_menu_id = $nav_menus[0]->term_id;
			}

			// Set $this->current_menu_id to 0 if no menus
			if ( $one_theme_location_no_menus ) {
				$this->current_menu_id = 0;
			} elseif ( empty( $this->current_menu_id ) && ! empty( $nav_menus ) && ! $add_new_screen ) {
				// if we have no selection yet, and we have menus, set to the first one in the list
				$this->current_menu_id = $nav_menus[0]->term_id;
			}

			return $this->current_menu_id;

		}

		/**
		 * Sanitize field
		 *
		 * @param  [type] $key   [description]
		 * @param  [type] $value [description]
		 * @return [type]        [description]
		 */
		public function sanitize_field( $key, $value ) {

			$specific_callbacks = apply_filters( 'jet-menu/admin/nav-item-settings/sanitize-callbacks', array(
				'icon_size'    => 'absint',
				'menu_badge'   => 'wp_kses_post',
			) );

			$callback = isset( $specific_callbacks[ $key ] ) ? $specific_callbacks[ $key ] : false;

			if ( ! $callback ) {
				return $value;
			}

			return call_user_func( $callback, $value );
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
 * Returns instance of Jet_Menu_Settings_Nav
 *
 * @return object
 */
function jet_menu_settings_nav() {
	return Jet_Menu_Settings_Nav::get_instance();
}
