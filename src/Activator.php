<?php

declare(strict_types=1);

namespace Apiki\Favorites;

defined('ABSPATH') || exit;

/**
 * Plugin activation handler.
 *
 * Creates the custom table used to persist favorites. We deliberately use
 * a dedicated table (not user_meta or post_meta) because the spec asks for
 * persistence in "uma tabela a parte" and because user/post meta would
 * require expensive full-table scans to answer "which posts has user X
 * favorited?" or "how many users favorited post Y?".
 */
final class Activator
{
    public static function activate(): void
    {
        global $wpdb;

        $table           = Plugin::table_name($wpdb);
        $charset_collate = $wpdb->get_charset_collate();

        // Composite UNIQUE on (user_id, post_id) prevents duplicate
        // favorites at the DATABASE level — even if a race condition
        // bypasses the application-level check, the DB rejects the row.
        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY user_id (user_id),
            KEY post_id (post_id)
        ) {$charset_collate};";

        // dbDelta is the WordPress-blessed way to run schema changes.
        // On first install it creates the table; on subsequent plugin
        // updates, if the CREATE TABLE statement above changes, dbDelta
        // generates the appropriate ALTER TABLE without losing data.
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
