<?php

namespace WordPressBackEndChallenge;

defined('ABSPATH') || exit;

class RestHandler {
    private $dbHandler;
    private $restNamespace;

    public function __construct() {
        $this->dbHandler = new DbHandler();
        $this->restNamespace = self::getRestNamespace();

        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public static function getRestNamespace() {
        return 'wordpress-back-end-challenge/v1';
    }

    public function registerRoutes() {
        register_rest_route($this->restNamespace, '/favorite-posts', [
            'methods'  => 'GET',
            'callback' => [$this, 'getFavoritePosts'],
            'permission_callback' => function () {
                return is_user_logged_in() ? true : new WP_Error( 'rest_not_logged_in', 'You must be logged in to access this endpoint.', array( 'status' => 401 ) );
            },
        ]);

        register_rest_route($this->restNamespace, '/favorite-posts', [
            'methods'  => 'POST',
            'callback' => [$this, 'addFavoritePost'],
            'permission_callback' => function () {
                return is_user_logged_in() ? true : new WP_Error( 'rest_not_logged_in', 'You must be logged in to access this endpoint.', array( 'status' => 401 ) );
            },
            'args' => [
                'post_id' => [
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ]
            ],
        ]);

        register_rest_route($this->restNamespace, '/favorite-posts', [
            'methods'  => 'DELETE',
            'callback' => [$this, 'removeFavoritePost'],
            'permission_callback' => function () {
                return is_user_logged_in() ? true : new WP_Error( 'rest_not_logged_in', 'You must be logged in to access this endpoint.', array( 'status' => 401 ) );
            },
            'args' => [
                'post_id' => [
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ]
            ],
        ]);
    }

    public function getFavoritePosts($request) {
        $favorites = $this->dbHandler->getAllFavoritesByUserId(get_current_user_id());

        if ($favorites) {
            return new \WP_REST_Response([
                'code' => 'favorite_posts_fetched',
                'status' => 'success',
                'message' => 'Favorite posts fetched',
                'data' => $favorites
            ], 200);
        }

        return new \WP_REST_Response([
            'code' => 'failed_to_fetch_favorite_posts',
            'status' => 'error',
            'message' => 'Failed to fetch favorite posts',
            'data' => []
        ], 200);
    }

    public function addFavoritePost($request) {
        $params = $request->get_json_params();

        $post_id = isset($params['post_id']) ? absint($params['post_id']) : absint($request->get_param('post_id'));

        if (!$post_id) {
            return new \WP_REST_Response([
                'code'    => 'invalid_favorite_post_params',
                'data'    => [],
                'message' => 'Invalid post_id',
                'status'  => 'error'
            ], 400);
        }

        $result = $this->dbHandler->addFavorite(get_current_user_id(), $post_id);

        if ($result) {
            return new \WP_REST_Response([
                'code' => 'favorite_post_added',
                'data' => [
                    'post_id' => $post_id
                ],
                'message' => 'Favorite post added',
                'status'  => 'success'
            ], 200);
        }

        return new \WP_REST_Response([
            'code'    => 'failed_to_add_favorite_post',
            'data'    => [],
            'message' => 'Failed to add favorite post',
            'status'  => 'error',
        ], 400);
    }

    public function removeFavoritePost($request) {
        $params = $request->get_json_params();
        $post_id = isset($params['post_id']) ? absint($params['post_id']) : absint($request->get_param('post_id'));

        if (!$post_id) {
            return new \WP_REST_Response([
                'code'    => 'invalid_favorite_post_params',
                'data'    => [],
                'message' => 'Invalid post_id',
                'status'  => 'error'
            ], 400);
        }

        $result = $this->dbHandler->removeFavorite(get_current_user_id(), $post_id);

        if ($result) {
            return new \WP_REST_Response([
                'code'    => 'favorite_post_removed',
                'data'    => [],
                'status'  => 'success',
                'message' => 'Favorite post removed',
            ], 200);
        }

        return new \WP_REST_Response([
            'code'    => 'failed_to_remove_favorite_post',
            'data'    => [],
            'status'  => 'error',
            'message' => 'Failed to remove favorite post'
        ], 400);
    }
}
