<?php
/*
Plugin Name: Requird
Plugin URI: http://kollectiv.org/requird
Description: Require fields for WP-Admin
Writer: Gil Barbara
Version: 1.2.2
Writer URI: http://kollectiv.org
*/

function requirdStart() {
	wp_enqueue_script(
		'requird',
		plugins_url( '/requird.js' , __FILE__ ),
		array( 'jquery' )
	);
	wp_localize_script('requird', 'requirdOptions', get_option('requird'));
}

function requird_settings_link($action_links,$plugin_file){
	if($plugin_file==plugin_basename(__FILE__)){
		$requird_settings_link = '<a href="tools.php?page=' . dirname(plugin_basename(__FILE__)) . '">' . __("Settings") . '</a>';
		array_unshift($action_links,$requird_settings_link);
	}
	return $action_links;
}
add_filter('plugin_action_links','requird_settings_link',10,2);

add_action('edit_form_advanced', 'requirdStart');
add_action('edit_page_form', 'requirdStart');
if (is_admin()) {
	include('requird-settings.php');
}

?>