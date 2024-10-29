<?php

namespace LikePosts\Models;

class LikePostsModel
{
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'most_liked';
    }

    public function create_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function like($user_id, $post_id)
    {
        global $wpdb;
        $wpdb->insert($this->table_name, [
            'user_id' => $user_id,
            'post_id' => $post_id
        ]);
    }

    public function dislike($user_id, $post_id)
    {
        global $wpdb;
        $wpdb->delete($this->table_name, [
            'user_id' => $user_id,
            'post_id' => $post_id
        ]);
    }

    public function user_has_liked_post($user_id, $post_id)
    {
        global $wpdb;

        if (!$user_id || !$post_id) {
            return false;
        }

        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        );

        $like_count = $wpdb->get_var($query);

        return $like_count > 0;
    }
}
