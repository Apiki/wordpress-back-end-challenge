<?php

declare(strict_types=1);

if (!defined("ABSPATH")) {
    exit;
}

class Apiki_Favorites_DB
{
    public static function create_favorites_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "apiki_favorites";

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            post_id bigint(20) UNSIGNED NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post_id (user_id, post_id)
        ) $charset_collate;";

        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        dbDelta($sql);
    }

    public static function drop_favorites_table(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "apiki_favorites";
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
