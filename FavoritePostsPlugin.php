<?php

class FavoritePostsPlugin
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = $GLOBALS['wpdb']->prefix . 'post_favorites';
    }

    public function activate()
    {
        flush_rewrite_rules();sadad
        $this->createFavoritesTable();
    }

    public function deactivate()
    {
        flush_rewrite_rules();
    }

    /**
     * Favorites a specified post. If the post is already favorited,
     * then it will be removed.
     * @return array 
     */
    public function favorite($data)
    {
        if (!is_user_logged_in()) {
            return new WP_Error('rest_unauthorized', 'You must be logged in to favorite a post.');
        }

        $user_id = get_current_user_id();
        $post_id = $data['id'];
        
        global $wpdb;

        if ($this->favorited($user_id, $post_id)) {
            $wpdb->delete($this->tableName, [
                'user_id' => $user_id,
                'post_id' => $post_id
            ]);

            return ['status' => 'Deleted'];
        }

        $wpdb->insert($this->tableName, [
            'user_id' => $user_id,
            'post_id' => $post_id
        ]);

        return ['status' => 'Favorited'];
    }

    /**
     * Gets all users that favorited a specified post.
     * @param array $data The post's id.
     * @return array The users that favorited the post.
     */
    public function favorites($data)
    {
        global $wpdb;

        $post_id = $data['id'];
        $usersTable = $wpdb->prefix . 'users';

        // Selects all columns from the users' table and only post_id from the favorites one
        $sql = "SELECT u.*, pf.post_id FROM $usersTable AS u
                INNER JOIN $this->tableName AS pf
                ON u.ID = pf.user_id AND pf.post_id = $post_id";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Indicates whether a user has favorited a post.
     * @param integer $user_id
     * @param integer $post_id
     * @return bool True if the user has already favorited the post; otherwise, false.
     */
    public function favorited(int $user_id, int $post_id)
    {
        $sql = "SELECT COUNT(*) FROM $this->tableName
                WHERE user_id = $user_id AND post_id = $post_id";

        global $wpdb;
        return $wpdb->get_var($sql) == 1;
    }

    /**
     * Creates a table in the database to store the favorites.
     * @return void
     */
    private function createFavoritesTable()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $tablePrefix = $GLOBALS['wpdb']->prefix;
        $usersTable = $tablePrefix . 'users';
        $postsTable = $tablePrefix . 'posts';

        $sql = "CREATE TABLE IF NOT EXISTS $this->tableName (
            id bigint(20) UNSIGNED AUTO_INCREMENT NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            post_id bigint(20) UNSIGNED NOT NULL,
            UNIQUE (user_id),
            UNIQUE (post_id),
            PRIMARY KEY  (id),
            FOREIGN KEY  (user_id) REFERENCES $usersTable (ID),
            FOREIGN KEY  (post_id) REFERENCES $postsTable (ID)
        )";

        dbDelta($sql);
    }
}