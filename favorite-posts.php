<?php
/*
Plugin Name: Favoritar Post
Description: Favoritar e desfavoritar post
Version: 1.0
Author: Dalton Macedo
*/

if (!defined('ABSPATH')) {
    exit;
}

class FavoritarPostsPlugin {
    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'onActivate']);
        add_action('rest_api_init', [$this, 'registerApiRoutes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_shortcode('favorite_button', [$this, 'exibirBotaoFavoritar']);
    }

    // Cria a tabela no banco de dados ao ativar o plugin
    public function onActivate() {
        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $tableName (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) $charsetCollate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Registra rotas na WP REST API
    public function registerApiRoutes() {
        register_rest_route('favorite-posts/v1', '/toggle', [
            'methods' => 'POST',
            'callback' => [$this, 'handleToggleFavorite'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);
    }

    // Cria e executa a lógica de favoritar/desfavoritar
    public function handleToggleFavorite(WP_REST_Request $request) {
        $userId = get_current_user_id();
        $postId = $request->get_param('post_id');

        if (!get_post($postId)) {
            return new WP_Error('invalid_post', 'Post inválido.', ['status' => 404]);
        }

        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';

        // Verifica se o post já está favoritado
        $favoriteExists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $tableName WHERE user_id = %d AND post_id = %d",
            $userId, $postId
        ));

        if ($favoriteExists) {
            // Remove o favorito
            $wpdb->delete($tableName, ['id' => $favoriteExists], ['%d']);
            return ['status' => 'unfavorited'];
        } else {
            // Adiciona o favorito
            $wpdb->insert($tableName, [
                'user_id' => $userId,
                'post_id' => $postId
            ], ['%d', '%d']);
            return ['status' => 'favorited'];
        }
    }

    // Adiciona os scripts necessários
    public function enqueueScripts() {
        if (is_single()) {
            wp_enqueue_script('favorite-posts-script', plugins_url('/favorite-posts.js', __FILE__), ['jquery'], null, true);
            wp_localize_script('favorite-posts-script', 'favoritePostsData', [
                'nonce' => wp_create_nonce('wp_rest'),
                'apiUrl' => rest_url('favorite-posts/v1/toggle')
            ]);
        }
    }

    // Exibir o botão de favoritar
    public function exibirBotaoFavoritar($atts) {
        if (!is_user_logged_in()) {
            return '<p>Você precisa estar logado para favoritar posts.</p>';
        }

        global $post;
        $postId = $post->ID;
        return '<button class="favorite-button" data-post-id="' . esc_attr($postId) . '">Favoritar</button>';
    }
}

new FavoritarPostsPlugin();
