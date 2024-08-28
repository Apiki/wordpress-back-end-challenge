<?php

// Função para adicionar um botão de "like" após o conteúdo do post.
function add_text_after_content($content) {
    global $wpdb;
    $post_id = get_the_ID();
    $user_id = get_current_user_id();

    $table_name = $wpdb->prefix . 'liked'; 

    // Consulta para verificar se o usuário já curtiu o post.
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
            $content .= "<div class='alert alert-warning'>Funcionalidade de like somente para usuários cadastrados</div>";
            return $content;
        }
         
        $img_url = plugin_dir_url(dirname(__FILE__)) . "img/star$active.svg";
        $img_html = '<div class="likeContainer"><img src="' . $img_url . '" class="likeImg" id="clickToLike" data-post-id="' . $post_id . '" data-user-id="' . $user_id . '" data-liked="' . $liked . '" alt="like"/></div>';
        $content .= $img_html;
    }

    return $content;
}
add_filter('the_content', 'add_text_after_content');

// Função para registrar endpoints personalizados da REST API.
add_action('rest_api_init', function () {
    register_rest_route('favorites/v1', '/toggle/', [
        'methods' => 'POST',
        'callback' => 'toggle_favorite',
        'permission_callback' => function () {
            return is_user_logged_in(); // Garante que apenas usuários logados possam acessar.
        },
    ]);
});

// Função de callback para o endpoint da REST API.
function toggle_favorite(WP_REST_Request $request) {
    global $wpdb;

    $user_id = get_current_user_id();
    $post_id = $request->get_param('post_id');
    
    if (!$user_id || !$post_id) {
        return new WP_REST_Response(['success' => false, 'message' => 'Dados inválidos'], 400);
    }

    $table_name = $wpdb->prefix . 'liked'; 

    // Verifica se o post já foi favoritado pelo usuário.
    $favorite = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id_post = %d AND id_user = %d",
        $post_id, $user_id
    ));

    if ($favorite) {
        // Se já estiver favoritado, remove.
        $wpdb->delete($table_name, ['id_user' => $user_id, 'id_post' => $post_id]);
        return new WP_REST_Response(['success' => true, 'message' => 'disliked'], 200);
    } else {
        // Caso contrário, adiciona como favorito.
        $wpdb->insert($table_name, ['id_post' => $post_id, 'id_user' => $user_id]);
        return new WP_REST_Response(['success' => true, 'message' => 'liked'], 200);
    }
}
