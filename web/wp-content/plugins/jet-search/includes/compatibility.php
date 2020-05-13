<?php
/**
 * Jet_Search_Compatibility class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Compatibility' ) ) {

	/**
	 * Define Jet_Search_Compatibility class
	 */
	class Jet_Search_Compatibility {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Compatibility
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {
			// WPML Compatibility
			if ( defined( 'WPML_ST_VERSION' ) ) {
				add_filter( 'wpml_elementor_widgets_to_translate', array( $this, 'add_wpml_translatable_nodes' ) );
			}

			// Polylang Compatibility
			if ( class_exists( 'Polylang' ) ) {
				add_filter( 'pll_home_url_white_list', array( $this, 'modify_pll_home_url_white_list' ) );
			}
		}

		/**
		 * Add wpml translation nodes
		 *
		 * @param array $nodes_to_translate
		 *
		 * @return array
		 */
		public function add_wpml_translatable_nodes( $nodes_to_translate ) {

			$nodes_to_translate['jet-ajax-search'] = array(
				'conditions' => array( 'widgetType' => 'jet-ajax-search' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder_text',
						'type'        => esc_html__( 'Jet Ajax Search: Placeholder Text', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'search_submit_label',
						'type'        => esc_html__( 'Jet Ajax Search: Submit Button Label', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'search_category_select_placeholder',
						'type'        => esc_html__( 'Jet Ajax Search: Select Placeholder', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'results_counter_text',
						'type'        => esc_html__( 'Jet Ajax Search: Results Counter Text', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'full_results_btn_text',
						'type'        => esc_html__( 'Jet Ajax Search: Full Results Button Text', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'negative_search',
						'type'        => esc_html__( 'Jet Ajax Search: Negative search results', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'server_error',
						'type'        => esc_html__( 'Jet Ajax Search: Technical error', 'jet-search' ),
						'editor_type' => 'LINE',
					),
				),
			);

			return $nodes_to_translate;
		}

		/**
		 * Modify the white list of the Polylang 'home_url' filter
		 *
		 * @since  1.1.0
		 * @param  array $list
		 * @return array
		 */
		public function modify_pll_home_url_white_list( $list = array() ) {

			$template_path = jet_search()->plugin_path( 'templates/jet-ajax-search' );
			$template_path = ( false === strpos( $template_path, '\\' ) ) ? $template_path : str_replace( '/', '\\', $template_path );

			$list[] = array(
				'file' => $template_path,
			);

			return $list;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Compatibility
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
 * Returns instance of Jet_Search_Compatibility
 *
 * @return Jet_Search_Compatibility
 */
function jet_search_compatibility() {
	return Jet_Search_Compatibility::get_instance();
}
