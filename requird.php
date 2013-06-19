<?php
/*
Plugin Name: Requird
Plugin URI: http://kollectiv.org/requird
Description: Require fields for WP-Admin
Writer: Gil Barbara
Version: 1.0.1
Writer URI: http://kollectiv.org
*/

function requirdStart() {
	wp_enqueue_script(
		'requird', //?width='.$options['width'].'&height='.$options['height']
		plugins_url( '/requird.js' , __FILE__ ),
		array( 'jquery' )
	);
	wp_localize_script('requird', 'requirdOptions', get_option('requird'));
}

add_action('edit_form_advanced', 'requirdStart');
add_action('edit_page_form', 'requirdStart');
if (is_admin()) {
	include('requird-settings.php');
}

?>