<?php

/*  Copyright 2013 Gil Barbara (email : gilbarbara@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Requird {

	public $options;
	public $version;

	var $default_options = array('error_message' => 'Field Required');

	function __construct() {
		// Set current options
		add_action('plugins_loaded', array(&$this, 'set_options'));
		// Add options page to menu
		add_action('admin_menu', array(&$this, 'admin_menu'));
		// Initialize options
		add_action('admin_init', array(&$this, 'initialize_options'));
		// Settings page actions
		$this->version = '1.2.2';
	}

	// Set options & possibly upgrade
	function set_options() {
		// Get the current options from the database
		$options = get_option('requird');
		// If there aren't any options, load the defaults
		if (!$options) $options = $this->default_options;
		// Check if our options need upgrading
		$options = $this->upgrade_options($options);
		// Set the options class variable
		$this->options = $options;
	}

	function upgrade_options($options) {

		// Boolean for if options need updating
		$options_need_updating = false;

		if (!isset($options['version']) || $options['version'] < $this->version) {
			$options['version'] = $this->version;
			$options_need_updating = true;
		}
		if (!isset($options['error_message'])) {
			$options['error_message'] = 'Field Required';
			$options_need_updating = true;
		}

		// Save options to database if they've been updated
		if ($options_need_updating) {
			update_option('requird', $options);
		}

		return $options;
	}

	function admin_menu() {

		add_management_page( __( 'Requird Options', 'requird' ), __( 'Requird', 'requird' ), 'manage_options', 'requird', array(&$this, 'options_page') );
	/*	add_options_page(
			'Required Options',
			'Requird',
			'manage_options',
			'requird',
			array(&$this, 'options_page')
		);*/
	}

	function initialize_options() {
		add_settings_section(
			'general_settings_section',
			'General Settings',
			array(&$this, 'general_settings_callback'),
			'requird'
		);

		// Get post types

		$supports = array('title', 'editor', 'thumbnail', 'excerpt');
		$post_types = get_post_types(array('public' => true));
		// Remove certain post types from array
		$post_types = array_diff($post_types, array('attachment', 'revision', 'nav_menu_item'));
		$args = array(
			'public'   => true,
			'_builtin' => false

		);
		$taxonomies = get_taxonomies(array('public' => true, '_builtin' => false),'objects');

		$this->add_text_setting(
			'error_message',
			'Required Message',
			'The message to show when a required field is empty'
		);

		foreach ($post_types as $post_type) {
			$fieldsAri = array();
			foreach ($supports as $support) {
				if (post_type_supports($post_type, $support)) $fieldsAri[] = $support;
			}
			if ($post_type == 'post') {
				$fieldsAri[] = 'category';
				$fieldsAri[] = 'post_tag';
			}
			foreach ($taxonomies as $k => $v) {
				if (in_array($post_type, $v->object_type)) {
					$fieldsAri[] = $k;
				}
			}

			$this->add_multicheckbox_setting(
				$post_type,
				ucfirst($post_type),
				$fieldsAri
			);
			$this->add_text_setting(
				$post_type . '-custom',
				'',
				'Enter the slugs of the custom fields separated by comma. No spaces.'
			);
		}

		register_setting('requird', 'requird', array(&$this, 'sanitize_callback'));
	}

	function sanitize_callback($input) {
		$current_settings = get_option('requird');
		$output = array();
		// General settings
		foreach ($input as $key => $value) {
			$output[$key] = $input[$key];
		}
		if (!$output['version']) $output['version'] = $current_settings['version'] ? $current_settings['version'] : $this->version;

		return $output;
	}

	function general_settings_callback() {
		echo '<p>Check the fields that you want to be required. Remember to show these fields as visible on Screen Options.</p>';
	}

	function add_checkbox_setting($slug, $name, $description) {
		add_settings_field(
			$slug,
			$name,
			array(&$this, 'checkbox_callback'),
			'requird',
			'general_settings_section',
			array(
				'slug' => $slug,
				'description' => $description
			)
		);
	}

	function checkbox_callback($args) {
		$html = '<label for="' . $args['slug'] . '"><input type="checkbox" id="' . $args['slug'] . '" name="requird[' . $args['slug'] . ']" value="1" ' . checked(1, $this->options[$args['slug']], false) . '/> ' . $args['description'] . '</label>';
		echo $html;
	}

	function add_multicheckbox_setting($slug, $name, $options) {
		add_settings_field(
			$slug,
			$name,
			array(&$this, 'multicheckbox_callback'),
			'requird',
			'general_settings_section',
			array(
				'slug' => $slug,
				'options' => $options
			)
		);
	}

	function multicheckbox_callback($args) {
		if (is_array($this->options[$args['slug']])) {
			$selected_types = $this->options[$args['slug']];
		} else {
			$selected_types = array();
		}
		$html = '';
		foreach ($args['options'] as $option) {
			$checked = (in_array($option, $selected_types) ? 'checked="checked"' : '');
			$html .= '<label for="' . $args['slug'] . '_' . $option . '"><input type="checkbox" id="' . $args['slug'] . '_' . $option . '" name="requird[' . $args['slug'] . '][]" value="' . $option . '" ' . $checked . '/> ' . $option . '</label><br>';
		}
		echo $html;
	}

	function add_text_setting($slug, $name, $description) {
		add_settings_field(
			$slug,
			$name,
			array(&$this, 'text_field_callback'),
			'requird',
			'general_settings_section',
			array(
				'slug' => $slug,
				'description' => $description
			)
		);
	}

	function text_field_callback($args) {
		$html = '<input type="text" id="' . $args['slug'] . '" name="requird[' . $args['slug'] . ']" value="' . $this->options[$args['slug']] . '"/>';
		$html .= '<label for="' . $args['slug'] . '"> ' . $args['description'] . '</label>';
		echo $html;
	}

	function options_page() {

	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	?>
	<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h2>Requird</h2>

	<form method="post" action="options.php">
		<?php settings_fields('requird'); ?>
		<?php do_settings_sections('requird'); ?>
		<?php submit_button(); ?>
	</form>

	</div>
	<?php
	}
}

new Requird();

?>