<?php

namespace FavoritePostPlugin\Controllers;

use FavoritePostPlugin\Repositories\FavoritePostRepository;

class FavoritePostsController
{
    private $namespace = 'apiki-favorite-post/v1';
    private $repository;

    public function __construct()
    {
        $this->repository = new FavoritePostRepository();
    }

    public function registerRoutes()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route($this->namespace, '/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array( $this, 'checkPost' ),
                'permission_callback' => array( $this, 'validate' ),
            ) );
            register_rest_route($this->namespace, '/favorite/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array( $this, 'favoritePost' ),
                'permission_callback' => array( $this, 'validate' ),
            ) );
            register_rest_route( $this->namespace, '/unfavorite/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array( $this, 'unfavoritePost'),
                'permission_callback' => array( $this, 'validate' ),
            ) );
        } );
    }

    public function validate(\WP_REST_Request $request)
    {
        if ( ! is_user_logged_in() )
            return new \WP_Error('not_logged_in', 'User not logged in');

        $post_id = $request->get_param('id');
        if ( ! get_post($post_id) ) {
            return new \WP_Error('post_not_found', 'Post not found');
        }

        return true;
    }

    public function checkPost(\WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('id');
        $favorite = $this->repository->get($user_id, $post_id);
        return new \WP_REST_Response( array( 'favorite' => isset($favorite) ) );
    }

    public function favoritePost(\WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('id');
        $favorite = $this->repository->get($user_id, $post_id);
        if ( ! $favorite ) {
            $favorite = $this->repository->create($user_id, $post_id);
        }

        return new \WP_REST_Response( array( 'success' => isset($favorite) ) );
    }

    public function unfavoritePost(\WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('id');
        $this->repository->delete($user_id, $post_id);
        return new \WP_REST_Response( array( 'success' => true ) );
    }
}
