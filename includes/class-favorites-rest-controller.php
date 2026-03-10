<?php
/**
 * Class to handle REST API endpoints for favorites.
 *
 * @package Apiki_Favorites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apiki Favorites REST Controller Class.
 */
class Apiki_Favorites_REST_Controller {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	private $namespace = 'apiki/v1';

	/**
	 * REST API base.
	 *
	 * @var string
	 */
	private $rest_base = 'favorites';

	/**
	 * Favorites repository instance.
	 *
	 * @var Apiki_Favorites_Repository
	 */
	private $repository;

	/**
	 * Constructor.
	 *
	 * @param Apiki_Favorites_Repository $repository Repository instance.
	 */
	public function __construct( $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<post_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_favorite' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'post_id' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_post_id' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_favorite' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'post_id' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_post_id' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Check if user is authenticated.
	 *
	 * @return bool True if user is logged in, false otherwise.
	 */
	public function check_permission() {
		return is_user_logged_in();
	}

	/**
	 * Validate post ID exists.
	 *
	 * @param mixed $param Parameter value.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_post_id( $param ) {
		$post_id = absint( $param );
		return $post_id > 0 && get_post( $post_id ) !== null;
	}

	/**
	 * Add a favorite to a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function add_favorite( $request ) {
		$post_id = absint( $request->get_param( 'post_id' ) );
		$user_id = get_current_user_id();

		// Validate post exists.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'apiki_favorites_post_not_found',
				__( 'Post not found.', 'apiki-favorites' ),
				array( 'status' => 404 )
			);
		}

		// Check if already favorited.
		if ( $this->repository->is_favorited( $user_id, $post_id ) ) {
			return new WP_Error(
				'apiki_favorites_already_exists',
				__( 'This post is already favorited.', 'apiki-favorites' ),
				array( 'status' => 422 )
			);
		}

		// Add favorite.
		$result = $this->repository->add_favorite( $user_id, $post_id );

		if ( ! $result ) {
			return new WP_Error(
				'apiki_favorites_add_failed',
				__( 'Failed to add favorite.', 'apiki-favorites' ),
				array( 'status' => 500 )
			);
		}

		$response = array(
			'message' => __( 'Post favorited successfully.', 'apiki-favorites' ),
			'data'    => array(
				'post_id' => $post_id,
				'user_id' => $user_id,
			),
		);

		return new WP_REST_Response( $response, 201 );
	}

	/**
	 * Remove a favorite from a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function remove_favorite( $request ) {
		$post_id = absint( $request->get_param( 'post_id' ) );
		$user_id = get_current_user_id();

		// Validate post exists.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'apiki_favorites_post_not_found',
				__( 'Post not found.', 'apiki-favorites' ),
				array( 'status' => 404 )
			);
		}

		// Check if favorite exists.
		if ( ! $this->repository->is_favorited( $user_id, $post_id ) ) {
			return new WP_Error(
				'apiki_favorites_not_found',
				__( 'This post is not favorited.', 'apiki-favorites' ),
				array( 'status' => 404 )
			);
		}

		// Remove favorite.
		$result = $this->repository->remove_favorite( $user_id, $post_id );

		if ( ! $result ) {
			return new WP_Error(
				'apiki_favorites_remove_failed',
				__( 'Failed to remove favorite.', 'apiki-favorites' ),
				array( 'status' => 500 )
			);
		}

		$response = array(
			'message' => __( 'Post unfavorited successfully.', 'apiki-favorites' ),
			'data'    => array(
				'post_id' => $post_id,
				'user_id' => $user_id,
			),
		);

		return new WP_REST_Response( $response, 200 );
	}
}
