<?php
function bookmarkposts_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix.'bookmarkposts';
    $sql = "CREATE TABLE $table_name (
        id int(10) NOT NULL AUTO_INCREMENT,
        post_id int(10) NOT NULL,
        user_id int(10) NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    dbDelta( $sql );
}
