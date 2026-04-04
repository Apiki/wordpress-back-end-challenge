<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apiki_Favorites_REST_Controller {

	protected $namespace = 'apiki/v1';

	protected $repository;

	public function __construct( Apiki_Favorites_Repository $repository ) {
		$this->repository = $repository;
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/posts/(?P<post_id>\d+)/favorite',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'favorite' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'unfavorite' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
			)
		);
	}

	public function permission_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'apiki_favorites_forbidden',
				'Authentication required.',
				array( 'status' => 401 )
			);
		}

		return true;
	}

	public function favorite( WP_REST_Request $request ) {
		$post_id = (int) $request['post_id'];
		$post    = $this->get_valid_post( $post_id );
		$user_id = get_current_user_id();

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( $this->repository->exists( $user_id, $post_id ) ) {
			return new WP_Error(
				'apiki_favorites_conflict',
				'Post already favorited.',
				array( 'status' => 409 )
			);
		}

		if ( ! $this->repository->insert( $user_id, $post_id ) ) {
			return new WP_Error(
				'apiki_favorites_insert_error',
				'Could not favorite post.',
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'post_id'   => $post_id,
				'favorited' => true,
			),
			201
		);
	}

	public function unfavorite( WP_REST_Request $request ) {
		$post_id = (int) $request['post_id'];
		$post    = $this->get_valid_post( $post_id );
		$user_id = get_current_user_id();

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( ! $this->repository->exists( $user_id, $post_id ) ) {
			return new WP_Error(
				'apiki_favorites_not_found',
				'Favorite not found.',
				array( 'status' => 404 )
			);
		}

		if ( ! $this->repository->delete( $user_id, $post_id ) ) {
			return new WP_Error(
				'apiki_favorites_delete_error',
				'Could not unfavorite post.',
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'post_id'   => $post_id,
				'favorited' => false,
			),
			200
		);
	}

	protected function get_valid_post( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'post' !== $post->post_type ) {
			return new WP_Error(
				'apiki_favorites_post_not_found',
				'Post not found.',
				array( 'status' => 404 )
			);
		}

		return $post;
	}
}
