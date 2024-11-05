<?php

namespace Alexs\FavoriteUnfavorite\routes;

use Alexs\FavoriteUnfavorite\controller\FavoriteController;

class Routes{

    private static function favoriteUnfavorite(){
        register_rest_route( 'api/v1', '/favorite', array(
            'methods'  => 'PUT',
            'callback' => array(FavoriteController::class, 'favorite'),
            'args'     => array(),
        ) );
    }

    public static function registerRoutes(){
        self::favoriteUnfavorite();
    }

}