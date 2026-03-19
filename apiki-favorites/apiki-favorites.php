<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Permite usuários logados favoritar e desfavoritar posts via REST API.
 * Version: 1.0.0
 * Author: Mainart Moretto
 * Text Domain: apiki-favorites
 *
 * @package Apiki_Favorites
 */

defined( 'ABSPATH' ) || exit;

define( 'APIKI_FAVORITES_VERSION', '1.0.0' );
define( 'APIKI_FAVORITES_PATH', plugin_dir_path( __FILE__ ) );

require_once APIKI_FAVORITES_PATH . 'includes/class-database.php';
require_once APIKI_FAVORITES_PATH . 'includes/class-rest-api.php';

register_activation_hook( __FILE__, array( 'Apiki_Favorites_Database', 'create_table' ) );

add_action( 'rest_api_init', array( 'Apiki_Favorites_Rest_Api', 'register_routes' ) );
