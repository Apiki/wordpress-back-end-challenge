<?php
/**
 * REST API handler for favorites
 * 
 * @package Apiki_Favorites
 */

defined('ABSPATH') or die('Direct access not allowed');

require_once __DIR__ . '/class-database.php';

class Apiki_Favorites_REST_API {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('apiki-favorites/v1', '/favorite', [
            'methods' => 'POST',
            'callback' => [$this, 'toggle_favorite'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => [$this, 'validate_post_id'],
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);

        register_rest_route('apiki-favorites/v1', '/favorites', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_favorites'],
            'permission_callback' => [$this, 'check_permission']
        ]);
    }

    public function check_permission() {
        return is_user_logged_in();
    }

    public function toggle_favorite(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('post_id');

        if (Apiki_Favorites_Database::is_post_favorited($user_id, $post_id)) {
            $success = Apiki_Favorites_Database::remove_favorite($user_id, $post_id);
            $action = 'removed';
        } else {
            $success = Apiki_Favorites_Database::add_favorite($user_id, $post_id);
            $action = 'added';
        }

        if ($success) {
            return new WP_REST_Response([
                'success' => true,
                'action' => $action,
                'is_favorited' => ($action === 'added'),
                'post_id' => $post_id
            ], 200);
        }

        return new WP_REST_Response([
            'success' => false,
            'message' => 'Database operation failed'
        ], 500);
    }

    public function get_user_favorites() {
        $user_id = get_current_user_id();
        $favorites = Apiki_Favorites_Database::get_user_favorites($user_id);
        
        return new WP_REST_Response([
            'success' => true,
            'favorites' => $favorites
        ], 200);
    }

    public function validate_post_id($post_id) {
        return is_numeric($post_id) && get_post($post_id) !== null;
    }
}