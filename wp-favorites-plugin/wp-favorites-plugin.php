<?php
/**
 * Plugin Name: WP Favorites Plugin
 * Description: Favoritar posts via REST API
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

define('WPFAV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WPFAV_VERSION', '1.0.0');

require_once WPFAV_PLUGIN_PATH . 'includes/class-database.php';
require_once WPFAV_PLUGIN_PATH . 'includes/class-plugin.php';

register_activation_hook(__FILE__, ['WPFAV_Database', 'create_table']);

function wpfav_init() {
    $plugin = new WPFAV_Plugin();
    $plugin->run();
}

wpfav_init();