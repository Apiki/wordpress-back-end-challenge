<?php
if (!defined('ABSPATH')) {
    exit;
}

class Apiki_Favorites_Repository
{

    /**
     * @var string
     */
    private $table_name;

    public function __construct()
    {
        $this->table_name = Apiki_Favorites_Installer::getTableName();
    }

    /**
     * @param int $user_id
     * @param int $post_id
     * @return bool
     */
    public function favorite($user_id, $post_id): bool
    {
        global $wpdb;

        $existing = $this->exists($user_id, $post_id);

        if ($existing) {
            return true;
        }

        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => (int)$user_id,
                'post_id' => (int)$post_id,
                'created_at' => current_time('mysql', true),
            ),
            array(
                '%d',
                '%d',
                '%s',
            )
        );

        return false !== $inserted;
    }

    /**
     * @param int $user_id
     * @param int $post_id
     * @return bool
     */
    public function unfavorite($user_id, $post_id): bool
    {
        global $wpdb;

        $deleted = $wpdb->delete(
            $this->table_name,
            array(
                'user_id' => (int)$user_id,
                'post_id' => (int)$post_id,
            ),
            array(
                '%d',
                '%d',
            )
        );

        return false !== $deleted;
    }

    /**
     * @param int $user_id
     * @param int $post_id
     * @return bool
     */
    public function exists($user_id, $post_id): bool
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT 1 FROM {$this->table_name} WHERE user_id = %d AND post_id = %d LIMIT 1",
            (int)$user_id,
            (int)$post_id
        );

        $result = $wpdb->get_var($sql);

        return !empty($result);
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function get_user_favorites($user_id): array
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT post_id, created_at
			FROM {$this->table_name}
			WHERE user_id = %d
			ORDER BY created_at DESC",
            (int)$user_id
        );

        $results = $wpdb->get_results($sql, ARRAY_A);

        if (empty($results)) {
            return array();
        }

        return $results;
    }
}