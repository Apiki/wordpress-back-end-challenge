<?php

namespace Apiki\FavoritePosts\Database;

/**
 * Class Migrator
 *
 * Responsável por gerenciar o esquema de tabelas customizadas do plugin no banco de dados do WordPress.
 *
 * @package Apiki\FavoritePosts\Database
 */
class Migrator
{
    /**
     * Nome base da tabela (sem o prefixo do WordPress).
     */
    const TABLE_NAME = 'apiki_favorites';

    /**
     * Cria ou atualiza a tabela customizada de favoritos.
     * Utiliza a função dbDelta() do WordPress para garantir portabilidade e segurança na migração do esquema.
     *
     * @return void
     */
    public function up()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        // Atenção: A função dbDelta exige formatação estrita do SQL:
        // - Deve haver dois espaços entre "PRIMARY KEY" e a abertura de parênteses "(id)".
        // - Cada campo deve estar em sua própria linha.
        // - Todos os comandos SQL devem ser em letras maiúsculas.
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post (user_id,post_id),
            KEY user_idx (user_id),
            KEY post_idx (post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Remove a tabela customizada de favoritos.
     * Normalmente invocada em rotinas de desinstalação completa (uninstall.php).
     *
     * @return void
     */
    public function down()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS $table_name;");
    }
}
