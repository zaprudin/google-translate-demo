<?php

require get_template_directory() . '/inc/ajax.php';
require get_template_directory() . '/inc/google-translate.php';
require get_template_directory() . '/inc/translation-info.php';

add_action('admin_enqueue_scripts', 'z_admin_enqueue_scripts');
function z_admin_enqueue_scripts() {
	wp_enqueue_style('z-admin-styles', get_template_directory_uri().'/admin/assets/admin.css');
	wp_enqueue_script( 'z-admin', get_template_directory_uri().'/admin/assets/admin.js', array(), '', true );
}