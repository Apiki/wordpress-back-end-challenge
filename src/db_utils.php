<?php

if (!defined("ABSPATH")) exit;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


class Database {
    public static function createTable() {
        global $wpdb;
    
        $table = $wpdb->prefix . "user_bookmarks";

        $collation = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE $table (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            post_id BIGINT(20) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_bookmark (user_id, post_id)
        ) $collation;";
    
        dbDelta($sql);
    }



    public static function addBookmark($user_id, $post_id) {
        global $wpdb;

        $table = $wpdb->prefix . "user_bookmarks";

        $wpdb->replace($table, ["user_id" => $user_id, "post_id" => $post_id]);
    }



    public static function removeBookmark($user_id, $post_id) {
        global $wpdb;

        $table = $wpdb->prefix . "user_bookmarks";

        $wpdb->delete($table, ["user_id" => $user_id, "post_id" => $post_id]);
    }



    public static function isBookmarked($user_id, $post_id) {
        global $wpdb;

        $table = $wpdb->prefix . "user_bookmarks";

        return ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d AND post_id = %d", $user_id, $post_id)) > 0);
    }



    public static function getBookmarks($user_id) {
        global $wpdb;

        $table = $wpdb->prefix . "user_bookmarks";

        return $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $table WHERE user_id = %d", $user_id));
    }
}