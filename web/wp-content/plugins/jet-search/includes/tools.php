<?php
/**
 * Jet_Search_Tools class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Tools' ) ) {

	/**
	 * Define Jet_Search_Tools class
	 */
	class Jet_Search_Tools {

		/**
		 * Get public post types options list
		 *
		 * @return array
		 */
		public static function get_post_types() {
			$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );

			$result = array();

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $post_type ) {
				$result[ $slug ] = $post_type->label;
			}

			return $result;
		}

		/**
		 * Get public taxonomies options list
		 *
		 * @return array
		 */
		public static function get_taxonomies() {
			$taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'objects' );

			$result = array();

			if ( empty( $taxonomies ) ) {
				return $result;
			}

			foreach ( $taxonomies as $slug => $post_type ) {
				$result[ $slug ] = $post_type->label;
			}

			return $result;
		}

		/**
		 * Returns image size array in slug => name format
		 *
		 * @return  array
		 */
		public static function get_image_sizes() {

			global $_wp_additional_image_sizes;

			$sizes  = get_intermediate_image_sizes();
			$result = array();

			foreach ( $sizes as $size ) {
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$result[ $size ] = ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) );
				} else {
					$result[ $size ] = sprintf(
						'%1$s (%2$sx%3$s)',
						ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) ),
						$_wp_additional_image_sizes[ $size ]['width'],
						$_wp_additional_image_sizes[ $size ]['height']
					);
				}
			}

			return array_merge( array( 'full' => esc_html__( 'Full', 'jet-search' ), ), $result );
		}

		/**
		 * Return available prev arrows list
		 *
		 * @return array
		 */
		public static function get_available_prev_arrows_list() {
			return apply_filters(
				'jet-search/available-nav-arrows/prev',
				array(
					'fa fa-angle-left'          => esc_html__( 'Angle', 'jet-search' ),
					'fa fa-chevron-left'        => esc_html__( 'Chevron', 'jet-search' ),
					'fa fa-angle-double-left'   => esc_html__( 'Angle Double', 'jet-search' ),
					'fa fa-arrow-left'          => esc_html__( 'Arrow', 'jet-search' ),
					'fa fa-caret-left'          => esc_html__( 'Caret', 'jet-search' ),
					'fa fa-long-arrow-left'     => esc_html__( 'Long Arrow', 'jet-search' ),
					'fa fa-arrow-circle-left'   => esc_html__( 'Arrow Circle', 'jet-search' ),
					'fa fa-chevron-circle-left' => esc_html__( 'Chevron Circle', 'jet-search' ),
					'fa fa-caret-square-o-left' => esc_html__( 'Caret Square', 'jet-search' ),
				)
			);
		}

		/**
		 * Return available prev arrows list
		 *
		 * @return array
		 */
		public static function get_available_next_arrows_list() {
			return apply_filters(
				'jet-search/available-nav-arrows/next',
				array(
					'fa fa-angle-right'          => esc_html__( 'Angle', 'jet-search' ),
					'fa fa-chevron-right'        => esc_html__( 'Chevron', 'jet-search' ),
					'fa fa-angle-double-right'   => esc_html__( 'Angle Double', 'jet-search' ),
					'fa fa-arrow-right'          => esc_html__( 'Arrow', 'jet-search' ),
					'fa fa-caret-right'          => esc_html__( 'Caret', 'jet-search' ),
					'fa fa-long-arrow-right'     => esc_html__( 'Long Arrow', 'jet-search' ),
					'fa fa-arrow-circle-right'   => esc_html__( 'Arrow Circle', 'jet-search' ),
					'fa fa-chevron-circle-right' => esc_html__( 'Chevron Circle', 'jet-search' ),
					'fa fa-caret-square-o-right' => esc_html__( 'Caret Square', 'jet-search' ),
				)
			);
		}

		/**
		 * Is FA5 migration.
		 *
		 * @return bool
		 */
		public static function is_fa5_migration() {

			if ( defined( 'ELEMENTOR_VERSION' )
				&& version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' )
				&& Elementor\Icons_Manager::is_migration_allowed()
			) {
				return true;
			}

			return false;
		}

		/**
		 * FA5 arrows map.
		 *
		 * @return array
		 */
		public static function get_fa5_arrows_map() {
			return apply_filters(
				'jet-search/fa5_arrows_map',
				array(
					'fa fa-angle-left'           => 'fas fa-angle-left',
					'fa fa-chevron-left'         => 'fas fa-chevron-left',
					'fa fa-angle-double-left'    => 'fas fa-angle-double-left',
					'fa fa-arrow-left'           => 'fas fa-arrow-left',
					'fa fa-caret-left'           => 'fas fa-caret-left',
					'fa fa-long-arrow-left'      => 'fas fa-long-arrow-alt-left',
					'fa fa-arrow-circle-left'    => 'fas fa-arrow-circle-left',
					'fa fa-chevron-circle-left'  => 'fas fa-chevron-circle-left',
					'fa fa-caret-square-o-left'  => 'fas fa-caret-square-left',

					'fa fa-angle-right'          => 'fas fa-angle-right',
					'fa fa-chevron-right'        => 'fas fa-chevron-right',
					'fa fa-angle-double-right'   => 'fas fa-angle-double-right',
					'fa fa-arrow-right'          => 'fas fa-arrow-right',
					'fa fa-caret-right'          => 'fas fa-caret-right',
					'fa fa-long-arrow-right'     => 'fas fa-long-arrow-alt-right',
					'fa fa-arrow-circle-right'   => 'fas fa-arrow-circle-right',
					'fa fa-chevron-circle-right' => 'fas fa-chevron-circle-right',
					'fa fa-caret-square-o-right' => 'fas fa-caret-square-right',
				)
			);
		}

		/**
		 * Prepare arrow
		 *
		 * @param  string $arrow
		 * @return string
		 */
		public static function prepare_arrow( $arrow ) {

			if ( ! self::is_fa5_migration() ) {
				return $arrow;
			}

			$fa5_arrows_map = self::get_fa5_arrows_map();

			return isset( $fa5_arrows_map[ $arrow ] ) ? $fa5_arrows_map[ $arrow ] : $arrow;
		}

		/**
		 * Check if is valid timestamp
		 *
		 * @param  int|string $timestamp
		 * @return boolean
		 */
		public static function is_valid_timestamp( $timestamp ) {
			return ( ( string ) ( int ) $timestamp === $timestamp ) && ( $timestamp <= PHP_INT_MAX ) && ( $timestamp >= ~PHP_INT_MAX );
		}
	}
}
