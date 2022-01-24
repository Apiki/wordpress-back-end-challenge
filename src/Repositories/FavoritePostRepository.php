<?php

namespace FavoritePostPlugin\Repositories;

use FavoritePostPlugin\FavoritePostPlugin;

class FavoritePostRepository extends Repository
{
    public function get($user_id, $post_id) {
        global $wpdb;
        $table_name = FavoritePostPlugin::tableName();
        return $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$table_name} WHERE post_id = %d AND user_id = %d", $post_id, $user_id ));
    }

    function create($user_id, $post_id) {
        global $wpdb;
        $table_name = FavoritePostPlugin::tableName();
        $wpdb->insert($table_name, array( 'user_id' => $user_id, 'post_id' => $post_id));
        return $wpdb->insert_id;
    }

    public function delete($user_id, $post_id) {
        global $wpdb;
        $table_name = FavoritePostPlugin::tableName();
        $wpdb->delete($table_name, array( 'user_id' => $user_id, 'post_id' => $post_id));
    }
}
