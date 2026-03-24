<?php
/**
 * Classe responsável pelo registro e manipulação das rotas REST.
 *
 * Registra os endpoints da API de favoritos e processa
 * as requisições de toggle e listagem.
 *
 * @package WordPressBackEndChallenge
 */

// Impede acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe WPBEC_Api.
 *
 * Responsável por registrar as rotas REST e manipular
 * as requisições e respostas da API de favoritos.
 */
class WPBEC_Api {

	/**
	 * Namespace da REST API.
	 *
	 * @var string
	 */
	private $namespace = 'favorites/v1';

	/**
	 * Instância da classe de banco de dados.
	 *
	 * @var WPBEC_Database
	 */
	private $database;

	/**
	 * Construtor – recebe a instância do banco de dados.
	 *
	 * @param WPBEC_Database $database Instância da classe de banco de dados.
	 */
	public function __construct( WPBEC_Database $database ) {
		$this->database = $database;
	}

	/**
	 * Registra as rotas da REST API.
	 *
	 * @return void
	 */
	public function register_routes() {
		// Endpoint para alternar favorito (favorite/unfavorite).
		register_rest_route(
			$this->namespace,
			'/toggle',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'toggle_favorite' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'post_id' => array(
						'required'          => true,
						'validate_callback' => array( $this, 'validate_post_id' ),
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Endpoint para listar os posts favoritados.
		register_rest_route(
			$this->namespace,
			'/list',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list_favorites' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Verifica se o usuário está autenticado.
	 *
	 * @return bool True se o usuário estiver logado; false caso contrário.
	 */
	public function check_permission() {
		return is_user_logged_in();
	}

	/**
	 * Valida o parâmetro post_id.
	 *
	 * Verifica se é um número inteiro positivo e se o post existe.
	 *
	 * @param mixed           $value   Valor do parâmetro.
	 * @param WP_REST_Request $request Requisição REST.
	 * @param string          $param   Nome do parâmetro.
	 * @return bool|WP_Error True se válido; WP_Error caso contrário.
	 */
	public function validate_post_id( $value, $request, $param ) {
		$post_id = absint( $value );

		if ( $post_id <= 0 ) {
			return new WP_Error(
				'rest_invalid_param',
				'O parâmetro post_id deve ser um número inteiro positivo.',
				array( 'status' => 400 )
			);
		}

		// Verifica se o post existe.
		$post = get_post( $post_id );
		if ( null === $post ) {
			return new WP_Error(
				'rest_post_not_found',
				'O post informado não foi encontrado.',
				array( 'status' => 404 )
			);
		}

		return true;
	}

	/**
	 * Alterna o status de favorito de um post (toggle).
	 *
	 * Se o post já estiver favoritado, remove. Caso contrário, adiciona.
	 *
	 * @param WP_REST_Request $request Requisição REST.
	 * @return WP_REST_Response Resposta com o status atualizado.
	 */
	public function toggle_favorite( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$post_id = $request->get_param( 'post_id' );

		$is_favorited = $this->database->is_favorited( $user_id, $post_id );

		if ( $is_favorited ) {
			// Remove dos favoritos.
			$this->database->remove_favorite( $user_id, $post_id );
			$favorited = false;
		} else {
			// Adiciona aos favoritos.
			$this->database->add_favorite( $user_id, $post_id );
			$favorited = true;
		}

		// Invalida o cache após a alteração.
		$this->database->clear_cache( $user_id );

		return new WP_REST_Response(
			array(
				'favorited' => $favorited,
			),
			200
		);
	}

	/**
	 * Retorna a lista de posts favoritados pelo usuário logado.
	 *
	 * @param WP_REST_Request $request Requisição REST.
	 * @return WP_REST_Response Resposta com a lista de IDs favoritados.
	 */
	public function list_favorites( WP_REST_Request $request ) {
		$user_id   = get_current_user_id();
		$favorites = $this->database->get_favorites( $user_id );

		return new WP_REST_Response(
			array(
				'favorites' => $favorites,
			),
			200
		);
	}
}
