<?php
/**
 * Auth Controller WordPress Back-end Challenge.
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
 * Auth Controller Class.
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/wordpress-back-end-challenge/tree/luis-paiva
 */
class Auth {

	/**
	 * Login user.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function login( \WP_REST_Request $request ) {

		if ( empty( $request->get_param( 'username' ) ) || empty( $request->get_param( 'password' ) ) ) {
			return new \WP_Error(
				'required_params',
				esc_html__( 'Usuário e senha são obrigatórios!', 'wp-back-end-challenge' ),
				array( 'status' => 401 )
			);
		}

		$user = wp_authenticate(
			$request->get_param( 'username' ),
			$request->get_param( 'password' )
		);

		if ( is_wp_error( $user ) ) {
			return new \WP_Error(
				'authentication_failed',
				esc_html__( 'Usuário ou senhas inválidos!', 'wp-back-end-challenge' ),
				array( 'status' => 401 )
			);
		}

		$data = \App\Controllers\JWT::generate( $user );

		return new \WP_REST_Response( $data, 200 );
	}
}
