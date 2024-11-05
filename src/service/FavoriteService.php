<?php

namespace Alexs\FavoriteUnfavorite\service;

use Alexs\FavoriteUnfavorite\db\DataBase;
use Alexs\FavoriteUnfavorite\model\Favorite;

class FavoriteService{

    private $db;

    public function __construct() {
        $this->db = new DataBase();
    }

    public function favorite(Favorite $data){
        return $this->db->uptadeFavoriteOrUnfavorite($data);
    }

}