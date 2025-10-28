<?php

if (!defined('ABSPATH')) {
    exit;
}

class Favoritar_Posts_Plugin
{
    private static $instance;
    private $repository;
    private $rest_controller;

    private function __construct()
    {
        global $wpdb;
        $this->repository = new Favoritos_Repository($wpdb);
        $this->rest_controller = new Favoritos_REST_Controller($this->repository);

        add_action('rest_api_init', [$this->rest_controller, 'register_routes']);
    }

    public static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
    }

    public static function activate()
    {
        global $wpdb;
        $repository = new Favoritos_Repository($wpdb);
        $repository->create_table();
    }

    public static function uninstall()
    {
        global $wpdb;
        $repository = new Favoritos_Repository($wpdb);
        $repository->drop_table();
    }
}
