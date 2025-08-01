<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Plugin para favoritar posts via REST API.
 */
defined('ABSPATH') or die('Direct access not allowed');

require_once __DIR__ . '/includes/class-database.php';
require_once __DIR__ . '/includes/class-rest-api.php';

class Apiki_Favorites_Plugin {
    public function __construct() {
        new Apiki_Favorites_Database();
        new Apiki_Favorites_REST_API();
    }
}
new Apiki_Favorites_Plugin();