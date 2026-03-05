<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Post favorites system via WP REST API for logged-in users.
 * Version: 1.0.0
 * Author: ANDRE AND COPILOT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'APIKI_FAV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'APIKI_FAV_TABLE_NAME', 'apiki_favorites' );
define( 'APIKI_FAV_REST_NAMESPACE', 'apiki/v1' );

require_once APIKI_FAV_PLUGIN_PATH . 'includes/class-activator.php';
require_once APIKI_FAV_PLUGIN_PATH . 'includes/class-favorites-service.php';
require_once APIKI_FAV_PLUGIN_PATH . 'includes/class-rest-controller.php';

register_activation_hook( __FILE__, [ 'Apiki_Favorites_Activator', 'activate' ] );

add_action( 'rest_api_init', function () {
    $service     = new Apiki_Favorites_Service();
    $controller  = new Apiki_Favorites_REST_Controller( $service );
    $controller->register_routes();
} );