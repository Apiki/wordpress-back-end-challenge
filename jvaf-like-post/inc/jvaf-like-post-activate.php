<?php
/**
 * @package JvafLikePost
 */

class JvafLikePostActivate {
    function onActivate(){  
        $sql = "CREATE TABLE $this->table_name (
            like_id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            like_post_id mediumint(7) NOT NULL DEFAULT 0,
            like_user_id mediumint(7) NOT NULL DEFAULT 0,
            PRIMARY KEY  (like_id)
        ) $this->charset;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        flush_rewrite_rules();
    }
}