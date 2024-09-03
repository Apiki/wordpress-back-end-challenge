<?php
/*
Plugin Name: Favorite Posts
Description: Plugin que permite que usuários logados favoritem e desfavoritem posts usando a WP REST API.
Version: 1.0
Author: Luan Menezes Barros 
*/

// Impedir acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Executa a função ao ativar o plugin
register_activation_hook(_FILE_, 'fp_create_favorite_table');

function fp_create_favorite_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'favorite_posts';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Adiciona a ação de inicializar as rotas da REST API
add_action('rest_api_init', function () {
    register_rest_route('favorite-posts/v1', '/toggle-favorite/(?P<post_id>\d+)', [
        'methods' => 'POST',
        'callback' => 'fp_toggle_favorite',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);

    register_rest_route('favorite-posts/v1', '/list-favorites', [
        'methods' => 'GET',
        'callback' => 'fp_list_favorites',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);
});

// Função para favoritar ou desfavoritar um post
function fp_toggle_favorite($data)
{
    global $wpdb;
    $user_id = get_current_user_id();
    $post_id = absint($data['post_id']);
    $table_name = $wpdb->prefix . 'favorite_posts';

    // Verifica se o post já está favoritado pelo usuário
    $is_favorited = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ));

    if ($is_favorited) {
        // Desfavorita o post
        $wpdb->delete($table_name, ['user_id' => $user_id, 'post_id' => $post_id]);
        return new WP_REST_Response(['status' => 'unfavorited'], 200);
    } else {
        // Favorita o post
        $wpdb->insert($table_name, ['user_id' => $user_id, 'post_id' => $post_id]);
        return new WP_REST_Response(['status' => 'favorited'], 200);
    }
}

// Função para listar os favoritos do usuário atual
function fp_list_favorites()
{
    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'favorite_posts';

    $favorites = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id FROM $table_name WHERE user_id = %d",
        $user_id
    ), ARRAY_A);

    return new WP_REST_Response.(['favorites' => $favorites], 200);
}