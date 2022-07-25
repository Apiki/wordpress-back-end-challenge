<?php


namespace model;

use Exception;

class Queries
{

    /**
     * @param $user_id
     * @param $post_id
     * @return array|object|null
     */
    public function fe_query_get_favorited($user_id, $post_id)
    {
        global $wpdb;
        $get_table = "SELECT  * FROM  `wp_favorite_posts_feplugin` 
    WHERE  user_id = $user_id AND post_id =  $post_id ";
        return $wpdb->get_results($get_table);
    }

    /**
     * @param $user_id
     * @param $post_id
     * @param $favorited
     */
    public function fe_query_update_like($user_id, $post_id, $favorited)
    {
        global $wpdb;
        $wpdb->query("UPDATE wp_favorite_posts_feplugin
    SET favorited =  $favorited , updated_at = CURRENT_TIMESTAMP  
    WHERE  user_id =  $user_id  AND post_id =  $post_id ");
    }

    /**
     * @param $user_id
     * @param $post_id
     * @throws Exception
     */
    public function fe_query_insert_like($user_id, $post_id)
    {
        global $wpdb;

        if (get_post($post_id) === null || get_user_by('id' ,$user_id) === false) {
            throw  new Exception('post or user not exist');
        }
        $insert_table = "INSERT INTO wp_favorite_posts_feplugin
                    (
                       user_id,
                       post_id,
                       favorited,
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
        $wpdb->query($insert_table);
    }
}