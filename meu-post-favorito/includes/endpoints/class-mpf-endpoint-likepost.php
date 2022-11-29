<?php
/**
 * MPF plugin file.
 *
 * @package MPF\Admin\LikePost
 */

/**
 * Representa uma implementação da interface MPF_Endpoint_Interface  para registrar um endpoint.
 */
class MPF_Endpoint_LikePost implements MPF_Endpoint_Interface  {

	/**
	 * O namespace da rota REST.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'mpf/v1';

	/**
	 * A rota do endpoint do MPF.
	 *
	 * @var string
	 */
	const LIKEPOST = 'likepost'; //post

	/**
	 * Service usado.
	 *
	 * @var MPF_LikePost_Service
	 */
	protected $service;

	/**
	 * Constrói a classe MPF_Endpoint_LikePost e define o service a ser usado.
	 *
	 * @param MPF_LikePost_Service $service Service usado.
	 */
	public function __construct( MPF_LikePost_Service $service ) {
		$this->service = $service;
	}

	/**
	 * Registra as rotas que estão disponíveis no endpoint.
	 */
	public function register() {

		$route_args = [
			'methods'             => 'POST',
			'callback'            => [ $this->service, 'like_post' ],
		];
		register_rest_route( self::REST_NAMESPACE, self::LIKEPOST, $route_args );	
	}
}
