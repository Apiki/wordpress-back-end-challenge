<?php

/**
 * Classe responsável por instalar o plugin
 * Cria a tabela wp_post_favorites no banco de dados
 */
class Install {
    public static function run() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'post_favorites';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
