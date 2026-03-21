<?php

class WPFAV_API {

    public static function register_routes() {

        register_rest_route('wpfav/v1', '/toggle', [
            'methods' => 'POST',
            'callback' => [self::class, 'toggle'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);

        register_rest_route('wpfav/v1', '/list', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_favorites'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);
    }

    public static function toggle($request) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('post_id');

        return WPFAV_Favorites::toggle_favorite($user_id, $post_id);
    }

    public static function get_favorites() {
        $user_id = get_current_user_id();

        return WPFAV_Favorites::get_user_favorites($user_id);
    }
}