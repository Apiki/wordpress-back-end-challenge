<?php

class Favorite_Posts_Activator {

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'favorite_posts';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            favorited_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post_pair (user_id,post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        if ( get_option( 'favorite_posts_delete_data_on_uninstall' ) === false ) {
            add_option( 'favorite_posts_delete_data_on_uninstall', 'no' ); 
        }
    }
}