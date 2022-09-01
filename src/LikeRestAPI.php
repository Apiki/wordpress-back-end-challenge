<?php

namespace Mazza\WordpressBackEndChallenge;

use WP_REST_Response;

class LikeRestAPI
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'at_rest_init'));
    }

    /*
     * Create the initial routes
     */
    public function at_rest_init(): void
    {
        // route url: domain.com/wp-json/$namespace/$route
        $namespace = 'api-like/v1';
        $route     = 'like';

        register_rest_route( $namespace, $route, array(
            'methods' => 'POST',
            'callback' => array($this, 'like'))
        );
        register_rest_route( $namespace, $route, array(
            'methods' => 'DELETE',
            'callback' => array($this, 'unlike'))
        );
    }

    /*
     * Function to like posts
     */
    public function like($data): WP_REST_Response
    {
        global $wpdb;
        if(is_user_logged_in()){
            $table_name = $wpdb->prefix . 'posts_like';
            $user = sanitize_text_field($data['user']);
            $post = sanitize_text_field($data['post']);
            if(!$user OR !$post){
                $data =
                    [
                        "post_id" => $post,
                        "user_id" => $user,
                        "response" => 'Usuário e/ou post não preenchidos'
                    ];
            }else{
                $like = array(
                    'post_id' => $post,
                    'user_id' => $user,
                );
                $wpdb->insert($table_name, $like);
                $wpdb->insert_id;

                $data =
                    [
                        "post_id" => $post,
                        "user_id" => $user,
                        "response" => 'Like dado com sucesso no post '. $post .' pelo usuário '. $user
                    ];
            }
            $response = new WP_REST_Response($data, 200);
        }else{
            $data = 'Sem autorização, faça login para poder dar like';
            $response = new WP_REST_Response($data, 401);
        }
        return $response;
    }

    /*
     * Function to unlike posts
     */
    public function unlike($data): WP_REST_Response
    {
        global $wpdb;
        if(is_user_logged_in()){
            $table_name = $wpdb->prefix . 'posts_like';
            $user = sanitize_text_field($data['user']);
            $post = sanitize_text_field($data['post']);
            if(!$user OR !$post){
                $data =
                    [
                        "post_id" => $post,
                        "user_id" => $user,
                        "response" => 'Usuário e/ou post não preenchidos'
                    ];
            }else{
                $like = array(
                    'post_id' => $post,
                    'user_id' => $user,
                );
                $wpdb->delete($table_name, $like);

                $data =
                    [
                        "post_id" => $post,
                        "user_id" => $user,
                        "response" => 'Unlike dado com sucesso no post '. $post .' pelo usuário '. $user
                    ];

            }
            $response = new WP_REST_Response($data, 200);
        }else{
            $data = 'Sem autorização, faça login para poder dar unlike';
            $response = new WP_REST_Response($data, 401);
        }
        return $response;
    }

}