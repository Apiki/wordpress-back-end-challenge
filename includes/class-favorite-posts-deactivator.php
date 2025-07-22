<?php

class Favorite_Posts_Deactivator {

    public static function deactivate() {
        $delete_data = isset( $_GET['_favorite_posts_delete_data'] ) ? sanitize_text_field( wp_unslash( $_GET['_favorite_posts_delete_data'] ) ) : 'no';

        if ( 'yes' === $delete_data ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-favorite-posts-database.php';
            $database = new Favorite_Posts_Database();
            $database->delete_table();
        }
    }
}