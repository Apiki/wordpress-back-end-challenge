<?php

if (!defined('ABSPATH')) {
    exit;
}

class Favoritos_Repository
{
    private $wpdb;
    private $table;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
        $this->table = $this->wpdb->prefix . 'user_favorites';
    }

    public function create_table()
    {
        $charset_collate = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY post_id (post_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function drop_table()
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->table}");
    }

    public function toggle($user_id, $post_id)
    {
        $exists = (bool) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->table} WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );

        if ($exists) {
            $deleted = $this->wpdb->delete(
                $this->table,
                ['user_id' => $user_id, 'post_id' => $post_id],
                ['%d', '%d']
            );

            if (false === $deleted) {
                return new WP_Error('db_delete_error', 'Nao foi possivel remover o favorito.');
            }

            return 'removido';
        }

        $inserted = $this->wpdb->insert(
            $this->table,
            [
                'user_id' => $user_id,
                'post_id' => $post_id,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%s']
        );

        if (false === $inserted) {
            return new WP_Error('db_insert_error', 'Nao foi possivel favoritar o post.');
        }

        return 'adicionado';
    }

    public function get_user_favorite_ids($user_id)
    {
        $results = $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT post_id FROM {$this->table} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            )
        );

        return array_map('intval', $results);
    }
}
