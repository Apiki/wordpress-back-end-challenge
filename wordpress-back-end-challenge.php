<?php
/**
 * Plugin Name:     WordPress Back-end Challenge
 * Plugin URI:      https://github.com/luispaiva/wordpress-back-end-challenge
 * Description:     Desafio para os futuros programadores back-end em WordPress da Apiki.
 * Author:          Luis Paiva
 * Author URI:      https://github.com/luispaiva
 * Text Domain:     wp-back-end-challenge
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         WP_Backend_Challenge
 */

defined( 'ABSPATH' ) || exit();

if ( ! defined( 'PLUGIN_DIRECTORY_PATH' ) ) {
	define( 'PLUGIN_DIRECTORY_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( file_exists( PLUGIN_DIRECTORY_PATH . '/vendor/autoload.php' ) ) { // phpcs:ignore
	require PLUGIN_DIRECTORY_PATH . '/vendor/autoload.php';
}

if ( class_exists( 'App\\Init' ) ) {
	add_action( 'plugins_loaded', array( '\App\Init', 'init' ) );
}

if ( class_exists( 'App\\Setup\\Config' ) ) {
	register_activation_hook( __FILE__, array( \App\Setup\Config::class, 'activation' ) );
	register_deactivation_hook( __FILE__, array( \App\Setup\Config::class, 'deactivation' ) );
	register_uninstall_hook( __FILE__, array( \App\Setup\Config::class, 'uninstall' ) );
}
