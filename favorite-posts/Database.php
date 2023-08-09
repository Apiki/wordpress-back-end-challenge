<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Database
{
    public static function createTable()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $tableName = $wpdb->prefix . 'favorite_posts';
        $sql = "CREATE TABLE $tableName (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_favorite (user_id, post_id)
    ) $charset_collate;";
        dbDelta($sql);
    }

    public static function dropTable()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';
        $wpdb->query("DROP TABLE IF EXISTS $tableName");
    }

    public static function addFavorite($userId, $postId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';
        $wpdb->insert($tableName, array('user_id' => $userId, 'post_id' => $postId));
    }

    public static function removeFavorite($favoriteId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';
        $wpdb->delete($tableName, array('id' => $favoriteId));
    }

    public static function searchFavorite($userId, $postId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $tableName WHERE user_id = %d AND post_id = %d",
            $userId,
            $postId
        ));
    }

    public static function getFavoritedPostsByUserId($userId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'favorite_posts';
        $favoritedPosts = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM $tableName WHERE user_id = %d",
            $userId
        ));
        return $favoritedPosts;
    }
}
