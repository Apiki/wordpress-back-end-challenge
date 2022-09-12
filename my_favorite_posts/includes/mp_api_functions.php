<?php

// ADICIONAR ENDPOINTS

// ADICIONAR AOS FAVORITOS
// YOUR_SITE_URL/wp-json/api/add_favorite_post/id_user/id_post
add_action(
    'rest_api_init',
    function () {
        register_rest_route(
            'api',
            '/add_favorite_post/(?P<id_user>\d+)/(?P<id_post>\d+)',
            array(
                'methods'  => 'GET',
                'callback' => 'insert_data',
            )
        );
    }
);

// REMOVER DOS FAVORITOS
// YOUR_SITE_URL/wp-json/api/add_favorite_post/id
add_action(
    'rest_api_init',
    function () {
        register_rest_route(
            'api',
            '/remove_favorite_post/(?P<id>\d+)',
            array(
                'methods'  => 'GET',
                'callback' => 'mp_remove_favorite_post',
            )
        );
    }
);