<?php
/**
 * Option page Class
 */

// If class `Popups_Options_Page` doesn't exists yet.
if ( ! class_exists( 'Jet_Menu_Options_Presets' ) ) {

	/**
	 * Jet_Menu_Options_Presets class.
	 */
	class Jet_Menu_Options_Presets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Post type name.
		 *
		 * @var string
		 */
		public $post_type = 'jet_options_preset';

		/**
		 * [$settings_key description]
		 * @var string
		 */
		public $settings_key = 'jet_preset_settings';

		/**
		 * [$title_key description]
		 * @var string
		 */
		public $title_key    = 'jet_preset_name';

		/**
		 * Preset list
		 *
		 * @var null
		 */
		public $presets = null;

		/**
		 * Attach hooks
		 */
		public function init() {

			add_action( 'init', array( $this, 'register_post_type' ) );

			add_action( 'jet-menu/options-page/before-render', array( $this, 'register_presets_settings' ), 10, 2 );

			add_action( 'jet-menu/widgets/mega-menu/controls', array( $this, 'add_widget_settings' ) );

			add_action( 'wp_ajax_jet_menu_create_preset', array( $this, 'create_preset' ) );

			add_action( 'wp_ajax_jet_menu_update_preset', array( $this, 'update_preset' ) );

			add_action( 'wp_ajax_jet_menu_load_preset', array( $this, 'load_preset' ) );

			add_action( 'wp_ajax_jet_menu_delete_preset', array( $this, 'delete_preset' ) );

		}

		/**
		 * Add widget settings
		 *
		 * @param object $widget Widget instance.
		 */
		public function add_widget_settings( $widget ) {

			$presets = $this->get_presets();

			if ( empty( $presets ) ) {
				return;
			}

			$presets = array( '0' => esc_html__( 'Not Selected', 'jet-menu' ) ) + $presets;

			$widget->add_control(
				'preset',
				array(
					'label'   => esc_html__( 'Menu Preset', 'jet-menu' ),
					'type'    => Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $presets,
				)
			);

		}

		/**
		 * Register post type
		 *
		 * @return void
		 */
		public function register_post_type() {

			register_post_type( $this->post_type, array(
				'public'      => false,
				'has_archive' => false,
				'rewrite'     => false,
				'can_export'  => true,
			) );

		}

		/**
		 * Create preset callback.
		 *
		 * @return void
		 */
		public function create_preset() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$name     = isset( $_POST['name'] ) ? esc_attr( $_POST['name'] ) : false;
			$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : false;

			if ( ! $settings ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Settings not provided', 'jet-menu' ),
				) );
			}

			if ( ! $name ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Please, specify preset name', 'jet-menu' ),
				) );
			}

			$post_title = 'jet_preset_' . md5( $name );

			if ( post_exists( $post_title ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Preset with the same name already exists, please change it', 'jet-menu' ),
				) );
			}

			$preset_id = wp_insert_post( array(
				'post_type'   => $this->post_type,
				'post_status' => 'publish',
				'post_title'  => $post_title,
				'meta_input'  => array(
					$this->title_key    => esc_attr( $name ),
					$this->settings_key => $settings,
				),
			) );

			do_action( 'jet-menu/presets/created' );

			wp_send_json_success( array(
				'message' => esc_html__( 'Settings preset have been created', 'jet-menu' ),
				'preset' => array(
					'id'      => $preset_id,
					'name'    => esc_attr( $name ),
				),
				'presets' => $this->get_presets_select_options(),
			) );

		}

		/**
		 * Update preset callback.
		 *
		 * @return void
		 */
		public function update_preset() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$preset   = isset( $_POST['preset'] ) ? absint( $_POST['preset'] ) : false;
			$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : false;

			if ( ! $preset ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Preset ID not defined', 'jet-menu' ),
				) );
			}

			update_post_meta( $preset, $this->settings_key, $settings );

			do_action( 'jet-menu/presets/updated' );

			wp_send_json_success( array(
				'message' => esc_html__( 'Preset have been updated', 'jet-menu' ),
			) );
		}

		/**
		 * Load preset callback.
		 *
		 * @return void
		 */
		public function load_preset() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$preset = isset( $_POST['preset'] ) ? absint( $_POST['preset'] ) : false;

			if ( ! $preset ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Preset ID not defined', 'jet-menu' ),
				) );
			}

			$preset_settings = get_post_meta( $preset, $this->settings_key, true );

			update_option( jet_menu_option_page()->options_slug(), $preset_settings );

			do_action( 'jet-menu/presets/loaded' );

			wp_send_json_success( array(
				'message'  => esc_html__( 'Preset have been applyed', 'jet-menu' ),
			) );
		}

		/**
		 * Delete preset callback
		 *
		 * @return void
		 */
		public function delete_preset() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'You are not allowed to do this', 'jet-menu' ),
				) );
			}

			$preset = isset( $_POST['preset'] ) ? absint( $_POST['preset'] ) : false;

			if ( ! $preset ) {
				wp_send_json_error( array(
					'message' => esc_html__( 'Preset ID not defined', 'jet-menu' ),
				) );
			}

			wp_delete_post( $preset, true );

			do_action( 'jet-menu/presets/deleted' );

			wp_send_json_success( array(
				'message' => esc_html__( 'Preset have been removing', 'jet-menu' ),
				'presets' => $this->get_presets_select_options(),
			) );
		}

		/**
		 * Register presets settings
		 *
		 * @param  object $builder      Builder instance.
		 * @param  object $options_page Options page instance.
		 * @return void
		 */
		public function register_presets_settings( $builder, $options_page ) {

			ob_start();
			include jet_menu()->get_template( 'admin/presets-controls.php' );
			$controls = ob_get_clean();

			$builder->register_control(
				array(
					'jet-presets-controls' => array(
						'type'   => 'html',
						'parent' => 'presets_tab',
						'class'  => 'jet-menu-presets',
						'html'   => $controls,
					),
				)
			);

		}

		/**
		 * Get presets list
		 *
		 * @return array
		 */
		public function get_presets() {

			if ( null !== $this->presets ) {
				return $this->presets;
			}

			$presets = get_posts( array(
				'post_type'      => $this->post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			) );

			if ( empty( $presets ) ) {
				$this->presets = array();
				return $this->presets;
			}

			$result = array();

			foreach ( $presets as $preset ) {
				$result[ $preset->ID ] = get_post_meta( $preset->ID, $this->title_key, true );
			}

			$this->presets = $result;

			return $this->presets;

		}

		/**
		 * [get_presets_select_options description]
		 * @return [type] [description]
		 */
		public function get_presets_select_options() {

			$presets = $this->get_presets();

			$preset_select_options = [];

			if ( ! empty( $presets ) ) {

				$preset_select_options[] = array(
					'label' => esc_html( 'None', 'jet-menu' ),
					'value' => '',
				);

				foreach ( $presets as $preset_slug => $preset_name ) {
					$preset_select_options[] = array(
						'label' => $preset_name,
						'value' => $preset_slug,
					);
				}
			}

			return $preset_select_options;
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

if ( ! function_exists( 'jet_menu_options_presets' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function jet_menu_options_presets() {
		return Jet_Menu_Options_Presets::get_instance();
	}
}
