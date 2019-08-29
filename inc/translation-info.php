<?php 

add_action('admin_menu', 'z_translation_info_admin_menu');
function z_translation_info_admin_menu() {
	add_menu_page('Переводы', 'Переводы', 'edit_posts', 'translation-info', 'z_translation_info_page');
}

function z_translation_info_page() {
	get_template_part('inc/translation-info-content');
}