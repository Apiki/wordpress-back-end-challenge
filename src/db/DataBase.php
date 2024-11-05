<?php

namespace Alexs\FavoriteUnfavorite\db;

use Alexs\FavoriteUnfavorite\model\Favorite;
use Dotenv\Dotenv;

class DataBase{

    private $dotenv;

    function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    public function connect(){
        
        $host = $_ENV['HOSTNAME'];
        $user = $_ENV['USERNAME_DATA_BASE'];
        $pass = $_ENV['PASSWORD'];
        $db = $_ENV['DB_NAME'];

        try{
            $conn = new \PDO("mysql:host=$host;dbname=$db", $user, $pass);

            return $conn;
        }catch(\PDOException $e){
            return new \WP_Error('Internal server error', "Internal error", array('status' => 500));
        }
    }

    public function disconnect($connection){
        mysqli_close($connections);
    }

    public function init(){
        $conn = $this->connect();

        $sql = "CREATE TABLE IF NOT EXISTS posts (
                id int AUTO_INCREMENT PRIMARY KEY,
                favorite boolean NOT NULL)";

        $stmt = $conn->prepare($sql);

        $stmt->execute();

        $conn = null;
    }

    public function uptadeFavoriteOrUnfavorite(Favorite $data){
        $conn = $this->connect();

        $verify = $this->verifyPost($data->getId());

        if($verify){
            $sql = "UPDATE posts SET favorite = :favorite WHERE id = :id";

            $stmt = $conn->prepare($sql);

            $action = $data->getAction() ? 1 : 0;

            $stmt->bindParam(":favorite", $action);
            $stmt->bindParam(":id", $data->getId());

            $stmt->execute();

            $conn = null;

            $response = [
                "id" => $data->getId(),
                "success" => true,
                "favorite" => $data->getAction(),
            ];

            return $response;
        }else{
            $conn = null;
            return new \WP_Error('invalid_request', "Post with id {$data->getId()} not found", array('status' => 404));
        }
    }

    private function verifyPost(int $id){
        $conn = $this->connect();

        $sql = "SELECT * FROM posts WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        $itens = $stmt->fetchAll();

        if(empty($itens)){
            return false;
        }

        return true;
    }
}