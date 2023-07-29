<?php
/**
 * Plugin Name: WP Favourites
 * Description: Allows users to favourite posts using the WP REST API.
 * Version: 1.0.0
 * Author: Itamar Silva
 */

// Impede o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe principal do Plugin WP Favourites
 */
class WP_Favourites {

    /**
     * Construtor da classe
     */
    public function __construct() {
        // Adicionar os hooks necessários do WordPress aqui
    }

    /**
     * Método de ativação do Plugin
     */
    public static function activate() {
        // Adicionar código de ativação aqui, como criar a tabela personalizada no banco de dados
    }

    /**
     * Método de desativação do Plugin
     */
    public static function deactivate() {
        // Adicionar código de desativação aqui, como remover a tabela personalizada do banco de dados
    }
}

// Instanciar a classe principal do Plugin
$wp_favourites_plugin = new WP_Favourites();

// Registrar os hooks de ativação e desativação do Plugin
register_activation_hook( __FILE__, array( 'WP_Favourites', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Favourites', 'deactivate' ) );
