<?php

namespace WordPressBackEndChallenge;

defined('ABSPATH') || exit;

class DbHandler {
    private $table_name;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'favorite_posts';
    }

    public function createTable() {
        $table           = $this->table_name;
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id_post_id (user_id, post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }

    private function dropTable() {
        $this->wpdb->query("DROP TABLE IF EXISTS $this->table_name");
    }

    public function getAllFavoritesByUserId($user_id) {
        $query = $this->wpdb->prepare("SELECT * FROM $this->table_name WHERE user_id = %d", $user_id);
        return $this->wpdb->get_results($query);
    }

    public function getFavoriteByUserAndPost($user_id, $post_id) {
        $query = $this->wpdb->prepare("SELECT * FROM $this->table_name WHERE user_id = %d AND post_id = %d", $user_id, $post_id);
        return $this->wpdb->get_results($query);
    }

    public function addFavorite($user_id, $post_id) {
        $existing = $this->getFavoriteByUserAndPost($user_id, $post_id);

        if (!empty($existing)) :
            return (int) $existing[0]->id;
        endif;

        $result = $this->wpdb->insert($this->table_name, ['user_id' => $user_id, 'post_id' => $post_id]);

        return $this->wpdb->insert_id;
    }

    public function removeFavorite($user_id, $post_id) {
        return $this->wpdb->delete($this->table_name, ['user_id' => $user_id, 'post_id' => $post_id]);
    }
}