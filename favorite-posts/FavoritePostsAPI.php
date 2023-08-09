<?php

require_once(plugin_dir_path(__FILE__) . '/Database.php');

class FavoritePostsAPI
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerFavoritePostRoute'));
    }

    public function registerFavoritePostRoute()
    {
        register_rest_route('favorite-posts', '/toggle', array(
            'methods' => 'POST',
            'callback' => array($this, 'favoritePost'),
            'permission_callback' => 'is_user_logged_in'
        ));
    }

    public function favoritePost($request)
    {
        $userId = get_current_user_id();
        $postId = intval($request->get_param('post_id'));
        if (!$userId || !$postId) {
            return new WP_Error('error', 'Parâmetros inválidos', array('status' => 400));
        }
        return self::toggleFavorite($userId, $postId);
    }

    private static function toggleFavorite($userId, $postId)
    {
        $post = get_post($postId);
        if (!$post) {
            return new WP_Error('error', 'O post de id ' . $postId . ' não existe!', array('status' => 400));
        }
        $existingFavorite = Database::searchFavorite($userId, $postId);
        if ($existingFavorite) {
            Database::removeFavorite($existingFavorite->id);
            return array('mensagem' => 'Post desfavoritado com sucesso.');
        }
        Database::addFavorite($userId, $postId);
        return array('mensagem' => 'Post favoritado com sucesso.');
    }
}
