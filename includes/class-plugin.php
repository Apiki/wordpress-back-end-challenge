<?php
/**
 * Main plugin class.
 *
 * @package Apiki_Favorites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apiki Favorites Plugin Class.
 */
class Apiki_Favorites_Plugin {

	/**
	 * Repository instance.
	 *
	 * @var Apiki_Favorites_Repository
	 */
	private $repository;

	/**
	 * REST Controller instance.
	 *
	 * @var Apiki_Favorites_REST_Controller
	 */
	private $rest_controller;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->repository       = new Apiki_Favorites_Repository();
		$this->rest_controller  = new Apiki_Favorites_REST_Controller( $this->repository );
	}

	/**
	 * Run the plugin.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this->rest_controller, 'register_routes' ) );
	}
}
