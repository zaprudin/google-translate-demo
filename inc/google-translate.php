<?php 

add_action('admin_menu', 'z_dev_info_admin_menu');
function z_dev_info_admin_menu() {
	add_menu_page('Dev', 'Dev', 'edit_posts', 'dev-info', 'z_dev_info_page');
}

function z_dev_info_page() {
	get_template_part('inc/dev-info-page');
}