<?php

declare(strict_types=1);

namespace ApikiFavorites;

final class Database
{
    public static function tableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'favorite_posts';
    }

    public static function createTable(): void
    {
        global $wpdb;

        $table = self::tableName();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function addFavorite(int $userId, int $postId): int|false
    {
        global $wpdb;

        if (self::isFavorite($userId, $postId)) {
            return false;
        }

        $result = $wpdb->insert(
            self::tableName(),
            [
                'user_id' => $userId,
                'post_id' => $postId,
            ],
            ['%d', '%d']
        );

        return $result !== false ? (int) $wpdb->insert_id : false;
    }

    public static function removeFavorite(int $userId, int $postId): bool
    {
        global $wpdb;

        $deleted = $wpdb->delete(
            self::tableName(),
            [
                'user_id' => $userId,
                'post_id' => $postId,
            ],
            ['%d', '%d']
        );

        return $deleted > 0;
    }

    /**
     * @return array<int, object{id: int, post_id: int, post_title: string, created_at: string}>
     */
    public static function getFavorites(int $userId): array
    {
        global $wpdb;

        $table = self::tableName();
        $posts = $wpdb->posts;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT f.id, f.post_id, p.post_title, f.created_at
                 FROM {$table} f
                 JOIN {$posts} p ON f.post_id = p.ID
                 WHERE f.user_id = %d
                 ORDER BY f.created_at DESC",
                $userId
            )
        );

        return $results ?: [];
    }

    public static function isFavorite(int $userId, int $postId): bool
    {
        global $wpdb;

        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM " . self::tableName() . " WHERE user_id = %d AND post_id = %d",
                $userId,
                $postId
            )
        );

        return $count > 0;
    }
}
