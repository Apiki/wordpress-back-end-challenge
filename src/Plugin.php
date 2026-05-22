<?php

namespace Apiki\FavoritePosts;

use Apiki\FavoritePosts\Database\Migrator;
use Apiki\FavoritePosts\Rest\Controller;

/**
 * Class Plugin
 *
 * Classe bootstrap principal do plugin Apiki Favorite Posts.
 * Implementa o padrão Singleton para gerenciar hooks globais e lifecycles.
 *
 * @package Apiki\FavoritePosts
 */
class Plugin
{
    /**
     * Instância única da classe.
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Construtor privado para evitar instanciação direta.
     */
    private function __construct()
    {
    }

    /**
     * Retorna a instância única do plugin.
     *
     * @return Plugin
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Executa a inicialização dos hooks do plugin.
     */
    public function run()
    {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    /**
     * Lógica executada na ativação do plugin.
     * Responsável por criar ou atualizar a tabela customizada de banco de dados.
     */
    public function activate()
    {
        $migrator = new Migrator();
        $migrator->up();
    }

    /**
     * Lógica executada na desativação do plugin.
     */
    public function deactivate()
    {
        // Ações de desativação (como limpar caches temporários) podem ser adicionadas aqui.
        // Como boa prática, não deletamos a tabela de dados na desativação simples para não
        // causar perda acidental de dados do usuário (somente no uninstall.php, se requisitado).
    }

    /**
     * Registra as rotas da WP REST API associadas ao plugin.
     */
    public function register_rest_routes()
    {
        $controller = new Controller();
        $controller->register_routes();
    }
}
