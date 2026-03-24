<?php
/**
 * Plugin Name: WordPress Back-end Challenge
 * Description: Desafio para os futuros programadores back-end em WordPress da Apiki.
 * Version: 1.0.0
 * Author: João Paulin
 * Text Domain: wordpress-back-end-challenge
 *
 * @package WordPressBackEndChallenge
 */

// Impede acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Carrega as classes do plugin.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';

/**
 * Executa a criação da tabela ao ativar o plugin.
 */
function wpbec_activate() {
	$database = new WPBEC_Database();
	$database->create_table();
}
register_activation_hook( __FILE__, 'wpbec_activate' );

/**
 * Inicializa o plugin.
 */
function wpbec_init() {
	$plugin = new WPBEC_Plugin();
	$plugin->run();
}
add_action( 'plugins_loaded', 'wpbec_init' );