<?php

function fp_toggle_favorite($data)
{
    global $wpdb;

    $user_id = get_current_user_id();
    $post_id = (int) $data['post_id'];
    $table_name = $wpdb->prefix . 'favorite_posts';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
        $user_id, $post_id
    ));

    if ($exists) {
        $wpdb->delete($table_name, ['id' => $exists]);
        return ['status' => 'removed'];
    } else {
        $wpdb->insert($table_name, ['user_id' => $user_id, 'post_id' => $post_id]);
        return ['status' => 'added'];
    }
}
