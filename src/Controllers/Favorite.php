<?php
/**
 * Favorite Controller WordPress Back-end Challenge.
 *
 * PHP version 7.4
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/wordpress-back-end-challenge/tree/luis-paiva
 */

namespace App\Controllers;

/**
 * Favorite Controller Class.
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/wordpress-back-end-challenge/tree/luis-paiva
 */
class Favorite {

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->user_id = get_current_user_id();
	}

	/**
	 * Set favorite post.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function post( \WP_REST_Request $request ) {
		$post_id = $request->get_param( 'post_id' );

		if ( null === get_post( $post_id ) ) {
			return new \WP_Error(
				'post_not_found',
				__( 'Post não encontrado.', 'wp-back-end-challenge' ),
				array( 'status' => 404 )
			);
		}

		$favorite = new \App\Models\Favorite();
		$post     = $favorite->read( $post_id, $this->user_id );

		if ( null === $post ) {
			$favorite->create( $post_id, $this->user_id );
			return new \WP_REST_Response(
				array(
					'status'  => 'success',
					'message' => __( 'Post favoritado.', 'wp-back-end-challenge' ),
				),
				200
			);
		}

		$status = ( '0' === $post->liked ) ? 1 : 0;
		$favorite->update( $post->id, $status );

		switch ( $status ) {
			case 0:
				$message = __( 'Remoção de favoritismo.', 'wp-back-end-challenge' );
				break;
			case 1:
				$message = __( 'Post favoritado.', 'wp-back-end-challenge' );
				break;
		}

		return new \WP_REST_Response(
			array(
				'status'  => 'success',
				'message' => $message,
			),
			200
		);
	}
}
