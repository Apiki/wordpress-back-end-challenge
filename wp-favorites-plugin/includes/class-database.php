<?php
/**
 * Responsável pela criação e atualização da tabela customizada.
 *
 * @package WPFavoritesPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPFAV_Database {

    const DB_VERSION_OPTION = 'wpfav_db_version';

    /**
     * Cria (ou atualiza) a tabela de favoritos usando dbDelta().
     * Chamada no hook de ativação do plugin.
     *
     * @return void
     */
    public static function create_table(): void {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'favorites';
        $charset_collate = $wpdb->get_charset_collate();

        /*
         * IMPORTANTE: dbDelta() é sensível ao formato do SQL.
         * Referência: https://developer.wordpress.org/plugins/creating-tables-with-plugins/
         */
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY idx_user_id (user_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( self::DB_VERSION_OPTION, WPFAV_VERSION );
    }

    /**
     * Verifica se a versão instalada da tabela está desatualizada
     * e executa create_table() caso necessário.
     * Chamada no hook plugins_loaded para suportar atualizações.
     *
     * @return void
     */
    public static function maybe_update(): void {
        $installed_version = get_option( self::DB_VERSION_OPTION, '0.0.0' );

        if ( version_compare( $installed_version, WPFAV_VERSION, '<' ) ) {
            self::create_table();
        }
    }
}
