<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Apiki_Favorites_Installer {

    const DB_VERSION = '1.0.0';
    const OPTION_DB_VERSION = 'apiki_favorites_db_version';

    public static function activate(): void
    {
        self::createTable();
        update_option( self::OPTION_DB_VERSION, self::DB_VERSION );
    }

    public static function getTableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'apiki_favorites';
    }

    private static function createTable(): void
    {
        global $wpdb;

        $table_name      = self::getTableName();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_post_unique (user_id, post_id),
			KEY user_id (user_id),
			KEY post_id (post_id)
		) {$charset_collate};";

        dbDelta( $sql );
    }
}