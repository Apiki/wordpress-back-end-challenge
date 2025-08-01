<?php
/**
 * @package Apiki_Favorites
 * @wordpress-plugin
 */

class Apiki_Favorites_Database {
    private static $table_name = 'apiki_user_favorites';

    public function __construct() {
        // Add any initialization code if needed
    }
    
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }
    
    public static function add_favorite($user_id, $post_id) {
        global $wpdb;
        $table_name = self::get_table_name();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id
            ),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    public static function remove_favorite($user_id, $post_id) {
        global $wpdb;
        $table_name = self::get_table_name();
        
        $result = $wpdb->delete(
            $table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id
            ),
            array('%d', '%d')
        );
        
        return $result !== false;
    }
    
    public static function get_user_favorites($user_id) {
        global $wpdb;
        $table_name = self::get_table_name();
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id FROM $table_name WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
return array_map(function($item) {
    return (int) $item['post_id'];
}, (array) $results);
    }
    
    public static function is_post_favorited($user_id, $post_id) {
        global $wpdb;
        $table_name = self::get_table_name();
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );
        
        return $count > 0;
    }
}