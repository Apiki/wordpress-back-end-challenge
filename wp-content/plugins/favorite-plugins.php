<?php
/*
Plugin Name: Favoritar Posts
Description: Plugin para favoritar posts usando a WP REST API.
Version: 1.0
Author: Juliano Firme
*/

function favorite_posts_api_routes() {
    register_rest_route( 'favorite-posts', '/favorite', array(
        'methods' => 'POST',
        'callback' => 'toggle_favorite_posts',
    ) );
}
add_action( 'rest_api_init', 'favorite_posts_api_routes' );

function toggle_favorite_posts( $request ) {
    $user_id = get_current_user_id();
    $post_id = $request['post_id'];

    $is_favorite = post_is_already_favorite( $user_id, $post_id );

    if ( $is_favorite ) {
        remove_post_favorite( $user_id, $post_id );
        $response = array( 'message' => 'Post removido dos favoritos.' );
    } else {
        add_post_favorite( $user_id, $post_id );
        $response = array( 'message' => 'Post adicionado aos favoritos.' );
    }

    return rest_ensure_response( $response );
}

function post_is_already_favorite( $user_id, $post_id ) {
    $favorites = get_user_meta( $user_id, 'favorite_posts', true );
    return in_array( $post_id, $favorites );
}

function add_post_favorite( $user_id, $post_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'favorite_posts';

    $data = array(
        'user_id' => $user_id,
        'post_id' => $post_id
    );

    $wpdb->insert( $table_name, $data );
}

function remove_post_favorite( $user_id, $post_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'favorite_posts';

    $wpdb->delete(
        $table_name,
        array(
            'user_id' => $user_id,
            'post_id' => $post_id
        )
    );
}

register_activation_hook( __FILE__, 'create_favorite_posts_table' );
function create_favorite_posts_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'favorite_posts';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->users} (ID),
        FOREIGN KEY (post_id) REFERENCES {$wpdb->posts} (ID)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
