<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'apiki_favorites';

// Cuidado: em produção, às vezes não se remove dados.
// Mas para o desafio, faz sentido limpar.
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );