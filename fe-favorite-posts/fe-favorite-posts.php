<?php
/**
 * @package  fe-favorite-posts
 */

/*
 Plugin Name: Favorite Posts - Felipe
Plugin URI:  https://www.linkedin.com/in/felipe-lopes-serra/
Description: This plugin allows you to favorite and unfavorite posts
Version: 1.0.0
Author: Felipe Lopes
License: MIT
Text Domain: fe-favorite-posts
 */

require_once ABSPATH . "wp-admin/includes/upgrade.php";


if (!defined("ABSPATH")) {
    exit();
}
require_once( __DIR__ .  '/model/Queries.php');

class FavoritePosts
{
    function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $create_table = "CREATE TABLE IF NOT EXISTS wp_favorite_posts_feplugin (
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          user_id bigint(20) UNSIGNED NOT NULL,
          post_id bigint(20) UNSIGNED NOT NULL,
          favorited boolean DEFAULT false,
          created_at datetime NOT NULL,
          updated_at datetime NOT NULL,
          PRIMARY  KEY  (ID),
    	  INDEX (ID),
          FOREIGN KEY (user_id) REFERENCES wp_users(ID),
          FOREIGN KEY (post_id) REFERENCES wp_posts(ID)
        ) $charset_collate;";
        $wpdb->query($create_table);
    }
}

//activation
register_activation_hook(__FILE__, [new FavoritePosts(), "activate"]);

// WP REST API
$queries = new \model\Queries;
/**
 * @param $data
 * @return WP_REST_Response
 */
function favorite_posts($data):  WP_REST_Response{
    try{
       global $queries;
        $user_id = $data['user_id'];
        $post_id = $data['post_id'];
        $favorited = $queries->fe_query_get_favorited($user_id, $post_id);
        if(  sizeof($favorited) > 0 && $favorited[0]->favorited === '1'){
            $response = new WP_REST_Response( 'favorite already exist' );
            $response->set_status( 200 );
            return $response;
        }
        if( sizeof($favorited) > 0 && $favorited[0]->favorited === '0'){
            $queries->fe_query_update_like($user_id, $post_id, 1);
            $response = new WP_REST_Response( 'favorited' );
            $response->set_status( 201 );
            return $response;
        }
        $queries->fe_query_insert_like($user_id, $post_id);
        $response = new WP_REST_Response( 'favorited' );
        $response->set_status( 201 );
        return $response;
    } catch (Exception $e){
        if($e->getMessage() === 'post or user not exist'){
            $response = new WP_REST_Response( $e->getMessage() );
        }else {
            $response = new WP_REST_Response('error');
        }
        $response->set_status( 404 );
        return $response;
    }
}

/**
 * @param $data
 * @return WP_REST_Response
 */
function unfavorite_posts($data): WP_REST_Response{
    try{
        global $queries;
        $user_id = $data['user_id'];
        $post_id = $data['post_id'];
        $favorited = $queries->fe_query_get_favorited($user_id, $post_id);
        if( sizeof($favorited) > 0 && $favorited[0]->favorited === '1'){
            $queries->fe_query_update_like($user_id, $post_id, 0);
            $response = new WP_REST_Response( 'unfavorited' );
            $response->set_status( 201 );
            return $response;
        }
        $response = new WP_REST_Response( 'favorite not exist' );
        $response->set_status( 200 );
        return $response;
    } catch (Exception){
        $response = new WP_REST_Response('error');
        $response->set_status( 404 );
        return $response;
    }
}

/**
 * @return void
 **/
function register_routes() {
    $version = '1';
    $namespace = 'api/v' . $version;
    $base = 'users';
    register_rest_route( $namespace, '/' . $base .
        '/(?P<user_id>\d+)'  . '/posts' . '/(?P<post_id>\d+)'. '/favorite', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'favorite_posts',
        ),
    ) );
    register_rest_route( $namespace, '/' . $base .
        '/(?P<user_id>\d+)'  . '/posts' . '/(?P<post_id>\d+)'. '/unfavorite', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'unfavorite_posts',
        ),
    ) );
}

add_action('rest_api_init', 'register_routes');




