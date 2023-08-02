<?php
/**
 * Nome do Plugin: WP Favourites
 * Descrição: Permite que os usuários favoritem posts usando a API REST do WordPress.
 * Versão: 1.0.0
 * Autor: Itamar Silva
 *
 * @package WP_Favourites
 */

// Impede o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Incluir o arquivo favourites-controller.php para registrar as rotas da API
require_once plugin_dir_path( __FILE__ ) . 'favourites-controller.php';

/**
 * Classe principal do Plugin WP Favourites.
 *
 * Esta classe inicializa o plugin e trata as ações de ativação,
 * desativação e desinstalação do plugin. Além disso, cria uma tabela
 * personalizada no banco de dados para armazenar dados de posts favoritos
 * e registra as rotas da API usando a classe FavouritesController.
 *
 * @since 1.0.0
 */
class WP_Favourites {

    /**
     * Construtor da classe WP_Favourites.
     *
     * Inicializa o plugin e registra os hooks necessários do WordPress.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Criar uma instância da classe FavouritesController para gerenciar as rotas da API
        $favourites_controller = new FavouritesController();
    
        // Registrar os hooks necessários do WordPress para a API REST do plugin
        add_action( 'rest_api_init', array( $favourites_controller, 'register_routes' ) );
    }

    /**
     * Método para ativar o plugin.
     *
     * Este método é chamado quando o plugin é ativado e cria
     * uma tabela personalizada no banco de dados para armazenar dados
     * de posts favoritos.
     *
     * @since 1.0.0
     */
    public static function activate() {
        self::create_custom_table();
    }

    /**
     * Método para desativar o plugin.
     *
     * Este método é chamado quando o plugin é desativado.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Adicionar código de desativação aqui, se necessário
    }

    /**
     * Método para desinstalar o plugin.
     *
     * Este método é chamado quando o plugin é desinstalado e
     * remove a tabela personalizada do banco de dados.
     *
     * @since 1.0.0
     */
    public static function uninstall() {
        self::remove_custom_table();
    }

    /**
     * Método para criar uma tabela personalizada no banco de dados.
     *
     * Este método é usado para criar uma tabela personalizada para
     * armazenar dados de posts favoritos, como o ID do usuário e o ID do post.
     *
     * @since 1.0.0
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
     * Método para remover a tabela personalizada do banco de dados.
     *
     * Este método é usado para remover a tabela personalizada quando o plugin é desinstalado.
     *
     * @since 1.0.0
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
     * Função customizada para executar uma consulta SQL usando dbDelta.
     *
     * Esta função é usada para executar a consulta SQL para criar a tabela personalizada.
     *
     * @param string $sql A consulta SQL para executar.
     *
     * @since 1.0.0
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