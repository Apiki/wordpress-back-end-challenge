<?php

class WPFAV_Database {

    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'favorites';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        add_option('wpfav_db_version', WPFAV_VERSION);
    }

    public static function maybe_update() {
        $installed_version = get_option('wpfav_db_version');

        if ($installed_version !== WPFAV_VERSION) {

            self::create_table();

            update_option('wpfav_db_version', WPFAV_VERSION);
        }
    }
}