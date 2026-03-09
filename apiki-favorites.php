<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Plugin to allow logged-in users to favorite posts using WP REST API.
 * Version: 1.0.0
 * Author: Apiki
 * License: GPL2
 * Text Domain: apiki-favorites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'APIKI_FAVORITES_VERSION', '1.0.0' );
define( 'APIKI_FAVORITES_PATH', plugin_dir_path( __FILE__ ) );
define( 'APIKI_FAVORITES_INCLUDES_PATH', APIKI_FAVORITES_PATH . 'includes/' );

/**
 * Code that runs during plugin activation.
 */
function apiki_favorites_activate() {
	require_once APIKI_FAVORITES_INCLUDES_PATH . 'class-activator.php';
	Apiki_Favorites_Activator::activate();
}

register_activation_hook( __FILE__, 'apiki_favorites_activate' );

/**
 * Load plugin classes.
 */
function apiki_favorites_load() {
	require_once APIKI_FAVORITES_INCLUDES_PATH . 'class-favorites-repository.php';
	require_once APIKI_FAVORITES_INCLUDES_PATH . 'class-favorites-rest-controller.php';
	require_once APIKI_FAVORITES_INCLUDES_PATH . 'class-plugin.php';

	$plugin = new Apiki_Favorites_Plugin();
	$plugin->run();
}

add_action( 'plugins_loaded', 'apiki_favorites_load' );
