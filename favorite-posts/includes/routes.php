<?php

add_action('rest_api_init', function () {
    register_rest_route('favorite-posts/v1', '/toggle/(?P<post_id>\d+)', [
        'methods' => 'POST',
        'callback' => 'fp_toggle_favorite',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);
});
