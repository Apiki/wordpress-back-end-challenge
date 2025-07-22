<?php

class Favorite_Posts_Database {

    private $table_name;
    private $charset_collate;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'favorite_posts';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    public function create_table() {
        if ( $this->table_exists() ) {
            return;
        }

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            favorited_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_post_id (user_id,post_id)
        ) {$this->charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function delete_table() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );
    }

    private function table_exists() {
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $this->table_name ) ) === $this->table_name;
    }

    public function add_favorite( $user_id, $post_id ) {
        global $wpdb;
        $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'favorited_at' => current_time( 'mysql' ),
            ),
            array(
                '%d',
                '%d',
                '%s',
            )
        );
        return $wpdb->insert_id;
    }

    public function remove_favorite( $user_id, $post_id ) {
        global $wpdb;
        return $wpdb->delete(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
            ),
            array(
                '%d',
                '%d',
            )
        );
    }

    public function is_favorited( $user_id, $post_id ) {
        global $wpdb;
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );
        return (bool) $count;
    }

    public function get_favorite_count( $post_id ) {
        global $wpdb;
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE post_id = %d",
                $post_id
            )
        );
        return (int) $count;
    }

    public function get_user_favorites( $user_id ) {
        global $wpdb;
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$this->table_name} WHERE user_id = %d",
                $user_id
            )
        );
        return array_map( 'absint', $results );
    }
}