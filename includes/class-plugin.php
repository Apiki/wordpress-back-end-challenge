<?php
/**
 * Classe principal do plugin.
 *
 * Responsável pela inicialização, registro de hooks
 * e bootstrap geral do plugin.
 *
 * @package WordPressBackEndChallenge
 */

// Impede acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe WPBEC_Plugin.
 *
 * Orquestra a inicialização do plugin, instanciando
 * as classes de banco de dados e API.
 */
class WPBEC_Plugin {

	/**
	 * Instância da classe de banco de dados.
	 *
	 * @var WPBEC_Database
	 */
	private $database;

	/**
	 * Instância da classe de API.
	 *
	 * @var WPBEC_Api
	 */
	private $api;

	/**
	 * Construtor – inicializa as dependências do plugin.
	 */
	public function __construct() {
		$this->database = new WPBEC_Database();
		$this->api      = new WPBEC_Api( $this->database );
	}

	/**
	 * Registra os hooks necessários para o funcionamento do plugin.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this->api, 'register_routes' ) );
	}
}
