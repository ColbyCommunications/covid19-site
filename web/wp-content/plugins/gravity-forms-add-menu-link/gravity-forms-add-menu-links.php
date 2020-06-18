<?php
/*
Plugin Name: Gravity Forms Add Menu Links
Plugin URI: https://github.com/bhays/gravity-forms-add-menu-links
Description: Add admin bar links for forms when viewing posts or pages.
Version: 1.0
Author: Ben Hays
Author URI: http://benhays.com

------------------------------------------------------------------------
Copyright 2014 Ben Hays

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

add_action('init',  array('GFAddMenuLinks', 'init'));

class GFAddMenuLinks {

	protected static $form_id;

	public static function init()
	{
		add_filter('gform_pre_render', array('GFAddMenuLinks', 'get_form_id'));
		add_action('wp_before_admin_bar_render', array('GFAddMenuLinks', 'add_to_admin_bar'));
	}

	public static function add_to_admin_bar() {
		global $wp_admin_bar;

		if( current_user_can('manage_options') && !empty(self::$form_id) )
		{
			$admin_url = get_admin_url().'admin.php';
			$menu_links = array(
				array(
					'id' => 'gfef_edit',
					'href' => $admin_url.'?page=gf_edit_forms&id='.self::$form_id,
					'title' => 'Edit Form',
				),
				array(
					'id' => 'gfef_entries',
					'href' => $admin_url.'?page=gf_entries&id='.self::$form_id,
					'title' => 'View Entries',
				),
				array(
					'id' => 'gfef_settings',
					'href' => $admin_url.'?page=gf_edit_forms&view=settings&subview=settings&id='.self::$form_id,
					'title' => 'Edit Settings',
				),
			);

			$wp_admin_bar->add_node(array(
				'id'     => 'gfef',
				'title'  => '<img src="'.GFCommon::get_base_url() . '/images/gravity-admin-icon.png" style="vertical-align: middle; margin-right: 5px;" /> Form Links',
				)
			);
			foreach( $menu_links as $link )
			{
				$wp_admin_bar->add_node(array(
					'parent' => 'gfef',
					'id'     => $link['id'],
					'title'  => $link['title'],
					'href'   => $link['href'],
				));
			}
		}
	}

	public static function get_form_id($form)
	{
		self::$form_id = $form['id'];
		return $form;
	}
}