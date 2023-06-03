<?php

function add_text_after_content($content) {

	

	global $wpdb; 
	$post_id = get_the_ID();
	$user_id = get_current_user_id();

	$table_name = $wpdb->prefix . 'liked'; 

	$query = $wpdb->prepare("SELECT * FROM $table_name WHERE id_post = %d AND id_user = %d", $post_id, $user_id);
  $already_liked = $wpdb->get_row($query);

	$active = '';
	$liked = 'false';
	if($already_liked){
		$active = '-active';
		$liked = 'true';
	}

	if (is_singular('post')) {

		if(!is_user_logged_in()){
			$content = "<div class='alert alert-warning'>funcionalidade de like somente para usuários cadastrados</div>";
			return $content;
		}
		 
		$img_url = plugin_dir_url(dirname(__FILE__)) . "img/heart$active.svg";
		$img_html = '<div class="likeContainer"><img src="' . $img_url . '" class="likeImg" id="clickToLike" data-post-id="' . $post_id . '" data-user-id="' . $user_id . '" data-liked="' . $liked . '" alt="like"/></div>';
		$content .= $img_html;
	}

	return $content;
}
add_filter('the_content', 'add_text_after_content');

// Função para adicionar o like
function register_like() {
	
	if (isset($_POST['post_id']) && isset($_POST['user_id']) && isset($_POST['is_liked'])) {
		global $wpdb; 

		$table_name = $wpdb->prefix . 'liked'; 

		$post_id = $_POST['post_id'];
		$user_id = $_POST['user_id'];
		$is_liked = $_POST['is_liked'];
		
		if($is_liked == "true"){
			$where = array(
				'id_user' => $user_id,
				'id_post' => $post_id
			);
			$wpdb->delete($table_name, $where);
			wp_send_json(array(
				'success' => true,
				'message' => 'disliked'
			));
			return false;

		}

		$wpdb->insert(
				$table_name,
				array(
						'id_post' => $post_id,
						'id_user' => $user_id
				)
		);

		wp_send_json(array(
			'success' => true,
			'message' => 'liked'
		));

	} else {

		wp_send_json(array(
			'success' => false,
			'message' => 'deu b.o'
		));

	}

	wp_die();
}

// Hook para adicionar a função no arquivo admin-ajax.php
add_action('wp_ajax_register_like', 'register_like');
add_action('wp_ajax_nopriv_register_like', 'register_like');
