<?php
/**
 * @package  fePostLikePlugin
 */

/*
 Plugin Name: PostLike Plugin - Felipe
Plugin URI:  https://www.linkedin.com/in/felipe-lopes-serra/
Description: This plugin allows you to like and dislike posts
Version: 1.0.0
Author: Felipe Lopes
License: MIT
Text Domain: FePostLikePlugin
 */

require_once ABSPATH . "wp-admin/includes/upgrade.php";

if (!defined("ABSPATH")) {
    exit();
}

class FePostLikePlugin
{
    function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $create_table = "CREATE TABLE IF NOT EXISTS wp_posts_like_feplugin (
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          user_id bigint(20) UNSIGNED NOT NULL,
          post_id bigint(20) UNSIGNED NOT NULL,
          liked boolean DEFAULT false,
          created_at datetime NOT NULL,
          updated_at datetime NOT NULL,
          PRIMARY  KEY  (ID),
    	  INDEX (ID),
          FOREIGN KEY (user_id) REFERENCES wp_users(ID),
          FOREIGN KEY (post_id) REFERENCES wp_posts(ID)
        ) $charset_collate;";
        dbDelta($create_table);
    }
    function deactivate()
    {
    }
}

//activation
register_activation_hook(__FILE__, [new FePostLikePlugin(), "activate"]);

//deactivation
register_deactivation_hook(__FILE__, [new FePostLikePlugin(), "deactivate"]);

//write log to debug

//function write_log ( $log )  {
//    if ( is_array( $log ) || is_object( $log ) ) {
//        error_log( print_r( $log, true ) );
//    } else {
//        error_log( $log );
//    }
//}

/**
 * @param $user_id
 * @param $post_id
 * @return array|object|stdClass[]|null
 */
function fe_query_get_liked($user_id, $post_id)
{
    global $wpdb;
    $get_table = "SELECT  * FROM  `wp_posts_like_feplugin` 
    WHERE  user_id = $user_id AND post_id =  $post_id ";
    return $wpdb->get_results($get_table);
}

/**
 * @param $post_id
 * @return array|null
 */
function fe_query_get_all_likes($post_id)
{
    global $wpdb;
    $get_table = "SELECT  * FROM  `wp_posts_like_feplugin` 
    WHERE  post_id =  $post_id ";
    return $wpdb->get_results($get_table);
}

/**
 * @param $user_id
 * @param $post_id
 * @param $liked
 */
function fe_query_update_like($user_id, $post_id, $liked)
{
    global $wpdb;
     $wpdb->query("UPDATE wp_posts_like_feplugin
    SET liked =  $liked , updated_at = CURRENT_TIMESTAMP  
    WHERE  user_id =  $user_id  AND post_id =  $post_id ");

}

/**
 * @param $user_id
 * @param $post_id
 */
function fe_query_insert_like($user_id, $post_id)
{
    $insert_table = "INSERT INTO wp_posts_like_feplugin
                    (
                       user_id,
                       post_id,
                       liked,
                       created_at,
                     updated_at
                    )
                    VALUES
                    (
                        $user_id,
                        $post_id,
                        true,
                        CURRENT_TIMESTAMP,
                     CURRENT_TIMESTAMP
                    );";
    dbDelta($insert_table);
}


function fe_show_post_likes()
{
    global $post;
    $post_id = $post->ID;
    $data_liked = fe_query_get_all_likes($post_id);
    $count_likes = 0;
    foreach ($data_liked as $item) {
        if ($item->liked == 1) {
            $count_likes++;
        }
    }
    echo "<p style='padding: 20px; font-size: 25px'>
Este post tem 
        $count_likes 
        curtida(s)!</p>";
}

/**
 * @return int
 */
function fe_like_or_unlike_action()
{
    global $post;
    $user_id = get_current_user_id();

    if (get_post_type() !== "post" )
        return 0;

    fe_show_post_likes();

    if($user_id  === 0)
        return 0;

    $post_id = $post->ID;
    $data_liked = fe_query_get_liked($user_id, $post_id);

    if (sizeof($data_liked) > 0 ) {
        $liked = $data_liked[0]->liked;

        echo "<p>Gostou desse post?</p>";
        if ($liked == 0 ) {
            echo "
             <form  action='#' method='post'>
             <input type='submit' name='like'
             value='Curtir'/>
             </form>
            ";
        } else {
            echo "<form action='#' method='post'>
        <input type='submit' name='unlike'
                value='Descurtir'/>
    </form>";
        }
    } else {
        echo "
             <form  action='#' method='post'>
             <input type='submit' name='create_like'
             value='Curtir'/>
             </form>
            ";
    }

    if (isset($_POST["like"])) {
        fe_query_update_like($user_id, $post_id, 1);
        echo "<strong>Você curtiu esse post!</strong>";
        echo "<meta http-equiv='refresh' content='0'>";
    }

    if (isset($_POST["create_like"])) {
        fe_query_insert_like($user_id, $post_id);
        echo "<strong>Você curtiu esse post!</strong>";
        echo "<meta http-equiv='refresh' content='0'>";
    }

    if (isset($_POST["unlike"])) {
        fe_query_update_like($user_id, $post_id, 0);
        echo "<strong>Você descurtiu esse post.</strong>";
        echo "<meta http-equiv='refresh' content='0'>";
    }

    return 1;
}

add_action("wp", "fe_like_or_unlike_action");
