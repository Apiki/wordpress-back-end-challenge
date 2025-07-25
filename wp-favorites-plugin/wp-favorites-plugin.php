<?php
/**
 * Plugin Name: WP Favorites Plugin
 * Description: A WordPress plugin that allows logged-in users to favorite and unfavorite posts using WP REST API
 * Version: 1.0.0
 * Author: WordPress Back-end Challenge
 * License: GPL v2 or later
 * Text Domain: wp-favorites-plugin
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_FAVORITES_PLUGIN_VERSION', '1.0.0');
define('WP_FAVORITES_PLUGIN_FILE', __FILE__);
define('WP_FAVORITES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_FAVORITES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WP_Favorites_Plugin\\';
    $base_dir = WP_FAVORITES_PLUGIN_PATH . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . str_replace('\\', '/', strtolower($relative_class)) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function wp_favorites_plugin_init() {
    require_once WP_FAVORITES_PLUGIN_PATH . 'includes/class-wp-favorites-plugin.php';
    WP_Favorites_Plugin::get_instance();
}

// Hook into WordPress
add_action('plugins_loaded', 'wp_favorites_plugin_init'); 