<?php
/**
 * @package ApikiFavoritePost
 * @version 0.1.0
 */
/**
 * Plugin Name: Apiki Favorite Post
 * Plugin URI: https://github.com/Apiki/wordpress-back-end-challenge
 * Description: A WordPress Plugin that creates API endpoints to manage favorite posts.
 * Version: 0.1.0
 * Author: Yves Cabral
 * Author URI: https://github.com/yvescabral
 * License: MIT
 */

if ( ! defined('ABSPATH') ) {
    die;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use FavoritePostPlugin\FavoritePostPlugin;

$plugin = FavoritePostPlugin::getInstance();

register_activation_hook( __FILE__, [$plugin, 'activate']);
register_deactivation_hook( __FILE__, [$plugin, 'deactivate'] );
register_uninstall_hook(__FILE__, [FavoritePostPlugin::class, 'uninstall']);

$plugin->register();
