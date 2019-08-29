<?php 
require get_template_directory() . '/inc/vendor/autoload.php';
use Google\Cloud\Translate\TranslateClient;

add_action( 'wp_ajax_get_published_posts_list', 'ajax_get_published_posts_list' );
add_action( 'wp_ajax_get_all_posts_list', 'ajax_get_all_posts_list' );
add_action( 'wp_ajax_translate_post', 'ajax_translate_post' );
add_action( 'wp_ajax_save_initial_texts', 'ajax_save_initial_texts' );

function ajax_get_published_posts_list() {
	$return = array();

	$args = array(
		'posts_per_page' => -1,
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'     => 'translated',
				'value'   => 1,
				'compare' => '<',
			),
			array(
				'key'     => 'translated',
				'compare' => 'NOT EXISTS',
			),
		),
	);
	$posts = get_posts( $args );

	$post_ids = array();
	foreach( $posts as $post ) {
		$post_ids[] = $post->ID;
	}

	$return['items'] = $post_ids;
	if( count($post_ids) ) {
		$return['status'] = 'ok';
	}

	echo json_encode($return);
	die();
}

function ajax_get_all_posts_list() {
	$return = array();

	$args = array(
		'posts_per_page' => -1,
		'post_type'      => array('photo', 'page', 'post'),
		'post_status'    => 'any',
	);
	$posts = get_posts( $args );

	$post_ids = array();
	foreach( $posts as $post ) {
		$post_ids[] = $post->ID;
		// break;
	}

	$return['items'] = $post_ids;
	if( count($post_ids) ) {
		$return['status'] = 'ok';
	}

	echo json_encode($return);
	die();
}

function translate_post($post_id) {
	$translate = new TranslateClient([
		'key' => 'GOOGLE_CLOUD_KEY',
		'project' => 'translate-ip',
		'target' => 'es',
		'source' => 'en',
	]);

	$return = array(
		'status' => 'error',
	);

	$post = get_post( (int)$post_id );

	$error = false;

	if( $post_id && $post ) {
		$en_content = get_post_meta( $post_id, 'en_content', true );

		if( ! $en_content ) {
			$en_content = $post->post_content;
			update_post_meta( $post_id, 'en_content', $post->post_content);
		}

		if( stristr($en_content, 'class="notranslate"') ) {
			$en_content = str_ireplace('class="notranslate"', '', $en_content);
		}

		if( $en_content ) {
			$en_content = str_replace("\r\n", "<justsomechars/>", $en_content);
			$translation = $translate->translate($en_content);

			if( $translation && $translation['text'] ) {
				$translation['text'] = str_replace(array('[/ caption]', '&quot;', '<justsomechars/>'), array('[/caption]', '"', "\r\n"), $translation['text']);
				$pattern = '/\[caption(.*?)](.*?)alt="(.*?)"(.*?)<\/a> (.*?) \[\/caption]/';
				$replacement = '[caption${1}]${2}alt="${3}"${4}</a> ${3}[/caption]';
				$translation['text'] = preg_replace($pattern, $replacement , $translation['text']);
				$new_content = $translation['text'];
				$post->post_content = $new_content;
			} else {
				$error == true;
			}
		}

		$en_title = get_post_meta( $post_id, 'en_title', true );

		if( ! $en_title ) {
			update_post_meta( $post_id, 'en_title', $post->post_title);
		}

		if( $en_title && ! $error ) {
			$translation = $translate->translate($en_title);
			if( $translation && $translation['text'] ) {
				$new_title = $translation['text'];
				$post->post_title = $new_title;
			} else {
				$error == true;
			}
		}

		if( wp_update_post( $post ) && ! $error ) {
			update_post_meta( $post_id, 'translated', 1);
		} else {
			$error = true;
		}

		if( ! $error ) {
			$return['result'] = $translation;
			$return['status'] = 'ok';
			$return['message'] = "<b>Переведено $post_id</b> - $new_title: <pre>{$new_content}</pre>";
		} else {
			$return['message'] = "<b>НЕ переведено $post_id</b>";
		}
	}

	echo json_encode($return);
	die();
}

function ajax_translate_post() {
	$post_id = (int)$_POST['item_to_process'];
	translate_post($post_id);
}

function ajax_save_initial_texts() {
	$return = array(
		'status' => 'error',
	);

	$item = (int)$_POST['item_to_process'];
	$post = get_post( $item );

	$error = false;

	if( $item && $post ) {
		update_post_meta( $item, 'en_content', $post->post_content);
		update_post_meta( $item, 'en_title', $post->post_title);

		if( ! $error ) {
			$return['result'] = $translation;
			$return['status'] = 'ok';
			$return['message'] = "Сохранены тексты $item - {$post->post_title}";
		} else {
			$return['message'] = "НЕ сохранены тексты $item";
		}
	}

	echo json_encode($return);
	die();
}
