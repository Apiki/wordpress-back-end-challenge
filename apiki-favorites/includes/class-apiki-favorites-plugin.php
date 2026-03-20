<?php
if (!defined('ABSPATH')) {
    exit;
}

class Apiki_Favorites_Plugin
{

    /**
     * @var Apiki_Favorites_Repository
     */
    private $repository;

    /**
     * @var Apiki_Favorites_REST_Controller
     */
    private $rest_controller;

    /**
     * @var Apiki_Favorites_Shortcode
     */
    private $shortcode;

    public function __construct()
    {
        $this->repository = new Apiki_Favorites_Repository();
        $this->rest_controller = new Apiki_Favorites_REST_Controller($this->repository);
        $this->shortcode = new Apiki_Favorites_Shortcode($this->repository);
    }

    public function initialize(): void
    {
        add_action('rest_api_init', array($this->rest_controller, 'register_routes'));
        add_action('wp_enqueue_scripts', array($this->shortcode, 'registerAssets'));
        add_shortcode('apiki_favorite_button', array($this->shortcode, 'showButton'));
        add_shortcode('apiki_favorite_list', array($this->shortcode, 'showFavoriteList'));
    }
}