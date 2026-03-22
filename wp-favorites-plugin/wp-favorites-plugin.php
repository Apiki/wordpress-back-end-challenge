<?php
/**
 * Plugin Name: WP Favorites Plugin
 * Description: Favoritar posts via REST API para usuários logados.
 * Version:     1.1.0
 * Author:      Pedro Henrique Mascimo
 * Text Domain: wp-favorites-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WPFAV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPFAV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPFAV_VERSION', '1.1.0' );

require_once WPFAV_PLUGIN_PATH . 'includes/class-database.php';
require_once WPFAV_PLUGIN_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, [ 'WPFAV_Database', 'create_table' ] );

/**
 * Inicializa o plugin.
 */
function wpfav_init(): void {
    $plugin = new WPFAV_Plugin();
    $plugin->run();
}

wpfav_init();
