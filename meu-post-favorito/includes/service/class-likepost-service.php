<?php
/**
 * MPF plugin file.
 *
 * @package MPF\Admin\LikePost
 */

/**
 * Class MPF_LikePost_Service.
 */
class MPF_LikePost_Service {

	/**
	 * Classe LikePost
	 *
	 * @var MPF_LikePost
	 */
	protected $likepost;

	/**
	 * MPF_LikePost_Service contructor.
	 *
	 * @param MPF_LikePost $likepost
	 */
	public function __construct( MPF_LikePost $likepost ) {
		$this->LikePost = $likepost;
	}

	/**
	 * ENDPOINT - Favorita uma postagem
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response.
	 */
	public function like_post( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'post_id' );
		$id_user = get_option( 'id_user' );
		
		$data = [
			'post_id'   => $post_id,
			'id_user' => $id_user
		];

		try {
			$_response = $this->LikePost->like_post( $data );
			return new WP_REST_Response( $_response );
		}
		catch ( Exception $exception ) {
			return new WP_REST_Response( $exception->getMessage(), 500 );
		}
	}
}
