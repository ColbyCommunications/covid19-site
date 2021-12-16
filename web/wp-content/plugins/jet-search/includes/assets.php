<?php
/**
 * Jet_Search_Assets class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Assets' ) ) {

	/**
	 * Define Jet_Search_Assets class
	 */
	class Jet_Search_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Assets
		 */
		private static $instance = null;

		/**
		 * Localize data.
		 *
		 * @var array
		 */
		public $localize_data = array();

		/**
		 * Constructor for the class
		 */
		public function init() {
			add_action( 'elementor/frontend/before_register_styles',  array( $this, 'register_styles' ) );
			add_action( 'elementor/frontend/after_enqueue_styles',    array( $this, 'enqueue_styles' ) );

			add_action( 'elementor/frontend/before_register_scripts', array( $this, 'register_scripts' ) );
			add_action( 'elementor/frontend/after_enqueue_scripts',   array( $this, 'enqueue_scripts' ) );

			add_action( 'elementor/editor/before_enqueue_scripts',    array( $this, 'editor_scripts' ) );
			add_action( 'elementor/editor/after_enqueue_styles',      array( $this, 'editor_styles' ) );

			add_action( 'wp_print_footer_scripts', array( $this, 'print_results_item_js_template' ), 0 );
		}

		/**
		 * Register plugin stylesheets.
		 *
		 * @return void
		 */
		public function register_styles() {
			wp_register_style(
				'jquery-chosen',
				jet_search()->plugin_url( 'assets/lib/chosen/chosen.min.css' ),
				false,
				'1.8.7'
			);
		}

		/**
		 * Enqueue plugin stylesheets.
		 *
		 * @return void
		 */
		public function enqueue_styles() {
			wp_enqueue_style(
				'jet-search',
				jet_search()->plugin_url( 'assets/css/jet-search.css' ),
				array(),
				jet_search()->get_version()
			);
		}

		/**
		 * Register plugin scripts
		 *
		 * @return void
		 */
		public function register_scripts() {
			// Register vendor chosen.jquery.min.js script (https://github.com/harvesthq/chosen/)
			wp_register_script(
				'jquery-chosen',
				jet_search()->plugin_url( 'assets/lib/chosen/chosen.jquery.min.js' ),
				array( 'jquery' ),
				'1.8.7',
				true
			);
		}

		/**
		 * Enqueue plugin scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_enqueue_script(
				'jet-search',
				jet_search()->plugin_url( 'assets/js/jet-search.js' ),
				array( 'jquery', 'elementor-frontend', 'wp-util' ),
				jet_search()->get_version(),
				true
			);

			$ajax_action = jet_search_ajax_handlers()->get_ajax_action();

			$this->localize_data['ajaxurl'] = esc_url( admin_url( 'admin-ajax.php' ) );
			$this->localize_data['action']  = $ajax_action;
			$this->localize_data['nonce']   = wp_create_nonce( $ajax_action );

			wp_localize_script( 'jet-search', 'jetSearchSettings', $this->localize_data );
		}

		/**
		 * Enqueue editor scripts
		 */
		public function editor_scripts() {

			wp_enqueue_script(
				'jet-search-editor',
				jet_search()->plugin_url( 'assets/js/jet-search-editor.js' ),
				array( 'jquery' ),
				jet_search()->get_version(),
				true
			);
		}

		/**
		 * Enqueue editor styles
		 *
		 * @return void
		 */
		public function editor_styles() {

			if ( is_rtl() ) {
				wp_enqueue_style(
					'jet-search-editor',
					jet_search()->plugin_url( 'assets/css/jet-search-editor.css' ),
					array(),
					jet_search()->get_version()
				);

				$ui_theme = \Elementor\Core\Settings\Manager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );

				if ( 'dark' === $ui_theme ) {
					wp_add_inline_style( 'jet-search-editor', '.rtl .jet-search-text-align-control{--jet-search-text-align-control-border-color:#64666a}' );
				}
			}

		}

		/**
		 * Print results item js template.
		 */
		public function print_results_item_js_template() {
			if ( ! wp_script_is( 'jet-search', 'enqueued' ) ) {
				return;
			}

			ob_start();
			include jet_search()->get_template( 'jet-ajax-search/global/results-item.php' );
			$content = ob_get_clean();

			if ( empty( $content ) ) {
				return;
			}

			$output = sprintf(
				'<script type="text/html" id="tmpl-jet-ajax-search-results-item">%s</script>',
				$content
			);

			echo $output;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Assets
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
 * Returns instance of Jet_Search_Assets
 *
 * @return Jet_Search_Assets
 */
function jet_search_assets() {
	return Jet_Search_Assets::get_instance();
}
