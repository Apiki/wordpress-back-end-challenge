<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apiki_Favorites_Plugin {

	protected $repository;

	protected $rest_controller;

	public function __construct() {
		$this->repository      = new Apiki_Favorites_Repository();
		$this->rest_controller = new Apiki_Favorites_REST_Controller( $this->repository );
	}

	public function run() {
		add_action( 'rest_api_init', array( $this->rest_controller, 'register_routes' ) );
	}
}
