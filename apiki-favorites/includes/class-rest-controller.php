<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Apiki_Favorites_REST_Controller {

    /**
     * @var Apiki_Favorites_Service
     */
    protected $service;

    public function __construct( Apiki_Favorites_Service $service ) {
        $this->service = $service;
    }

    /**
     * Registers the REST API routes.
     */
    public function register_routes() {
        register_rest_route(
            APIKI_FAV_REST_NAMESPACE,
            '/favorites',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_favorites' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'add_favorite' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => [
                        'post_id' => [
                            'required'          => true,
                            'validate_callback' => [ $this, 'validate_post_id' ],
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'remove_favorite' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => [
                        'post_id' => [
                            'required'          => true,
                            'validate_callback' => [ $this, 'validate_post_id' ],
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
            ]
        );

        // Optional: route to check if a specific post is favorited.
        register_rest_route(
            APIKI_FAV_REST_NAMESPACE,
            '/favorites/check',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'check_favorite' ],
                'permission_callback' => [ $this, 'permissions_check' ],
                'args'                => [
                    'post_id' => [
                        'required'          => true,
                        'validate_callback' => [ $this, 'validate_post_id' ],
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ]
        );
    }

    /**
     * Ensures the user is logged in.
     */
    public function permissions_check( WP_REST_Request $request ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Você precisa estar logado para usar favoritos.', 'apiki-favorites' ),
                [ 'status' => 401 ]
            );
        }

        return true;
    }

    /**
     * Validates if the post exists.
     */
    public function validate_post_id( $param, WP_REST_Request $request, $key ) {
        $post = get_post( (int) $param );
        return ( $post instanceof WP_Post );
    }

    /**
     * GET /favorites
     * Lists the posts favorited by the logged-in user.
     */
    public function get_favorites( WP_REST_Request $request ) {
        $user_id  = get_current_user_id();
        $post_ids = $this->service->get_favorites_by_user( $user_id );

        $favorites = [];

        foreach ( $post_ids as $post_id ) {
            $post = get_post( $post_id );
            if ( ! $post ) {
                continue;
            }

            $favorites[] = [
                'id'    => $post->ID,
                'title' => get_the_title( $post ),
                'link'  => get_permalink( $post ),
            ];
        }

        return rest_ensure_response(
            [
                'user_id'   => $user_id,
                'favorites' => $favorites,
            ]
        );
    }

    /**
     * POST /favorites
     * Add a post to your favorites.
     */
    public function add_favorite( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $post_id = (int) $request->get_param( 'post_id' );

        $status = $this->service->add_favorite( $user_id, $post_id );

        $http_status = ( 'favorited' === $status ) ? 201 : 200;

        return new WP_REST_Response(
            [
                'status'  => $status,
                'user_id' => $user_id,
                'post_id' => $post_id,
            ],
            $http_status
        );
    }

    /**
     * DELETE /favorites
     * Remove a post from your favorites.
     */
    public function remove_favorite( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $post_id = (int) $request->get_param( 'post_id' );

        $status = $this->service->remove_favorite( $user_id, $post_id );

        $http_status = ( 'unfavorited' === $status ) ? 200 : 404;

        return new WP_REST_Response(
            [
                'status'  => $status,
                'user_id' => $user_id,
                'post_id' => $post_id,
            ],
            $http_status
        );
    }

    /**
     * GET /favorites/check?post_id=123
     * Check if a post has been favorited.
     */
    public function check_favorite( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $post_id = (int) $request->get_param( 'post_id' );

        $is_favorited = $this->service->is_favorited( $user_id, $post_id );

        return rest_ensure_response(
            [
                'user_id'      => $user_id,
                'post_id'      => $post_id,
                'is_favorited' => $is_favorited,
            ]
        );
    }
}