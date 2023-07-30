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
        // Registrar os hooks necessários do WordPress aqui
        add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );
    }

    /**
     * Método de ativação do Plugin
     */
    public static function activate() {
        self::create_custom_table();
        $favourites_controller = new FavouritesController();
        $favourites_controller->register_routes(); // Registrar as rotas da API do plugin
    }

    /**
     * Método de desativação do Plugin
     */
    public static function deactivate() {
        // Adicionar código de desativação aqui, se necessário
    }

    /**
     * Método de desinstalação do Plugin
     */
    public static function uninstall() {
        self::remove_custom_table();
    }

    /**
     * Método para criar a tabela personalizada no banco de dados
     */
    private static function create_custom_table() {
        global $wpdb;

        // Nome da tabela personalizada
        $table_name = $wpdb->prefix . 'wp_favourites';

        // SQL para criar a tabela
        $charset_collate = $wpdb->get_charset_collate();

        // Montar a consulta SQL para criar a tabela
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            user_id BIGINT NOT NULL,
            post_id BIGINT NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_favourite (user_id, post_id)
        ) $charset_collate;";

        // Executar a consulta SQL usando a função dbDelta customizada
        self::custom_dbDelta( $sql );
    }

    /**
     * Método para remover a tabela personalizada do banco de dados
     */
    public static function remove_custom_table() {
        global $wpdb;

        // Nome da tabela personalizada
        $table_name = $wpdb->prefix . 'wp_favourites';

        // SQL para remover a tabela
        $sql = "DROP TABLE IF EXISTS $table_name;";

        // Executar a consulta SQL usando $wpdb->query()
        $wpdb->query( $sql );
    }

    /**
     * Função customizada para executar a consulta SQL usando dbDelta
     */
    private static function custom_dbDelta( $sql ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}

// Instanciar a classe principal do Plugin
$wp_favourites_plugin = new WP_Favourites();

// Registrar os hooks de ativação, desativação e desinstalação do Plugin
register_activation_hook( __FILE__, array( 'WP_Favourites', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Favourites', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'WP_Favourites', 'uninstall' ) );