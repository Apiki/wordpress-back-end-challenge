<?php
/**
 * Plugin Name: Apiki Favorite Posts
 * Plugin URI:  https://github.com/Apiki/wordpress-back-end-challenge
 * Description: Plugin desenvolvido para o desafio técnico da Apiki. Permite que usuários autenticados disfovem e favoritem posts utilizando a WP REST API, persistindo dados em tabela customizada.
 * Version:     1.0.0
 * Author:      Guilherme Ramos
 * Author URI:  https://github.com/Guilherme-Ramos
 * License:     MIT
 * Text Domain: apiki-favorite-posts
 * Requires PHP: 5.6
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Require Composer Autoloader for PSR-4 autoloading
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Registra a ativação do plugin.
 * Responsável por criar a tabela customizada no banco de dados.
 */
register_activation_hook( __FILE__, function() {
    if ( class_exists( 'Apiki\FavoritePosts\Plugin' ) ) {
        \Apiki\FavoritePosts\Plugin::get_instance()->activate();
    }
} );

/**
 * Registra a desativação do plugin.
 */
register_deactivation_hook( __FILE__, function() {
    if ( class_exists( 'Apiki\FavoritePosts\Plugin' ) ) {
        \Apiki\FavoritePosts\Plugin::get_instance()->deactivate();
    }
} );

/**
 * Inicializa a lógica principal do plugin assim que todos os plugins estiverem carregados.
 */
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'Apiki\FavoritePosts\Plugin' ) ) {
        \Apiki\FavoritePosts\Plugin::get_instance()->run();
    }
} );
