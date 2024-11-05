<?php

namespace Alexs\FavoriteUnfavorite\model;

class Favorite{

    private int $id;
    private bool $action;

    function __construct(int $id, bool $action){
        $this->id = $id;
        $this->action = $action;
    }

    public function getId(){
        return $this->id;
    }

    public function setId(int $id){
        $this->id = $id;
    }

    public function getAction(){
        return $this->action;
    }

    public function setAction(bool $action){
        $this->action = $action;
    }
}