<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Apiki_Favorites_Service {

    /**
     * Returns the full table name (including prefix).
     *
     * @return string
     */
    protected function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . APIKI_FAV_TABLE_NAME;
    }

    /**
     * Adds a post to the user's favorites.
     *
     * @param int $user_id
     * @param int $post_id
     *
     * @return string status: 'favorited' or 'already_favorited'
     */
    public function add_favorite( $user_id, $post_id ) {
        global $wpdb;

        $table_name = $this->get_table_name();

        $inserted = $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'post_id' => $post_id,
            ],
            [ '%d', '%d' ]
        );

        if ( false === $inserted ) {
            return 'already_favorited';
        }

        return 'favorited';
    }

    /**
     * Remove a post from the user's favorites.
     *
     * @param int $user_id
     * @param int $post_id
     *
     * @return string status: 'unfavorited' or 'not_found'
     */
    public function remove_favorite( $user_id, $post_id ) {
        global $wpdb;

        $table_name = $this->get_table_name();

        $deleted = $wpdb->delete(
            $table_name,
            [
                'user_id' => $user_id,
                'post_id' => $post_id,
            ],
            [ '%d', '%d' ]
        );

        if ( $deleted ) {
            return 'unfavorited';
        }

        return 'not_found';
    }

    /**
     * Returns IDs of posts favorited by the user.
     *
     * @param int $user_id
     *
     * @return int[]
     */
    public function get_favorites_by_user( $user_id ) {
        global $wpdb;

        $table_name = $this->get_table_name();

        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$table_name} WHERE user_id = %d",
                $user_id
            )
        );

        return array_map( 'intval', $post_ids );
    }

    /**
     * Check if a post has been favored by the user.
     *
     * @param int $user_id
     * @param int $post_id
     *
     * @return bool
     */
    public function is_favorited( $user_id, $post_id ) {
        global $wpdb;

        $table_name = $this->get_table_name();

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );

        return ( $exists > 0 );
    }
}