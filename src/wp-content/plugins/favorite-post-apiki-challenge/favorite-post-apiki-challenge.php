<?php
/*
Plugin Name: Favorite
Plugin URI: https://br.wordpress.org/plugins/
Description: Plugin criado para o teste Wordpress da Apiki para favoritar posts.
Version: 0.1.0
Author: Gustavo Breternitz
Author URI: https://www.linkedin.com/in/gustavo-breternitz-9b83901ba/
License: GPLv2 or later
*/
require_once( plugin_dir_path( __FILE__ ) . 'class.favorite-post-apiki-challenge.php' );
add_action('wp', ['Favorite_Apiki', 'init']);
function start_plugin()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $wpdb->query("CREATE TABLE {$wpdb->prefix}favorite_post (
        id_favorite BIGINT NOT NULL AUTO_INCREMENT, 
        id_user BIGINT NOT NULL,
        id_post BIGINT NOT NULL,
        st_favorite varchar(3) NOT NULL,
        PRIMARY KEY  (id_favorite)) $charset_collate;");

}

register_activation_hook(__FILE__, 'start_plugin');

function stop_plugin()
{
    global $wpdb;

    $wpdb->query("DROP TABLE {$wpdb->prefix}favorite_post");
}
register_deactivation_hook(__FILE__, 'stop_plugin');

add_action( 'rest_api_init', 'init' );
function init()
{
    register_rest_route( 'v2', '/favorite', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback'=> 'favorite_unfavorite_post'
        )
    ));

    register_rest_route( 'v2', '/check-favorite', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback'=> 'check_favorite'
        )
    ));
}

function favorite_unfavorite_post($request)
{
    Favorite_Apiki::favorite_unfavorite_post($request->get_params());
}

function check_favorite($request)
{
    Favorite_Apiki::check_favorite_post($request->get_params());
}

function ajax_check_user_logged_in()
{
    wp_send_json_success(['user' => get_current_user_id()]);
}
add_action('wp_ajax_is_user_logged_in', 'ajax_check_user_logged_in');


