<?php

if (!defined('ABSPATH')) {
    exit;
}

class Favoritos_REST_Controller
{
    private $namespace = 'favoritos/v1';
    private $repository;

    public function __construct(Favoritos_Repository $repository)
    {
        $this->repository = $repository;
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/toggle/(?P<post_id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_toggle'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'post_id' => [
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    },
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/list', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_list'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    public function check_permissions()
    {
        return is_user_logged_in();
    }

    public function handle_toggle($request)
    {
        $user_id = get_current_user_id();
        $post_id = (int) $request['post_id'];

        $post = get_post($post_id);
        if (!$post || 'publish' !== $post->post_status) {
            return new WP_Error('invalid_post', 'Post invalido.', ['status' => 400]);
        }

        $status = $this->repository->toggle($user_id, $post_id);
        if ($status instanceof WP_Error) {
            return $status;
        }

        $favorites = $this->format_posts(
            $this->repository->get_user_favorite_ids($user_id)
        );

        return rest_ensure_response([
            'status' => $status,
            'favoritos' => $favorites,
        ]);
    }

    public function handle_list($request)
    {
        $user_id = get_current_user_id();
        $favorites = $this->format_posts(
            $this->repository->get_user_favorite_ids($user_id)
        );

        return rest_ensure_response($favorites);
    }

    private function format_posts(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $posts = get_posts([
            'post__in' => $ids,
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'post__in',
            'posts_per_page' => count($ids),
        ]);

        return array_map(function ($post) {
            return [
                'ID' => $post->ID,
                'title' => get_the_title($post),
                'link' => get_permalink($post),
            ];
        }, $posts);
    }
}
