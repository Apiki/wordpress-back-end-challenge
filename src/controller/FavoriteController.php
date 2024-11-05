<?php

namespace Alexs\FavoriteUnfavorite\controller;

use Alexs\FavoriteUnfavorite\service\FavoriteService;
use Alexs\FavoriteUnfavorite\model\Favorite;

class FavoriteController{

    public static function favorite($req){
        
        $service = new FavoriteService();

        try{
            $body = $req->get_json_params();

            if(is_bool($body['action'])){
                $data = new Favorite($body['id'], $body['action']);

                $result = $service->favorite($data);
    
                return rest_ensure_response($result);
            }else{
                return new \WP_Error('invalid_request', 'Value is not boolean', array('status' => 400));
            }

        } catch(\TypeError $te){
            return new \WP_Error('invalid_request', 'Invalid data format', array('status' => 400));
        }
    }
}