<?php
/**
 * Apply Hooks WordPress Back-end Challenge.
 *
 * PHP version 7.4
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/wordpress-back-end-challenge
 */

namespace App\Hooks;

/**
 * Hooks Class.
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/back-end-challenge/tree/luis-paiva
 */
class Hooks {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'rest_pre_dispatch', array( $this, 'rest_pre_dispatch' ), 10, 3 );
	}

	/**
	 * Filter rest_pre_dispatch.
	 *
	 * @param mixed            $result  Anything.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 */
	public function rest_pre_dispatch( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {

		list($token) = sscanf( $request->get_header( 'authorization' ), 'Bearer %s' );

		if ( ! empty( $token ) ) {
			$auth = \App\Controllers\JWT::validate( $token );

			if ( is_wp_error( $auth ) ) {
				return new \WP_Error(
					$auth->get_error_code(),
					$auth->get_error_message(),
					array( 'status' => 401 )
				);
			}

			wp_set_current_user( $auth->data->user->id );
		}
	}
}
