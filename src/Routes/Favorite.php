<?php
/**
 * Favorite Route WordPress Back-end Challenge.
 *
 * PHP version 7.4
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/wordpress-back-end-challenge
 */

namespace App\Routes;

/**
 * Favorite Route Class.
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/back-end-challenge/tree/luis-paiva
 */
class Favorite {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'apiki/challenge',
			'/favorite/(?P<post_id>[\d]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => function ( \WP_REST_Request $request ) {
						return ( new \App\Controllers\Favorite() )->post( $request );
					},
					'permission_callback' => function () {
						if ( ! is_user_logged_in() ) {
							return new \WP_Error(
								'rest_api_unauthorized',
								__( 'Você deve estar logado para favoritar este post.', 'wp-back-end-challenge' ),
								array( 'status' => 401 )
							);
						}
						return true;
					},
				),
			)
		);
	}
}
