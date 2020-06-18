<?php
/**
 * Jet_Search_Ajax_Handlers class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Ajax_Handlers' ) ) {

	/**
	 * Define Jet_Search_Ajax_Handlers class
	 */
	class Jet_Search_Ajax_Handlers {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Ajax_Handlers
		 */
		private static $instance = null;

		/**
		 * Ajax action.
		 *
		 * @var string
		 */
		private $action = 'jet_ajax_search';

		/**
		 * Has navigation.
		 *
		 * @var bool
		 */
		public $has_navigation = false;

		/**
		 * Search query.
		 *
		 * @var array
		 */
		public $search_query = array();

		/**
		 * Table alias.
		 *
		 * @var string
		 */
		private $postmeta_table_alias = 'jetsearch';

		/**
		 * Constructor for the class
		 */
		public function init() {
			// Set search query settings on the search result page
			add_action( 'pre_get_posts', array( $this, 'set_search_query' ) );

			// Search in custom fields
			add_filter( 'posts_join',  array( $this, 'cf_search_join' ) );
			add_filter( 'posts_where', array( $this, 'cf_search_where' ) );

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_action( "wp_ajax_{$this->action}",        array( $this, 'get_search_results' ) );
				add_action( "wp_ajax_nopriv_{$this->action}", array( $this, 'get_search_results' ) );

				add_action( 'wp_ajax_jet_search_get_query_control_options', array( $this, 'get_query_control_options' ) );
			}
		}

		/**
		 * Get ajax action.
		 *
		 * @since  1.1.2
		 * @return string
		 */
		public function get_ajax_action() {
			return $this->action;
		}

		/**
		 * Set search query settings on the search result page.
		 *
		 * @param object $query
		 */
		public function set_search_query( $query ) {
			if ( ! is_admin() && $query->is_search && ! empty( $_GET['jet_ajax_search_settings'] ) ) {
				$form_settings = stripcslashes( $_GET['jet_ajax_search_settings'] );
				$form_settings = json_decode( $form_settings );
				$form_settings = get_object_vars( $form_settings );

				$this->set_query_settings( $form_settings );

				$query->query_vars = array_merge( $query->query_vars, $this->search_query );
			}
		}

		/**
		 * Get search results.
		 */
		public function get_search_results() {

			//if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], $this->action ) ) {
			//	wp_send_json_error( array(
			//		'message' => 'Invalid Nonce!'
			//	) );
			//	return;
			//}

			$data = $this->get_search_data();

			if ( empty( $data ) ) {
				wp_send_json_error( array(
					'message' => 'Empty Search Data'
				) );
				return;
			}

			wp_send_json_success( $data );
		}

		/**
		 * Get search data.
		 *
		 * @return array|bool
		 */
		public function get_search_data() {
			if ( empty( $_GET['data'] ) ) {
				return false;
			}

			$data                                      = $_GET['data'];
			$this->search_query['s']                   = urldecode( $data['value'] );
			$this->search_query['nopaging']            = false;
			$this->search_query['ignore_sticky_posts'] = false;
			$this->search_query['posts_per_page']      = ( int ) $data['limit_query_in_result_area'];
			$this->search_query['post_status']         = 'publish';

			$this->set_query_settings( $data );

			add_filter( 'wp_query_search_exclusion_prefix', '__return_empty_string' );

			$search   = new WP_Query( $this->search_query );
			$response = array(
				'error'      => false,
				'post_count' => 0,
				'message'    => '',
				'posts'      => null,
			);

			remove_filter( 'wp_query_search_exclusion_prefix', '__return_empty_string' );

			if ( is_wp_error( $search ) ) {
				$response['error']   = true;
				$response['message'] = esc_html( $data['server_error'] );

				return $response;
			}

			if ( empty( $search->post_count ) ) {
				$response['message'] = esc_html( $data['negative_search'] );

				return $response;
			}

			$data['limit_query'] = $this->extract_limit_query( $data );

			$data['post_count'] = $search->post_count;
			$data['columns']    = ceil( $data['post_count'] / $data['limit_query'] );

			$response['posts']              = array();
			$response['columns']            = $data['columns'];
			$response['limit_query']        = $data['limit_query'];
			$response['post_count']         = $data['post_count'];
			$response['results_navigation'] = $this->get_results_navigation( $data );

			foreach ( $search->posts as $key => $post ) {

				$response['posts'][ $key ] = array(
					'title'          => $post->post_title,
					'before_title'   => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'title_related', 'jet-search-title-fields', array( 'before' ) ),
					'after_title'    => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'title_related', 'jet-search-title-fields', array( 'after' ) ),
					'content'        => Jet_Search_Template_Functions::get_post_content( $data, $post ),
					'before_content' => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'content_related', 'jet-search-content-fields', array( 'before' ) ),
					'after_content'  => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'content_related', 'jet-search-content-fields', array( 'after' ) ),
					'thumbnail'      => Jet_Search_Template_Functions::get_post_thumbnail( $data, $post ),
					'link'           => esc_url( get_permalink( $post->ID ) ),
					'price'          => Jet_Search_Template_Functions::get_product_price( $data, $post ),
					'rating'         => Jet_Search_Template_Functions::get_product_rating( $data, $post ),
				);

				$custom_post_data = apply_filters( 'jet-search/ajax-search/custom-post-data', array(), $data, $post );

				if ( ! empty( $custom_post_data ) ) {
					$response['posts'][ $key ] = array_merge( $response['posts'][ $key ], $custom_post_data );
				}

				if ( ! $this->has_navigation && $key === $data['limit_query'] - 1 ) {
					break;
				}
			}

			return $response;
		}

		/**
		 * Set search query settings.
		 *
		 * @param array $args
		 */
		protected function set_query_settings( $args = array() ) {
			if ( $args ) {
				$this->search_query['cache_results'] = true;
				$this->search_query['post_type']     = $args['search_source'];
				$this->search_query['order']         = $args['results_order'];
				$this->search_query['orderby']       = $args['results_order_by'];
				$this->search_query['tax_query']     = array( 'relation' => 'AND' );
				$this->search_query['sentence']      = isset( $args['sentence'] ) ? filter_var( $args['sentence'], FILTER_VALIDATE_BOOLEAN ) : false;

				// Include specific terms
				if ( ! empty( $args['category__in'] ) ) {
					$tax = ! empty( $args['search_taxonomy'] ) ? $args['search_taxonomy'] : 'category';

					array_push(
						$this->search_query['tax_query'],
						array(
							'taxonomy' => $tax,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $args['category__in'],
						)
					);
				} else if ( ! empty( $args['include_terms_ids'] ) ) {

					$include_tax_query = array( 'relation' => 'OR' );
					$terms_data        = $this->prepare_terms_data( $args['include_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$include_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $terms_ids,
						);
					}

					array_push(
						$this->search_query['tax_query'],
						$include_tax_query
					);
				}

				// Exclude specific terms
				if ( ! empty( $args['exclude_terms_ids'] ) ) {

					$exclude_tax_query = array( 'relation' => 'OR' );
					$terms_data        = $this->prepare_terms_data( $args['exclude_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$exclude_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'NOT IN',
							'terms'    => $terms_ids,
						);
					}

					array_push(
						$this->search_query['tax_query'],
						$exclude_tax_query
					);
				}

				// Exclude specific posts
				if ( ! empty( $args['exclude_posts_ids'] ) ) {
					$this->search_query['post__not_in'] = $args['exclude_posts_ids'];
				}

				// Current Query
				if ( ! empty( $args['current_query'] ) ) {
					$this->search_query = array_merge( $this->search_query, $args['current_query'] );
				}
			}
		}

		/**
		 * Get Query control options list.
		 *
		 * @since  2.0.0
		 * @return void
		 */
		function get_query_control_options() {

			$data = $_REQUEST;

			if ( ! isset( $data['query_type'] ) ) {
				wp_send_json_error();
				return;
			}

			$results = array();

			switch ( $data['query_type'] ) {
				case 'terms':

					$terms_args = array(
						'hide_empty' => false,
					);

					if ( ! empty( $data['q'] ) ) {
						$terms_args['search'] = $data['q'];
					}

					if ( ! empty( $data['post_type'] ) ) {
						$terms_args['taxonomy'] = get_object_taxonomies( $data['post_type'], 'names' );
					} else {
						$terms_args['taxonomy'] = get_taxonomies( array( 'show_in_nav_menus' => true ), 'names' );
					}

					if ( ! empty( $data['ids'] ) ) {
						$terms_args['include'] = $data['ids'];
					}

					$terms = get_terms( $terms_args );

					global $wp_taxonomies;

					foreach ( $terms as $term ) {

						$results[] = array(
							'id'   => $term->term_id,
							'text' => sprintf( '%1$s: %2$s', $wp_taxonomies[ $term->taxonomy ]->label, $term->name ),
						);
					}

					break;

				case 'posts':

					$query_args = array(
						'post_type'           => 'any',
						'posts_per_page'      => - 1,
						'suppress_filters'    => false,
						'ignore_sticky_posts' => true,
					);

					if ( ! empty( $data['q'] ) ) {
						$query_args['s_title'] = $data['q'];
						$query_args['orderby'] = 'relevance';
					}

					if ( ! empty( $data['post_type'] ) ) {
						$query_args['post_type'] = $data['post_type'];
					}

					if ( ! empty( $data['ids'] ) ) {
						$query_args['post__in'] = $data['ids'];
					}

					add_filter( 'posts_where', array( $this, 'force_search_by_title' ), 10, 2 );

					$posts = get_posts( $query_args );

					remove_filter( 'posts_where', array( $this, 'force_search_by_title' ), 10 );

					foreach ( $posts as $post ) {
						$results[] = array(
							'id'   => $post->ID,
							'text' => sprintf( '%1$s: %2$s', ucfirst( $post->post_type ), $post->post_title ),
						);
					}

					break;
			}

			$data = array(
				'results' => $results,
			);

			wp_send_json_success( $data );
		}

		/**
		 * Force query to look in post title while searching.
		 *
		 * @since  2.0.0
		 * @param  string $where
		 * @param  object $query
		 * @return string
		 */
		public function force_search_by_title( $where, $query ) {

			$args = $query->query;

			if ( ! isset( $args['s_title'] ) ) {
				return $where;
			}

			global $wpdb;

			$search = esc_sql( $wpdb->esc_like( $args['s_title'] ) );
			$where .= " AND {$wpdb->posts}.post_title LIKE '%$search%'";

			return $where;
		}

		/**
		 * Prepare terms data for tax query
		 *
		 * @since  2.0.0
		 * @param  array $terms_ids
		 * @return array
		 */
		public function prepare_terms_data( $terms_ids = array() ) {

			$result = array();

			foreach ( $terms_ids as $term_id ) {
				$term     = get_term( $term_id );
				$taxonomy = $term->taxonomy;

				$result[ $taxonomy ][] = $term_id;
			}

			return $result;
		}

		/**
		 * Is ajax search request
		 *
		 * @since  2.0.0
		 * @return bool
		 */
		public function is_ajax_search_request() {

			if ( ( isset( $_GET['action'] ) && $this->action === $_GET['action'] && ! empty( $_GET['data'] ) )
				|| ( is_search() && ! empty( $_GET['jet_ajax_search_settings'] ) )
			) {
				return true;
			}

			return false;
		}

		/**
		 * Get custom fields keys for search
		 *
		 * @since  2.0.0
		 * @return array|bool
		 */
		public function get_cf_search_keys() {

			$cf_source = false;

			if ( ! empty( $_GET['data']['custom_fields_source'] ) ) {
				$cf_source = $_GET['data']['custom_fields_source'];

			} elseif ( ! empty( $_GET['jet_ajax_search_settings'] ) ) {
				$settings  = stripcslashes( $_GET['jet_ajax_search_settings'] );
				$settings  = json_decode( $settings, true );
				$cf_source = ! empty( $settings['custom_fields_source'] ) ? $settings['custom_fields_source'] : false;
			}

			if ( empty( $cf_source ) ) {
				return false;
			}

			$cf_keys = explode( ',', str_replace( ' ', '', $cf_source ) );

			return $cf_keys;
		}

		/**
		 * Modify the JOIN clause of the query.
		 *
		 * @since  2.0.0
		 * @param  string $join
		 * @return string
		 */
		public function cf_search_join( $join ) {

			if ( ! $this->is_ajax_search_request() || ! $this->get_cf_search_keys() ) {
				return $join;
			}

			global $wpdb;

			$join .= " LEFT JOIN {$wpdb->postmeta} {$this->postmeta_table_alias} ON {$wpdb->posts}.ID = {$this->postmeta_table_alias}.post_id ";

			return $join;
		}

		/**
		 * Modify the WHERE clause of the query.
		 *
		 * @since  2.0.0
		 * @param  string $where
		 * @return string
		 */
		public function cf_search_where( $where ) {

			if ( ! $this->is_ajax_search_request() || ! $this->get_cf_search_keys() ) {
				return $where;
			}

			global $wpdb;

			$cf_where = '';
			$cf_keys  = $this->get_cf_search_keys();

			$or_op = '';

			foreach ( $cf_keys as $cf_key ) {
				$cf_where .= "{$or_op}({$this->postmeta_table_alias}.meta_key = '{$cf_key}' AND {$this->postmeta_table_alias}.meta_value LIKE $1)";
				$or_op = ' OR ';
			}

			$where = preg_replace(
				"/\(\s*{$wpdb->posts}.post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"({$wpdb->posts}.post_content LIKE $1) OR {$cf_where}", $where );

			return $where;
		}

		/**
		 * Extract limit query from data array.
		 *
		 * @since  2.0.0
		 * @param  array $data
		 * @return int
		 */
		public function extract_limit_query( $data ) {
			$limit_query = ! empty( $data['limit_query'] ) ? $data['limit_query'] : 5;

			if ( empty( $data['deviceMode'] ) ) {
				return $limit_query;
			}

			$limit_query_tablet = ! empty( $data['limit_query_tablet'] ) ? $data['limit_query_tablet'] : $limit_query;
			$limit_query_mobile = ! empty( $data['limit_query_mobile'] ) ? $data['limit_query_mobile'] : $limit_query_tablet;

			switch ( $data['deviceMode'] ) {
				case 'tablet':
					$limit_query = $limit_query_tablet;
					break;

				case 'mobile':
					$limit_query = $limit_query_mobile;
					break;
			}

			return $limit_query;
		}

		/**
		 * Return result area navigation.
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		public function get_results_navigation( $settings = array() ) {
			$navigation_container_html = apply_filters(
				'jet-search/ajax-search/navigation-container-html',
				'<div class="jet-ajax-search__navigation-container">%s</div>'
			);

			$navigation_types = apply_filters(
				'jet-search/ajax-search/navigation-types',
				array( 'bullet_pagination', 'number_pagination', 'navigation_arrows' )
			);

			$header_navigation = '';
			$footer_navigation = '';

			if ( $settings['limit_query'] < $settings['post_count'] ) {

				foreach ( $navigation_types as $type ) {
					if ( ! isset( $settings[ $type ] ) ) {
						continue;
					}

					if ( ! $settings[ $type ] ) {
						continue;
					}

					$buttons = $this->get_navigation_buttons_html( $settings, $type );

					if ( empty( $buttons ) ) {
						continue;
					}

					$this->has_navigation = true;

					switch ( $settings[ $type ] ) {
						case 'in_header':
							$header_navigation .= sprintf( $navigation_container_html, $buttons );
							break;

						case 'in_footer':
							$footer_navigation .= sprintf( $navigation_container_html, $buttons );
							break;

						case 'both':
							$header_navigation .= sprintf( $navigation_container_html, $buttons );
							$footer_navigation .= sprintf( $navigation_container_html, $buttons );
							break;
					}
				}
			}

			return array(
				'in_header' => $header_navigation,
				'in_footer' => $footer_navigation,
			);
		}

		/**
		 * Get results navigation buttons html.
		 *
		 * @param array  $settings
		 * @param string $type
		 *
		 * @return string
		 */
		public function get_navigation_buttons_html( $settings = array(), $type = 'bullet_pagination' ) {
			$output_html = '';
			$bullet_html = apply_filters( 'jet-search/ajax-search/navigate-button-html', '<button class="jet-ajax-search__navigate-button %1$s" data-number="%2$s"></button>' );

			switch ( $type ) {
				case 'bullet_pagination':
					$button_class = 'jet-ajax-search__bullet-button';

				case 'number_pagination':
					$button_class = isset( $button_class ) ? $button_class : 'jet-ajax-search__number-button';

					for ( $i = 0; $i < $settings['columns']; $i++ ) {
						$active_button_class = ( $i === 0 ) ? ' jet-ajax-search__active-button' : '' ;
						$output_html .= sprintf( $bullet_html, $button_class . $active_button_class, $i + 1 );
					}
					break;

				case 'navigation_arrows':
					$prev_button = apply_filters( 'jet-search/ajax-search/prev-button-html', '<button class="jet-ajax-search__prev-button jet-ajax-search__arrow-button jet-ajax-search__navigate-button jet-ajax-search__navigate-button-disable %s" data-direction="-1"></button>' );
					$next_button = apply_filters( 'jet-search/ajax-search/next-button-html', '<button class="jet-ajax-search__next-button jet-ajax-search__arrow-button jet-ajax-search__navigate-button %s" data-direction="1"></button>' );
					$arrow       = Jet_Search_Tools::prepare_arrow( $settings['navigation_arrows_type'] );
					$output_html = sprintf( $prev_button . $next_button, esc_attr( $arrow ), esc_attr( $arrow ) );
					break;
			}

			return $output_html;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Ajax_Handlers
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
 * Returns instance of Jet_Search_Ajax_Handlers
 *
 * @return Jet_Search_Ajax_Handlers
 */
function jet_search_ajax_handlers() {
	return Jet_Search_Ajax_Handlers::get_instance();
}
