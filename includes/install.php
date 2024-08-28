<?php

function create_custom_table() {
    global $wpdb;
    
    $nome_tabela = $wpdb->prefix . 'liked';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $nome_tabela (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        id_post BIGINT(20) NOT NULL,
        id_user BIGINT(20) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_post (id_user, id_post)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_custom_table');
