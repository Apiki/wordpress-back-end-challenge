<?php
/**
 * Classe responsável pelos endpoints da REST API.
 *
 * @package Apiki_Favorites
 */

defined( 'ABSPATH' ) || exit;

class Apiki_Favorites_Rest_Api {

	const NAMESPACE = 'apiki/v1';

	/**
	 * Registra as rotas da API.
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/favorites',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'add_favorite' ),
					'permission_callback' => array( __CLASS__, 'check_permission' ),
					'args'                => array(
						'post_id' => array(
							'required'          => true,
							'validate_callback' => array( __CLASS__, 'validate_post_id' ),
							'sanitize_callback' => 'absint',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_favorites' ),
					'permission_callback' => array( __CLASS__, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/favorites/(?P<post_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'remove_favorite' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'post_id' => array(
						'validate_callback' => array( __CLASS__, 'validate_post_id' ),
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Verifica se o usuário está logado.
	 *
	 * @return bool|WP_Error
	 */
	public static function check_permission() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				'Você precisa estar logado para usar esta funcionalidade.',
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Valida o post_id.
	 *
	 * @param mixed $value Valor do parâmetro.
	 * @return bool
	 */
	public static function validate_post_id( $value ) {
		$post_id = absint( $value );

		if ( $post_id <= 0 ) {
			return false;
		}

		return get_post_status( $post_id ) !== false;
	}

	/**
	 * Favoritar um post.
	 *
	 * @param WP_REST_Request $request Objeto da requisição.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function add_favorite( $request ) {
		$user_id = get_current_user_id();
		$post_id = $request->get_param( 'post_id' );

		$result = Apiki_Favorites_Database::add_favorite( $user_id, $post_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Post adicionado aos favoritos.',
				'post_id' => $post_id,
			),
			201
		);
	}

	/**
	 * Desfavoritar um post.
	 *
	 * @param WP_REST_Request $request Objeto da requisição.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function remove_favorite( $request ) {
		$user_id = get_current_user_id();
		$post_id = $request->get_param( 'post_id' );

		$result = Apiki_Favorites_Database::remove_favorite( $user_id, $post_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Post removido dos favoritos.',
				'post_id' => $post_id,
			),
			200
		);
	}

	/**
	 * Listar favoritos do usuário.
	 *
	 * @param WP_REST_Request $request Objeto da requisição.
	 * @return WP_REST_Response
	 */
	public static function get_favorites( $request ) {
		$user_id   = get_current_user_id();
		$favorites = Apiki_Favorites_Database::get_user_favorites( $user_id );

		$data = array();

		foreach ( $favorites as $favorite ) {
			$post = get_post( $favorite->post_id );

			if ( ! $post ) {
				continue;
			}

			$data[] = array(
				'post_id'    => absint( $favorite->post_id ),
				'title'      => $post->post_title,
				'created_at' => $favorite->created_at,
			);
		}

		return new WP_REST_Response( $data, 200 );
	}
}
