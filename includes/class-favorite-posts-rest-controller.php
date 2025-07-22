<?php

class Favorite_Posts_REST_Controller {

    private $namespace = 'favorite-posts/v1';
    private $database;

    public function __construct( $database ) {
        $this->database = $database;
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/favorite/(?P<post_id>\d+)', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( $this, 'favorite_post' ),
            'permission_callback' => array( $this, 'user_logged_in_permission' ),
            'args'                => array(
                'post_id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param ) && get_post_status( (int) $param );
                    },
                ),
            ),
        ) );

        register_rest_route( $this->namespace, '/unfavorite/(?P<post_id>\d+)', array(
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => array( $this, 'unfavorite_post' ),
            'permission_callback' => array( $this, 'user_logged_in_permission' ),
            'args'                => array(
                'post_id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param ) && get_post_status( (int) $param );
                    },
                ),
            ),
        ) );

        register_rest_route( $this->namespace, '/is-favorited/(?P<post_id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'is_post_favorited' ),
            'permission_callback' => array( $this, 'user_logged_in_permission' ),
            'args'                => array(
                'post_id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param ) && get_post_status( (int) $param );
                    },
                ),
            ),
        ) );

        register_rest_route( $this->namespace, '/favorites', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( $this, 'get_user_favorites' ),
            'permission_callback' => array( $this, 'user_logged_in_permission' ),
        ) );
    }

    public function favorite_post( WP_REST_Request $request ) {
        $post_id = (int) $request['post_id'];
        $user_id = get_current_user_id();

        if ( $this->database->is_favorited( $user_id, $post_id ) ) {
            return new WP_REST_Response( array( 'message' => 'Post já favoritado.', 'favorited' => true ), 200 );
        }

        $result = $this->database->add_favorite( $user_id, $post_id );

        if ( $result ) {
            return new WP_REST_Response( array( 'message' => 'Post favoritado com sucesso.', 'favorited' => true, 'post_id' => $post_id ), 200 );
        } else {
            return new WP_Error( 'favorite_failed', 'Não foi possível favoritar o post.', array( 'status' => 500 ) );
        }
    }

    public function unfavorite_post( WP_REST_Request $request ) {
        $post_id = (int) $request['post_id'];
        $user_id = get_current_user_id();

        if ( ! $this->database->is_favorited( $user_id, $post_id ) ) {
            return new WP_REST_Response( array( 'message' => 'Post não está favoritado.', 'favorited' => false ), 200 );
        }

        $result = $this->database->remove_favorite( $user_id, $post_id );

        if ( $result ) {
            return new WP_REST_Response( array( 'message' => 'Post desfavoritado com sucesso.', 'favorited' => false, 'post_id' => $post_id ), 200 );
        } else {
            return new WP_Error( 'unfavorite_failed', 'Não foi possível desfavoritar o post.', array( 'status' => 500 ) );
        }
    }

    public function is_post_favorited( WP_REST_Request $request ) {
        $post_id = (int) $request['post_id'];
        $user_id = get_current_user_id();

        $is_favorited = $this->database->is_favorited( $user_id, $post_id );

        return new WP_REST_Response( array( 'is_favorited' => $is_favorited ), 200 );
    }

    public function get_user_favorites( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $favorite_post_ids = $this->database->get_user_favorites( $user_id );

        $favorited_posts_data = array();
        if ( ! empty( $favorite_post_ids ) ) {
            $args = array(
                'post_type'      => 'post',
                'post__in'       => $favorite_post_ids,
                'posts_per_page' => -1,
                'orderby'        => 'post__in',
                'fields'         => 'ids',
            );
            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $post = WP_Post::get_instance( $post_id );
                    if ( $post ) {
                        $favorited_posts_data[] = array(
                            'id'    => $post->ID,
                            'title' => $post->post_title,
                            'link'  => get_permalink( $post->ID ),
                            'date'  => $post->post_date,
                        );
                    }
                }
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response( array( 'favorites' => $favorited_posts_data ), 200 );
    }

    public function user_logged_in_permission( WP_REST_Request $request ) {
        return is_user_logged_in();
    }
}