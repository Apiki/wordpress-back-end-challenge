<?php
/**
 * Created by PhpStorm.
 * User: Guilherme
 * Date: 03/07/2018
 * Time: 22:30
 */
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class DBUtil
{
    private $db;
    private $version = "1.0";
    private $name_table= '';


    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->name_table = $this->db->prefix.'fav_user';
    }


    public function createTable(){
        if(get_option("version_bd_api") == ''){

            $table_name = $this->name_table;
            $sql = "CREATE TABLE $table_name (
  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id int,
  user_id int 
) ";
            dbDelta($sql);
            add_option ('version_bd_api', $this->version);
        }


    }
    public function insertDB($post_id,$user_id){
        $this->db->insert(
            $this->name_table,
            array(
                'post_id' => $post_id,
                'user_id' => $user_id
            ));
    }
    public function deleteDB($post_id,$user_id){
        $this->db->delete(
            $this->name_table,
            array(
                'post_id' => $post_id,
                'user_id' => $user_id
            ));
    }

    //implementar dps
    public function updateTable(){

    }


}