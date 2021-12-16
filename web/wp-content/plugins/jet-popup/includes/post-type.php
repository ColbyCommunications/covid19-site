<?php
/**
 * JetPopup post type template
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Popup_Post_Type' ) ) {

	/**
	 * Define Jet_Popup_Post_Type class
	 */
	class Jet_Popup_Post_Type {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * [$post_type description]
		 * @var string
		 */
		protected $post_type = 'jet-popup';

		/**
		 * [$meta_key description]
		 * @var string
		 */
		protected $meta_key = 'jet-popup-item';

		/**
		 * Site conditions
		 * @var array
		 */
		private $conditions = array();

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			self::register_post_type();

			add_filter( 'option_elementor_cpt_support', [ $this, 'set_option_support' ] );

			add_filter( 'default_option_elementor_cpt_support', [ $this, 'set_option_support' ] );

			add_action( 'elementor/documents/register', [ $this, 'register_elementor_document_type' ] );

			add_action( 'wp_insert_post', [ $this, 'set_document_type_on_post_create' ], 10, 2 );

			add_action( 'template_include', [ $this, 'set_post_type_template' ], 9999 );

			add_filter( 'manage_' . $this->slug() . '_posts_columns', [ $this, 'set_post_columns' ] );

			add_action( 'manage_' . $this->slug() . '_posts_custom_column', [ $this, 'post_columns' ], 10, 2 );
		}

		/**
		 * Set required post columns
		 *
		 * @param [type] $columns [description]
		 */
		public function set_post_columns( $columns ) {

			unset( $columns['date'] );

			$columns['conditions'] = __( 'Active Conditions', 'jet-popup' );
			$columns['date']       = __( 'Date', 'jet-popup' );

			return $columns;
		}

		/**
		 * Manage post columns content
		 *
		 * @return [type] [description]
		 */
		public function post_columns( $column, $post_id ) {

			$all_conditions = jet_popup()->conditions->get_site_conditions();

			switch ( $column ) {

				case 'conditions':

					echo '<div class="jet-popup-conditions">';

					if ( isset( $all_conditions[ 'jet-popup' ] ) ) {

						if ( ! empty( $all_conditions[ 'jet-popup' ][ $post_id ] ) ) {

							printf(
								'<div class="jet-popup-conditions-list">%1$s</div>',
								jet_popup()->conditions->post_conditions_verbose( $post_id )
							);
						} else {
							printf(
								'<div class="jet-popup-conditions-undefined"><span class="dashicons dashicons-warning"></span>%1$s</div>',
								__( 'Conditions not selected', 'jet-popup' )
							);

						}
					} else {
						printf(
							'<div class="jet-popup-conditions-undefined"><span class="dashicons dashicons-warning"></span>%1$s</div>',
							__( 'Conditions not selected', 'jet-popup' )
						);
					}

					echo '</div>';

					break;
			}
		}

		/**
		 * Set apropriate document type on post creation
		 *
		 * @param int     $post_id Created post ID.
		 * @param WP_Post $post    Created post object.
		 */
		public function set_document_type_on_post_create( $post_id, $post ) {

			if ( $post->post_type !== $this->slug() ) {
				return;
			}

			if ( ! class_exists( 'Elementor\Plugin' ) ) {
				return;
			}

			$documents = Elementor\Plugin::instance()->documents;
			$doc_type  = $documents->get_document_type( $this->slug() );

			update_post_meta( $post_id, $doc_type::TYPE_META_KEY, $this->slug() );
		}

		/**
		 * Register apropriate document type for 'jet-woo-builder' post type
		 *
		 * @param  Elementor\Core\Documents_Manager $documents_manager [description]
		 * @return void
		 */
		public function register_elementor_document_type( $documents_manager ) {
			require jet_popup()->plugin_path( 'includes/document-types/document.php' );
			require jet_popup()->plugin_path( 'includes/document-types/not-supported.php' );

			$documents_manager->register_document_type( $this->slug(), 'Jet_Popup_Document' );
			$documents_manager->register_document_type( $this->slug() . '-not-supported', 'Jet_Popup_Not_Supported' );
		}

		/**
		 * Returns post type slug
		 *
		 * @return string
		 */
		public function slug() {
			return $this->post_type;
		}

		/**
		 * Returns Mega Menu meta key
		 *
		 * @return string
		 */
		public function meta_key() {
			return $this->meta_key;
		}

		/**
		 * Add elementor support for mega menu items.
		 */
		public function set_option_support( $value ) {

			if ( empty( $value ) ) {
				$value = array();
			}

			return array_merge( $value, array( $this->slug() ) );
		}

		/**
		 * Register post type
		 *
		 * @return void
		 */
		static public function register_post_type() {

			$labels = array(
				'name'          => esc_html__( 'JetPopup', 'jet-popup' ),
				'singular_name' => esc_html__( 'JetPopup', 'jet-popup' ),
				'all_items'     => esc_html__( 'All Popups', 'jet-popup' ),
				'add_new'       => esc_html__( 'Create New Popup', 'jet-popup' ),
				'add_new_item'  => esc_html__( 'Create New Popup', 'jet-popup' ),
				'edit_item'     => esc_html__( 'Edit Popup', 'jet-popup' ),
				'menu_name'     => esc_html__( 'JetPopup', 'jet-popup' ),
			);

			$supports = apply_filters( 'jet-popups/post-type/register/supports', [ 'title' ] );

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'description',
				'taxonomies'          => [],
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 101,
				'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20 1H4C2.34315 1 1 2.34315 1 4V20C1 21.6569 2.34315 23 4 23H20C21.6569 23 23 21.6569 23 20V4C23 2.34315 21.6569 1 20 1ZM4 0C1.79086 0 0 1.79086 0 4V20C0 22.2091 1.79086 24 4 24H20C22.2091 24 24 22.2091 24 20V4C24 1.79086 22.2091 0 20 0H4Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M21.6293 6.00066C21.9402 5.98148 22.1176 6.38578 21.911 6.64277L20.0722 8.93035C19.8569 9.19824 19.4556 9.02698 19.4598 8.669L19.4708 7.74084C19.4722 7.61923 19.4216 7.50398 19.3343 7.42975L18.6676 6.86321C18.4105 6.6447 18.5378 6.19134 18.8619 6.17135L21.6293 6.00066ZM6.99835 12.008C6.99835 14.1993 5.20706 15.9751 2.99967 15.9751C2.44655 15.9751 2 15.5293 2 14.9827C2 14.4361 2.44655 13.9928 2.99967 13.9928C4.10336 13.9928 4.99901 13.1036 4.99901 12.008V9.03323C4.99901 8.48413 5.44556 8.04082 5.99868 8.04082C6.55179 8.04082 6.99835 8.48413 6.99835 9.03323V12.008ZM17.7765 12.008C17.7765 13.1036 18.6721 13.9928 19.7758 13.9928C20.329 13.9928 20.7755 14.4336 20.7755 14.9827C20.7755 15.5318 20.329 15.9751 19.7758 15.9751C17.5684 15.9751 15.7772 14.1993 15.7772 12.008V9.03323C15.7772 8.48413 16.2237 8.04082 16.7768 8.04082C17.33 8.04082 17.7765 8.48665 17.7765 9.03323V9.92237H18.5707C19.1238 9.92237 19.5729 10.3682 19.5729 10.9173C19.5729 11.4664 19.1238 11.9122 18.5707 11.9122H17.7765V12.008ZM15.2038 10.6176C15.2063 10.6151 15.2088 10.6151 15.2088 10.6151C14.8942 9.79393 14.3056 9.07355 13.4835 8.60001C11.5755 7.50181 9.13979 8.15166 8.04117 10.0508C6.94001 11.9475 7.59462 14.3731 9.50008 15.4688C10.9032 16.2749 12.593 16.1338 13.8261 15.2472L13.8184 15.2371C14.1026 15.0633 14.2904 14.751 14.2904 14.3958C14.2904 13.8492 13.8438 13.4059 13.2932 13.4059C13.0268 13.4059 12.7833 13.5092 12.6057 13.6805C12.0069 14.081 11.2102 14.1439 10.5378 13.7762L14.5644 11.9198C14.7978 11.8493 15.0059 11.6931 15.1353 11.4664C15.2926 11.1969 15.3078 10.8871 15.2038 10.6176ZM12.4864 10.3153C12.6057 10.3833 12.7122 10.4614 12.8112 10.5471L9.49754 12.0709C9.48993 11.7208 9.5762 11.3657 9.76395 11.0407C10.3145 10.0937 11.5324 9.76874 12.4864 10.3153Z" fill="#24292D"/></svg>'),
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'capability_type'     => 'post',
				'supports'            => $supports,
			);

			register_post_type( 'jet-popup', $args );

		}

		/**
		 * Set blank template for editor
		 */
		public function set_post_type_template( $template ) {

			if ( is_singular( $this->slug() ) ) {

				$template = jet_popup()->plugin_path( 'templates/single.php' );

				if ( jet_popup()->elementor()->preview->is_preview_mode() ) {
					$template = jet_popup()->plugin_path( 'templates/editor.php' );
				}

				do_action( 'jet-popups/template-include/found' );

				return $template;
			}

			return $template;
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
