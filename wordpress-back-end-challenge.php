<?php
/*
Plugin Name: Wordpress Back-End Challenge plugin
Description: Plugin voltado para o teste de dev PHP
Version: 1.0
Author: Igor Gabriel de Sousa Silva
Text Domain: Plugin Teste WP
*/

    global $wpdb;
    $table_name = 'like_posts';
    $charset_collate = $wpdb->get_charset_collate();
    $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

    if ( ! $wpdb->get_var( $query ) == $table_name ) {
        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          user_id BIGINT(20) UNSIGNED NOT NULL,
          FOREIGN KEY (user_id) REFERENCES wp_users(id),
          post_id BIGINT(20) UNSIGNED NOT NULL,
          FOREIGN KEY (post_id) REFERENCES wp_posts(id),
          created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          updated_at datetime,
          PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

function like_post_user($request) {
    global $wpdb;

        if($request['post_id'] != '' && $request['post_id'] != null ) {
            $post_id = $request['post_id'];
            $user_id = $GLOBALS['user_id'];
            $table_name = 'like_posts';
            $results = $wpdb->get_row("SELECT * FROM $table_name WHERE post_id = '$post_id' AND user_id = '$user_id'");

            if($results) {
                $delete = $wpdb->delete( $table_name, array( 'user_id' => $user_id, 'post_id' => $post_id ) );
                $response = array(
                    'success' => true,
                    'message' => 'Curtida deletada com Sucesso!',
                    'data' => $delete
                );
            } else {
                $like = $wpdb->insert('like_posts', array(
                    'user_id' => $GLOBALS['user_id'],
                    'post_id' => $request['post_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ));
                $response = array(
                    'success' => true,
                    'message' => 'Curtida adicionada com sucesso!',
                    'data' => $like
                );
            }

        } else {
            $response = array(
                'success' => false,
                'message' => 'Confira novamente os parâmetros passados e tente de novo.'
            );
        }

        return rest_ensure_response($response);
}

function register_routes_like_post(){


    register_rest_route('testewp/v1', 'like', array(
        'methods' => 'POST',
        'callback' => 'like_post_user',
        'permission_callback' => 'permission_callback'
    ));
    register_rest_route('testewp/v1', 'unlike', array(
        'methods' => 'POST',
        'callback' => 'unlike_post_user',
        'permission_callback' => 'permission_callback'
    ));

}
    function permission_callback() {
        if(is_user_logged_in()){
            $GLOBALS['user_id'] = get_current_user_id();
            return true;
        }
        return false;
    }

add_action( 'rest_api_init', 'register_routes_like_post');


