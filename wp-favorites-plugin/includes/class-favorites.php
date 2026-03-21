<?php

class WPFAV_Favorites {

    public static function toggle_favorite($user_id, $post_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'favorites';

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ));

        if ($exists) {
            $wpdb->delete($table, [
                'user_id' => $user_id,
                'post_id' => $post_id
            ]);

            return ['status' => 'removed'];
        }

        $wpdb->insert($table, [
            'user_id' => $user_id,
            'post_id' => $post_id
        ]);

        return ['status' => 'added'];
    }

    public static function get_user_favorites($user_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'favorites';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM $table WHERE user_id = %d",
            $user_id
        ));
    }
}