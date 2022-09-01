<?php

namespace Mazza\WordpressBackEndChallenge;

class CreateTable
{
    function jal_install(): void
    {
        global $wpdb;
        global $jal_db_version;

        $table_name = $wpdb->prefix . 'posts_like';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
          id int(10) NOT NULL AUTO_INCREMENT,
          post_id int(10) NOT NULL,
          user_id int(10) NOT NULL,
          PRIMARY KEY  (id),
          UNIQUE KEY (post_id,user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        add_option( 'jal_db_version', $jal_db_version );
    }
}