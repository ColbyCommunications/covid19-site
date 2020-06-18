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

if ( ! class_exists( 'Jet_Menu_Public_Manager' ) ) {

	/**
	 * Define Jet_Menu_Public_Manager class
	 */
	class Jet_Menu_Public_Manager {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * [$raw_menu_data description]
		 * @var array
		 */
		public $raw_menu_data = array();

		/**
		 * Constructor for the class
		 */
		public function init() {

			add_filter( 'wp_nav_menu_args', array( $this, 'set_menu_args' ), 99999 );

			add_filter( 'pre_wp_nav_menu', array( $this, 'modify_pre_wp_nav_menu' ), 10, 2 );

			add_filter( 'walker_nav_menu_start_el', array( $this, 'fix_double_desc' ), 0, 4 );

			add_action( 'jet-menu/blank-page/after-content', array( $this, 'set_menu_canvas_bg' ) );

			add_filter( 'body_class', array( $this, 'modify_body_class' ) );

		}

		/**
		 * Add background from options from menu canvas
		 */
		public function set_menu_canvas_bg() {
			jet_menu_dynmic_css()->add_single_bg_styles( 'jet-menu-sub-panel-mega', 'body' );
		}

		/**
		 * [modify_body_class description]
		 * @param  [type] $classes [description]
		 * @return [type]          [description]
		 */
		public function modify_body_class( $classes ) {

			$classes[] = ! Jet_Menu_Tools::is_phone() ? 'jet-desktop-menu-active' : 'jet-mobile-menu-active';

			return $classes;
		}

		/**
		 * Fix double decription bug.
		 *
		 * @param  string  $item_output The menu item output.
		 * @param  WP_Post $item        Menu item object.
		 * @param  int     $depth       Depth of the menu.
		 * @param  array   $args        wp_nav_menu() arguments.
		 * @return string
		 */
		public function fix_double_desc( $item_output, $item, $depth, $args ) {
			$item->description = '';

			return $item_output;
		}

		/**
		 * Set mega menu arguments
		 *
		 * @param [type] $args [description]
		 */

		public function set_menu_args( $args ) {

			if ( ! isset( $args['theme_location'] ) ) {
				return $args;
			}

			$location = $args['theme_location'];

			$menu_id = $this->get_menu_id( $location );

			if ( false === $menu_id ) {
				return $args;
			}

			$settings = jet_menu_settings_nav()->get_settings( $menu_id );

			$settings = apply_filters( 'jet-menu/public-manager/menu-settings', $settings );
			$location = apply_filters( 'jet-menu/public-manager/menu-location', $location );

			if ( ! isset( $settings[ $location ] ) ) {
				return $args;
			}

			if ( ! isset( $settings[ $location ]['enabled'] ) || 'true' !== $settings[ $location ]['enabled'] ) {
				return $args;
			}

			$preset = isset( $settings[ $location ]['preset'] ) ? absint( $settings[ $location ]['preset'] ) : 0;

			if ( 0 !== $preset ) {
				$preset_options = get_post_meta( $preset, jet_menu_options_presets()->settings_key, true );
				jet_menu_option_page()->pre_set_options( $preset_options );
			} else {
				jet_menu_option_page()->pre_set_options( false );
			}

			$args = array_merge( $args, $this->get_mega_nav_args( $preset ) );

			return $args;

		}

		/**
		 * Returns array ow Mega Mneu attributes for wp_nav_menu() function.
		 *
		 * @return array
		 */
		public function get_mega_nav_args( $preset = 0 ) {
			global $is_iphone;

			// Get animation type for mega menu instance
			$animation_type = jet_menu_option_page()->get_option( 'jet-menu-animation', 'fade' );

			$raw_attributes = apply_filters( 'jet-menu/set-menu-args/', array(
				'class' => array(
					'jet-menu',
					( ! empty( $preset ) ? 'jet-preset-' . $preset : '' ),
					'jet-menu--animation-type-' . $animation_type,
					$is_iphone ? 'jet-menu--iphone-mode' : '',
				),
			) );

			$attributes = '';

			foreach ( $raw_attributes as $name => $value ) {

				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}

				$attributes .= sprintf( ' %1$s="%2$s"', esc_attr( $name ), esc_attr( $value ) );
			}

			$roll_up = jet_menu_option_page()->get_option( 'jet-menu-roll-up', 'false' );

			$args = array(
				'menu_class' => '',
				'items_wrap' => '<div class="jet-menu-container"><div class="jet-menu-inner"><ul' . $attributes . '>%3$s</ul></div></div>',
				'before'     => '',
				'after'      => '',
				'walker'     => new Jet_Menu_Main_Walker(),
				'roll_up'    => filter_var( $roll_up, FILTER_VALIDATE_BOOLEAN ),
			);

			$this->add_dynamic_styles( $preset );

			return $args;

		}

		/**
		 * [modify_pre_wp_nav_menu description]
		 * @param  [type] $args [description]
		 * @return [type]       [description]
		 */
		public function modify_pre_wp_nav_menu( $desktop_output, $args ) {

			if ( ! isset( $args->theme_location ) ) {
				return $desktop_output;
			}

			$location = $args->theme_location;

			$menu_id = $this->get_menu_id( $location );

			if ( false === $menu_id ) {
				return $desktop_output;
			}

			$settings = jet_menu_settings_nav()->get_settings( $menu_id );

			if ( ! isset( $settings[ $location ] ) ) {
				return $desktop_output;
			}

			if ( ! isset( $settings[ $location ]['enabled'] ) || 'true' !== $settings[ $location ]['enabled'] ) {
				return $desktop_output;
			}

			$preset = isset( $settings[ $location ]['preset'] ) ? absint( $settings[ $location ]['preset'] ) : 0;

			$show_for_device = jet_menu_option_page()->get_option( 'jet-menu-show-for-device', 'both' );


			switch ( $show_for_device ) {
				case 'both':
					if ( ! Jet_Menu_Tools::is_phone() ) {
						return $desktop_output;
					}
				break;

				case 'desktop':
					return $desktop_output;
				break;
			}

			$this->add_menu_advanced_styles( $menu_id );

			$menu_uniqid = uniqid();

			$toggle_closed_icon_html = sprintf( '<i class="fa %s"></i>', jet_menu_option_page()->get_option( 'jet-menu-mobile-toggle-icon', 'fa-bars' ) );
			$toggle_opened_icon_html = sprintf( '<i class="fa %s"></i>', jet_menu_option_page()->get_option( 'jet-menu-mobile-toggle-opened-icon', 'fa-times' ) );
			$container_close_icon_html = sprintf( '<i class="fa %s"></i>', jet_menu_option_page()->get_option( 'jet-menu-mobile-container-close-icon', 'fa-times' ) );
			$container_back_icon_html = sprintf( '<i class="fa %s"></i>', jet_menu_option_page()->get_option( 'jet-menu-mobile-container-back-icon', 'fa-angle-left' ) );
			$dropdown_icon_html = sprintf( '<i class="fa %s"></i>', jet_menu_option_page()->get_option( 'jet-mobile-items-dropdown-icon', 'fa-angle-right' ) );
			$breadcrumb_icon_html = sprintf( '<i class="fa %s"></i>', jet_menu_option_page()->get_option( 'jet-menu-mobile-breadcrumb-icon', 'fa-angle-right' ) );
			$use_breadcrumbs = jet_menu_option_page()->get_option( 'jet-menu-mobile-use-breadcrumb', 'true' );
			$toggle_loader = jet_menu_option_page()->get_option( 'jet-menu-mobile-toggle-loader', 'true' );

			$menu_options = array(
				'menuUniqId'       => $menu_uniqid,
				'menuId'           => $menu_id,
				'mobileMenuId'     => isset( $settings[ $location ]['mobile'] ) ? intval( $settings[ $location ]['mobile'] ) : false,
				'menuLocation'     => $location,
				'menuLayout'       => jet_menu_option_page()->get_option( 'jet-menu-mobile-layout', 'slide-out' ),
				'togglePosition'   => jet_menu_option_page()->get_option( 'jet-menu-mobile-toggle-position', 'default' ),
				'menuPosition'     => jet_menu_option_page()->get_option( 'jet-menu-mobile-container-position', 'right' ),
				'headerTemplate'   => jet_menu_option_page()->get_option( 'jet-menu-mobile-header-template', 0 ),
				'beforeTemplate'   => jet_menu_option_page()->get_option( 'jet-menu-mobile-before-template', 0 ),
				'afterTemplate'    => jet_menu_option_page()->get_option( 'jet-menu-mobile-after-template', 0 ),
				'toggleClosedIcon' => $toggle_closed_icon_html ? $toggle_closed_icon_html : '',
				'toggleOpenedIcon' => $toggle_opened_icon_html ? $toggle_opened_icon_html : '',
				'closeIcon'        => $container_close_icon_html ? $container_close_icon_html : '',
				'backIcon'         => $container_back_icon_html ? $container_back_icon_html : '',
				'dropdownIcon'     => $dropdown_icon_html ? $dropdown_icon_html : '',
				'useBreadcrumb'    => filter_var( $use_breadcrumbs, FILTER_VALIDATE_BOOLEAN ),
				'breadcrumbIcon'   => $breadcrumb_icon_html ? $breadcrumb_icon_html : '',
				'toggleText'       => jet_menu_option_page()->get_option( 'jet-menu-mobile-toggle-text', '' ),
				'toggleLoader'     => filter_var( $toggle_loader, FILTER_VALIDATE_BOOLEAN ),
				'backText'         => jet_menu_option_page()->get_option( 'jet-menu-mobile-back-text', '' ),
				'itemIconVisible'  => jet_menu_option_page()->get_option( 'jet-mobile-items-icon-enabled', 'true' ),
				'itemBadgeVisible' => jet_menu_option_page()->get_option( 'jet-mobile-items-badge-enabled', 'true' ),
				'itemDescVisible'  => jet_menu_option_page()->get_option( 'jet-mobile-items-desc-enable', 'false' ),
				'loaderColor'      => jet_menu_option_page()->get_option( 'jet-mobile-loader-color', 'false' ),
				'subTrigger'       => jet_menu_option_page()->get_option( 'jet-menu-mobile-sub-trigger', 'item' ),
			);

			$output = sprintf(
				'<div id="%1$s" class="jet-mobile-menu jet-mobile-menu-single %2$s" data-menu-id="%3$s" data-menu-options=\'%4$s\'><MobileMenu :menu-options="menuOptions"></MobileMenu></div>',
				'jet-mobile-menu-' . $menu_uniqid,
				0 !== $preset ? 'jet-preset-' . $preset : '',
				$menu_id,
				json_encode( $menu_options )
			);

			return $output;
		}

		/**
		 * Add menu dynamic styles
		 */
		public function add_dynamic_styles( $preset = 0 ) {

			if ( jet_menu_css_file()->is_enqueued( $preset ) ) {
				return;
			} else {
				jet_menu_css_file()->add_preset_to_save( $preset );
			}

			$preset_class = ( 0 !== $preset ) ? '.jet-preset-' . $preset : '';
			$wrapper      = $preset_class;

			jet_menu_dynmic_css()->add_fonts_styles( $preset_class );
			jet_menu_dynmic_css()->add_backgrounds( $preset_class );
			jet_menu_dynmic_css()->add_borders( $preset_class );
			jet_menu_dynmic_css()->add_shadows( $preset_class );
			jet_menu_dynmic_css()->add_positions( $preset_class );

			$css_scheme = apply_filters( 'jet-menu/menu-css/scheme', array(
				'jet-menu-container-alignment' => array(
					'selector'  => '',
					'rule'      => 'justify-content',
					'value'     => '%1$s',
					'important' => true,
				),
				'jet-menu-mega-padding' => array(
					'selector'  => '',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => true,
				),
				'jet-menu-min-width' => array(
					'selector'  => '',
					'rule'      => 'min-width',
					'value'     => '%1$spx',
					'important' => false,
					'desktop'   => true,
				),
				'jet-menu-mega-border-radius' => array(
					'selector'  => '',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => true,
				),
				'jet-menu-item-text-color' => array(
					'selector'  => '.jet-menu-item .top-level-link',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-item-desc-color' => array(
					'selector'  => '.jet-menu-item .jet-menu-item-desc.top-level-desc',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-item-padding' => array(
					'selector'  => '.jet-menu-item .top-level-link',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-margin' => array(
					'selector'  => '.jet-menu-item .top-level-link',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-border-radius' => array(
					'selector'  => '.jet-menu-item .top-level-link',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-top-badge-text-color' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-badge__inner',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-badge-padding' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-badge__inner',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-top-badge-margin' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-badge',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-top-badge-border-radius' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-badge__inner',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-badge-text-color' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-badge__inner',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-badge-padding' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-badge__inner',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-badge-margin' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-badge__inner',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-badge-border-radius' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-badge__inner',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-text-color-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-item-desc-color-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link .jet-menu-item-desc.top-level-desc',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-item-padding-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-margin-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-border-radius-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-text-color-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .top-level-link',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-item-desc-color-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .jet-menu-item-desc.top-level-desc',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-item-padding-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .top-level-link',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-margin-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .top-level-link',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-item-border-radius-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .top-level-link',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-panel-width-simple' => array(
					'selector'  => 'ul.jet-sub-menu',
					'rule'      => 'min-width',
					'value'     => '%1$spx',
					'important' => false,
				),
				'jet-menu-sub-panel-padding-simple' => array(
					'selector'  => 'ul.jet-sub-menu',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-panel-margin-simple' => array(
					'selector'  => 'ul.jet-sub-menu',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-panel-border-radius-simple' => array(
					'selector'  => 'ul.jet-sub-menu',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-panel-padding-mega' => array(
					'selector'  => 'div.jet-sub-mega-menu',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-panel-margin-mega' => array(
					'selector'  => 'div.jet-sub-mega-menu',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-panel-border-radius-mega' => array(
					'selector'  => 'div.jet-sub-mega-menu',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-text-color' => array(
					'selector'  => 'li.jet-sub-menu-item .sub-level-link',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-desc-color' => array(
					'selector'  => '.jet-menu-item-desc.sub-level-desc',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-padding' => array(
					'selector'  => 'li.jet-sub-menu-item .sub-level-link',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-margin' => array(
					'selector'  => 'li.jet-sub-menu-item .sub-level-link',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-border-radius' => array(
					'selector'  => 'li.jet-sub-menu-item .sub-level-link',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-text-color-hover' => array(
					'selector'  => 'li.jet-sub-menu-item:hover > .sub-level-link',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-desc-color-hover' => array(
					'selector'  => 'li.jet-sub-menu-item:hover > .sub-level-link .jet-menu-item-desc.sub-level-desc',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-padding-hover' => array(
					'selector'  => 'li.jet-sub-menu-item:hover > .sub-level-link',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-margin-hover' => array(
					'selector'  => 'li.jet-sub-menu-item:hover > .sub-level-link',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-border-radius-hover' => array(
					'selector'  => 'li.jet-sub-menu-item:hover > .sub-level-link',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-text-color-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .sub-level-link',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-desc-color-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .jet-menu-item-desc.sub-level-desc',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-padding-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .sub-level-link',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-margin-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .sub-level-link',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-border-radius-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .sub-level-link',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-top-icon-color' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-icon-color-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-icon-color-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .top-level-link .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-icon-color' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-icon-color-hover' => array(
					'selector'  => '.jet-menu-item:hover > .sub-level-link .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-icon-color-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .sub-level-link .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-arrow-color' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-arrow-color-hover' => array(
					'selector'  => '.jet-menu-item:hover > .top-level-link .jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-arrow-color-active' => array(
					'selector'  => '.jet-menu-item.jet-current-menu-item .top-level-link .jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-arrow-color' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-arrow-color-hover' => array(
					'selector'  => '.jet-menu-item:hover > .sub-level-link .jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-arrow-color-active' => array(
					'selector'  => 'li.jet-sub-menu-item.jet-current-menu-item .sub-level-link .jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-icon-order' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-icon',
					'rule'      => 'order',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-icon-order' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-icon',
					'rule'      => 'order',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-badge-order' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-badge',
					'rule'      => 'order',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-badge-order' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-badge',
					'rule'      => 'order',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-arrow-order' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-dropdown-arrow',
					'rule'      => 'order',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-sub-arrow-order' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-dropdown-arrow',
					'rule'      => 'order',
					'value'     => '%1$s',
					'important' => false,
				),
				'jet-menu-top-icon-size' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-icon',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
				),
				'jet-menu-top-icon-margin' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-menu-icon',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-icon-size' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-icon',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
				),
				'jet-menu-sub-icon-margin' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-menu-icon',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-top-arrow-size' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-dropdown-arrow',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
				),
				'jet-menu-top-arrow-margin' => array(
					'selector'  => '.jet-menu-item .top-level-link .jet-dropdown-arrow',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),
				'jet-menu-sub-arrow-size' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-dropdown-arrow',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
				),
				'jet-menu-sub-arrow-margin' => array(
					'selector'  => '.jet-menu-item .sub-level-link .jet-dropdown-arrow',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
				),

				'jet-menu-mobile-toggle-color' => array(
					'selector'  => '.jet-mobile-menu__toggle',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-toggle-bg' => array(
					'selector'  => '.jet-mobile-menu__toggle',
					'rule'      => 'background-color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-toggle-text-color' => array(
					'selector'  => '.jet-mobile-menu__toggle .jet-mobile-menu__toggle-text',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-toggle-size' => array(
					'selector'  => '.jet-mobile-menu__toggle',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-toggle-border-radius' => array(
					'selector'  => '.jet-mobile-menu__toggle',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-toggle-padding' => array(
					'selector'  => '.jet-mobile-menu__toggle',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-breadcrumbs-text-color' => array(
					'selector'  => '.jet-mobile-menu__breadcrumbs .breadcrumb-label',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-breadcrumbs-icon-color' => array(
					'selector'  => '.jet-mobile-menu__breadcrumbs .breadcrumb-divider',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-breadcrumbs-icon-size' => array(
					'selector'  => '.jet-mobile-menu__breadcrumbs .breadcrumb-divider',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-width' => array(
					'selector'  => '.jet-mobile-menu__container',
					'rule'      => 'width',
					'value'     => '%spx',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-bg' => array(
					'selector'  => '.jet-mobile-menu__container-inner',
					'rule'      => 'background-color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-border-radius' => array(
					'selector'  => array(
						'.jet-mobile-menu__container',
						'.jet-mobile-menu__container-inner',
					),
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-padding' => array(
					'selector'  => '.jet-mobile-menu__container-inner',
					'rule'      => 'padding-%s',
					'value'     => '',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-cover-bg' => array(
					'selector'  => '.jet-mobile-menu-cover',
					'rule'      => 'background-color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-close-color' => array(
					'selector'  => '.jet-mobile-menu__back i',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-close-size' => array(
					'selector'  => '.jet-mobile-menu__back i',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
					'mobile'    => true,
				),
				'jet-menu-mobile-container-back-text-color' => array(
					'selector'  => '.jet-mobile-menu__back span',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-dropdown-color' => array(
					'selector'  => '.jet-dropdown-arrow',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-dropdown-size' => array(
					'selector'  => '.jet-dropdown-arrow',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-label-color' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-label',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-label-color-active' => array(
					'selector'  => '.jet-mobile-menu__item.jet-mobile-menu__item--active .jet-menu-label',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-desc-color' => array(
					'selector'  => '.jet-mobile-menu__item.jet-mobile-menu__item--active .jet-menu-desc',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-desc-color-active' => array(
					'selector'  => '.jet-mobile-menu__item.jet-mobile-menu__item--active .jet-menu-desc',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-icon-color' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-icon',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-icon-size' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-icon',
					'rule'      => 'font-size',
					'value'     => '%spx',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-icon-margin' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-icon',
					'rule'      => 'margin-%s',
					'value'     => '',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-badge-color' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-badge__inner',
					'rule'      => 'color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-badge-bg-color' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-badge__inner',
					'rule'      => 'background-color',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-badge-padding' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-badge__inner',
					'rule'      => 'padding-%s',
					'value'     => '%s',
					'important' => false,
					'mobile'    => true,
				),
				'jet-mobile-items-badge-border-radius' => array(
					'selector'  => '.jet-mobile-menu__item .jet-menu-badge__inner',
					'rule'      => 'border-%s-radius',
					'value'     => '',
					'important' => false,
					'mobile'    => true,
				),

			) );

			foreach ( $css_scheme as $setting => $data ) {

				$value = jet_menu_option_page()->get_option( $setting );

				if ( empty( $value ) || 'false' === $value ) {
					continue;
				}

				$_wrapper = $wrapper;

				if ( isset( $data['mobile'] ) && true === $data['mobile'] ) {
					$_wrapper = '.jet-mobile-menu-single';
				} else {
					$_wrapper = '.jet-menu';
				}

				$selector = $data['selector'];

				if ( is_array( $value ) && isset( $value['units'] ) ) {

					if ( is_array( $selector ) ) {

						foreach ( $selector as $key => $selector_item ) {
							jet_menu_dynmic_css()->add_dimensions_css(
								array(
									'selector'  => sprintf( '%1$s %2$s', $_wrapper, $selector_item ),
									'rule'      => $data['rule'],
									'values'    => $value,
									'important' => $data['important'],
								)
							);
						}

					} else {
						jet_menu_dynmic_css()->add_dimensions_css(
							array(
								'selector'  => sprintf( '%1$s %2$s', $_wrapper, $selector ),
								'rule'      => $data['rule'],
								'values'    => $value,
								'important' => $data['important'],
							)
						);
					}

					continue;
				}

				$important = ( true === $data['important'] ) ? ' !important' : '';

				if ( is_array( $selector ) ) {

					foreach ( $selector as $key => $selector_item ) {
						jet_menu()->dynamic_css()->add_style(
							sprintf( '%1$s %2$s', $_wrapper, $selector_item ),
							array(
								$data['rule'] => sprintf( $data['value'], esc_attr( $value ) ) . $important,
							)
						);
					}
				} else {
					jet_menu()->dynamic_css()->add_style(
						sprintf( '%1$s %2$s', $_wrapper, $selector ),
						array(
							$data['rule'] => sprintf( $data['value'], esc_attr( $value ) ) . $important,
						)
					);
				}

			}

			// Items Styles
			$items_map = array(
				'first' => array(
					'top-left'    => 'top',
					'bottom-left' => 'left',
				),
				'last'  => array(
					'top-right'    => 'right',
					'bottom-right' => 'bottom',
				),
			);

			$wrapper = empty( $wrapper ) ? '.jet-menu' : $wrapper;

			foreach ( $items_map as $item => $data ) {

				$parent_radius = jet_menu_option_page()->get_option( 'jet-menu-mega-border-radius' );

				if ( ! $parent_radius ) {
					continue;
				}

				$is_enabled = jet_menu_option_page()->get_option( 'jet-menu-inherit-' . $item . '-radius' );

				if ( 'true' !== $is_enabled ) {
					continue;
				}

				$styles = array();

				foreach ( $data as $rule => $val ) {

					if ( ! $parent_radius ) {
						continue;
					}

					$styles[ 'border-' . $rule . '-radius' ] = $parent_radius[ $val ] . $parent_radius['units'];
				}

				if ( ! empty( $styles ) ) {

					$selector = '%1$s > .jet-menu-item:%2$s-child > .top-level-link';

					if ( 'last' === $item ) {
						$selectors = array(
							'%1$s > .jet-regular-item.jet-has-roll-up:nth-last-child(2) .top-level-link',
							'%1$s > .jet-regular-item.jet-no-roll-up:nth-last-child(1) .top-level-link',
							'%1$s > .jet-responsive-menu-available-items:last-child .top-level-link',
						);

						$selector = join( ',', $selectors );
					}

					jet_menu()->dynamic_css()->add_style(
						sprintf( $selector, $wrapper, $item ),
						$styles
					);
				}

			}

			// Extra Styles
			$max_width = jet_menu_option_page()->get_option( 'jet-menu-item-max-width', 0 );

			if ( 0 !== absint( $max_width ) ) {
				jet_menu()->dynamic_css()->add_style(
					sprintf( '%1$s > .jet-menu-item', $wrapper ),
					array(
						'max-width' => absint( $max_width ) . '%',
					)
				);
			}

			$menu_align = jet_menu_option_page()->get_option( 'jet-menu-container-alignment' );

			if ( 'stretch' === $menu_align ) {
				jet_menu()->dynamic_css()->add_style(
					sprintf( '%1$s > .jet-menu-item', $wrapper ),
					array(
						'flex-grow' => 1,
					)
				);

				jet_menu()->dynamic_css()->add_style(
					sprintf( '%1$s > .jet-menu-item > a', $wrapper ),
					array(
						'justify-content' => 'center',
					)
				);
			}

			// Mobile Styles
			$divider_enabled = jet_menu_option_page()->get_option( 'jet-mobile-items-divider-enabled', false );

			if ( filter_var( $divider_enabled, FILTER_VALIDATE_BOOLEAN ) ) {

				$divider_color = jet_menu_option_page()->get_option( 'jet-mobile-items-divider-color', '#3a3a3a' );
				$divider_width = jet_menu_option_page()->get_option( 'jet-mobile-items-divider-width', '1' );

				jet_menu()->dynamic_css()->add_style(
					'.jet-mobile-menu-single .jet-mobile-menu__item',
					array(
						'border-bottom-style' => 'solid',
						'border-bottom-width' => sprintf( '%spx', $divider_width ),
						'border-bottom-color' => $divider_color,
					)
				);
			}

			$item_icon_enabled = jet_menu_option_page()->get_option( 'jet-mobile-items-icon-enabled', 'true' );

			if ( filter_var( $item_icon_enabled, FILTER_VALIDATE_BOOLEAN ) ) {
				$item_icon_ver_position = jet_menu_option_page()->get_option( 'jet-mobile-items-icon-ver-position', 'center' );

				switch ( $item_icon_ver_position ) {
					case 'top':
						$ver_position = 'flex-start';
					break;
					case 'center':
						$ver_position = 'center';
					break;
					case 'bottom':
						$ver_position = 'flex-end';
					break;
					default:
						$ver_position = 'center';
						break;
				}

				jet_menu()->dynamic_css()->add_style( '.jet-mobile-menu-single .jet-menu-icon', array(
					'-webkit-align-self' => $ver_position,
					'align-self'         => $ver_position,
				) );
			}

			$item_badge_enabled = jet_menu_option_page()->get_option( 'jet-mobile-items-badge-enabled', 'true' );

			if ( filter_var( $item_badge_enabled, FILTER_VALIDATE_BOOLEAN ) ) {
				$item_badge_ver_position = jet_menu_option_page()->get_option( 'jet-mobile-items-badge-ver-position', 'center' );

				switch ( $item_badge_ver_position ) {
					case 'top':
						$ver_position = 'flex-start';
					break;
					case 'center':
						$ver_position = 'center';
					break;
					case 'bottom':
						$ver_position = 'flex-end';
					break;
					default:
						$ver_position = 'center';
						break;
				}

				jet_menu()->dynamic_css()->add_style( '.jet-mobile-menu-single .jet-menu-badge', array(
					'-webkit-align-self' => $ver_position,
					'align-self'         => $ver_position,
				) );
			}

		}

		/**
		 * [generate_menu_raw_data description]
		 * @param  string  $menu_slug [description]
		 * @param  boolean $is_return [description]
		 * @return [type]             [description]
		 */
		public function generate_menu_raw_data( $menu_id = false ) {

			if ( ! $menu_id ) {
				return false;
			}

			$menu_items = $this->get_menu_items_object_data( $menu_id );

			$items = array();

			foreach ( $menu_items as $key => $item ) {

				$item_id = $item->ID;

				$item_settings = jet_menu_settings_nav()->get_item_settings( $item_id );

				$item_template_id = get_post_meta( $item_id, jet_menu_post_type()->meta_key(), true );

				$elementor_template_id = ( isset( $item_settings['enabled'] ) && filter_var( $item_settings['enabled'], FILTER_VALIDATE_BOOLEAN ) ) ? (int)$item_template_id : false;

				$icon_type = isset( $item_settings['menu_icon_type'] ) ? $item_settings['menu_icon_type'] : 'icon';

				switch ( $icon_type ) {
					case 'icon':
						$item_icon = ! empty( $item_settings['menu_icon'] ) ? jet_menu_tools()->get_icon_html( $item_settings['menu_icon'] ) : false;
					break;

					case 'svg':
						$item_icon = ! empty( $item_settings['menu_svg'] ) ? jet_menu_tools()->get_svg_html( $item_settings['menu_svg'] ) : false;
					break;
				}

				$items[] = array(
					'id'                  => 'item-' . $item_id,
					'name'                => $item->title,
					'description'         => $item->description,
					'url'                 => $item->url,
					'itemParent'          => '0' !== $item->menu_item_parent ? 'item-' . (int)$item->menu_item_parent : false,
					'itemId'              => $item_id,
					'elementorTemplateId' => $elementor_template_id,
					'elementorContent'    => false,
					'open'                => false,
					'badgeText'           => isset( $item_settings['menu_badge'] ) ? $item_settings['menu_badge'] : false,
					'itemIcon'            => $item_icon,
				);
			}

			if ( ! empty( $items ) ) {
				$items = $this->buildItemsTree( $items, false );
			}

			$menu_data = array(
				'items' => $items,
			);

			return $menu_data;
		}

		/**
		 * [buildItemsTree description]
		 * @param  array   &$items   [description]
		 * @param  integer $parentId [description]
		 * @return [type]            [description]
		 */
		public function buildItemsTree( array &$items, $parentId = false ) {

			$branch = [];

			foreach ( $items as &$item ) {

				if ( $item['itemParent'] === $parentId ) {
					$children = $this->buildItemsTree( $items, $item['id'] );

					if ( $children && !$item['elementorTemplateId'] ) {
						$item['children'] = $children;
					}

					$branch[ $item['id'] ] = $item;

					unset( $item );
				}
			}

			return $branch;

		}

		/**
		 * [get_menu_items_object_data description]
		 * @param  boolean $menu_id [description]
		 * @return [type]           [description]
		 */
		public function get_menu_items_object_data( $menu_id = false ) {

			if ( ! $menu_id ) {
				return false;
			}

			$menu = wp_get_nav_menu_object( $menu_id );

			$menu_items = wp_get_nav_menu_items( $menu );

			if ( ! $menu_items ) {
				return false;
			}

			return $menu_items;
		}

		/**
		 * Get menu ID for current location
		 *
		 * @param  [type] $location [description]
		 * @return [type]           [description]
		 */
		public function get_menu_id( $location = null ) {

			$locations = get_nav_menu_locations();

			return isset( $locations[ $location ] ) ? $locations[ $location ] : false;
		}

		/**
		 * [add_menu_advanced_styles description]
		 * @param boolean $menu_id [description]
		 */
		public function add_menu_advanced_styles( $menu_id = false ) {

			if ( ! $menu_id ) {
				return false;
			}

			$menu_items = $this->get_menu_items_object_data( $menu_id );

			if ( ! $menu_items ) {
				return false;
			}

			foreach ( $menu_items as $key => $item ) {
				jet_menu_tools()->add_menu_css( $item->ID, '.jet-menu-item-' . $item->ID );
			}
		}

		/**
		 * Save in object chache trigger that defines we output menu in Elementor
		 *
		 * @return void
		 */
		public function set_elementor_mode() {
			wp_cache_set( 'jet-menu-in-elementor', true );
		}

		/**
		 * Reset trigger that defines we output menu in Elementor
		 *
		 * @return void
		 */
		public function reset_elementor_mode() {
			wp_cache_delete( 'jet-menu-in-elementor' );
		}

		/**
		 * Check if current menu inside Elementor
		 *
		 * @return boolean
		 */
		public function is_elementor_mode() {
			return wp_cache_get( 'jet-menu-in-elementor' );
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
 * Returns instance of Jet_Menu_Public_Manager
 *
 * @return object
 */
function jet_menu_public_manager() {
	return Jet_Menu_Public_Manager::get_instance();
}
