<?php

class Routes {
    public static function register() {
        // Rota para favoritar/desfavoritar post
        register_rest_route('post-favorites/v1', '/toggle', [
            'methods' => 'POST',
            'callback' => ['FavoriteController', 'toggle'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args' => [
                'post_id' => [
                    'required' => true,
                    'type' => 'integer',
                ]
            ]
        ]); 
        
        // Rota para listar favoritos do usuário logado
        register_rest_route('post-favorites/v1', '/list', [
            'methods' => 'GET',
            'callback' => ['FavoriteController', 'list'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);
    }
}
