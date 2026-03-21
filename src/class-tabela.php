<?php
/**
 * Classe que cria a tabela de favoritos no banco de dados.
 *
 * @package ApikiFavoritos
 */

declare(strict_types=1);

/**
 * Tabela de favoritos.
 */
class Tabela
{
    /**
     * Nome da tabela no banco.
     *
     * @var string
     */
    const NOME = 'apiki_favoritos';

    /**
     * Cria a tabela de favoritos no banco de dados.
     *
     * @return void
     */
    public static function criar()
    {
        global $wpdb;

        $tabela  = $wpdb->prefix . self::NOME;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$tabela} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            usuario_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY usuario_post (usuario_id, post_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
