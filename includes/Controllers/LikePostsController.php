<?php

namespace LikePosts\Controllers;

use LikePosts\Models\LikePostsModel;
use LikePosts\Views\LikePostsView\LikePostsView;
use WP_REST_Response;

class LikePostsController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new LikePostsModel();
        $this->model->create_table();
        $this->view = new LikePostsView();

        add_action('rest_api_init', [$this, 'register_rest_routes']);      
        add_action('the_content', [$this, 'add_like_button']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_rest_routes() {
        register_rest_route('like/v1', '/add', [
            'methods' => 'POST',
            'callback' => [$this, 'like_post'],
            'permission_callback' => [$this, 'is_user_logged_in']
        ]);

        register_rest_route('like/v1', '/delete', [
            'methods' => 'DELETE',
            'callback' => [$this, 'dislike_post'],
            'permission_callback' => [$this, 'is_user_logged_in']
        ]);
    }

    public function is_user_logged_in() {
        return is_user_logged_in();
    }

    public function like_post($request) {
        $user_id = get_current_user_id();
        $post_id = intval($request['post_id']);

        if (!$this->is_valid_post($post_id)) {
            return new WP_REST_Response(['message' => 'Post inválido!'], 400);
        }

        $this->model->like($user_id, $post_id);
        return new WP_REST_Response(['message' => 'Post favoritado!'], 200);
    }

    public function dislike_post($request) {
        $user_id = get_current_user_id();
        $post_id = intval($request['post_id']);

        if (!$this->is_valid_post($post_id)) {
            return new WP_REST_Response(['message' => 'Post inválido!'], 400);
        }

        $this->model->dislike($user_id, $post_id);
        return new WP_REST_Response(['message' => 'Post desfavoritado!'], 200);
    }


    public function add_like_button($content) {
        if (!is_single()) {
            return $content; 
        }

        ob_start();
        $this->view->render(get_the_ID());
        $button = ob_get_clean();

        return $content . $button;       
    }

    public function enqueue_assets() {
        wp_enqueue_style('like-posts-style', plugin_dir_url(__FILE__) . '../../assets/css/style.css');
        wp_enqueue_script('like-posts-script', plugin_dir_url(__FILE__) . '../../assets/js/script.js', [], time(), true);
        wp_localize_script('like-posts-script', 'wpApiSettings', [
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    public function has_liked_post($user_id, $post_id){
        return $this->model->user_has_liked_post($user_id, $post_id);
    }

    private function is_valid_post($post_id) {
        return get_post($post_id) !== null;
    }
}
