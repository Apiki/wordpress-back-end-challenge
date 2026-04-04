<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Favorite and unfavorite posts for authenticated users via the WordPress REST API.
 * Version: 1.0.0
 * Requires PHP: 5.6
 * Author: Apiki Challenge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'APIKI_FAVORITES_VERSION', '1.0.0' );
define( 'APIKI_FAVORITES_PLUGIN_FILE', __FILE__ );
define( 'APIKI_FAVORITES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once APIKI_FAVORITES_PLUGIN_PATH . 'includes/class-activator.php';
require_once APIKI_FAVORITES_PLUGIN_PATH . 'includes/class-favorites-repository.php';
require_once APIKI_FAVORITES_PLUGIN_PATH . 'includes/class-favorites-rest-controller.php';
require_once APIKI_FAVORITES_PLUGIN_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Apiki_Favorites_Activator', 'activate' ) );

function apiki_favorites_run_plugin() {
	$plugin = new Apiki_Favorites_Plugin();
	$plugin->run();
}

apiki_favorites_run_plugin();
