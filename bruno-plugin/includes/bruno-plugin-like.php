<?php 

class LikeData {

  private $ID;
  private $user_id;
  private $post_id;
  private $status;
  private $wpdb;

  public function like($id, $post_id, $user_id, $status){

    global $wpdb;

    $this->ID      = $id;    
    $this->post_id = $post_id;    
    $this->user_id = $user_id;       
    $this->status  = $status;   
    
    $sql = "INSERT INTO `{$wpdb->prefix}post_likes` 
            (`ID`, `user_id`, `post_id`, `like_status`) 
            VALUES  ({$this->ID},	{$this->user_id},	{$this->post_id},	{$this->status})
            ON DUPLICATE KEY UPDATE `ID` = VALUES(`ID`), `user_id` = VALUES(`user_id`), `post_id` = VALUES(`post_id`), `like_status` = VALUES(`like_status`) ";
    
    $wpdb->query(
      $wpdb->prepare(
        $sql
      )
    );    

    $this->wpdb = $wpdb;

  }	

  public function get_message(){
    return $this->wpdb;
  }

}