<?php
/**
 * Plugin Name: Apiki Favorites
 * Plugin URI: https://apiki.com/
 * Description: Plugin para favoritar e desfavoritar posts via WP REST API para usuários logados.
 * Version: 1.0.0
 * Author: Wilton Gomes
 * Author URI: https://apiki.com/
 * Text Domain: apiki-favorites
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('APIKI_FAVORITES_VERSION', '1.0.0');
define('APIKI_FAVORITES_FILE', __FILE__);
define('APIKI_FAVORITES_PATH', plugin_dir_path(__FILE__));
define('APIKI_FAVORITES_URL', plugin_dir_url(__FILE__));

require_once APIKI_FAVORITES_PATH . 'includes/class-apiki-favorites-installer.php';
require_once APIKI_FAVORITES_PATH . 'includes/class-apiki-favorites-repository.php';
require_once APIKI_FAVORITES_PATH . 'includes/class-apiki-favorites-rest-controller.php';
require_once APIKI_FAVORITES_PATH . 'includes/class-apiki-favorites-shortcode.php';
require_once APIKI_FAVORITES_PATH . 'includes/class-apiki-favorites-plugin.php';

register_activation_hook(APIKI_FAVORITES_FILE, array('Apiki_Favorites_Installer', 'activate'));

function apiki_favorites_run(): void
{
    $plugin = new Apiki_Favorites_Plugin();
    $plugin->initialize();
}

apiki_favorites_run();