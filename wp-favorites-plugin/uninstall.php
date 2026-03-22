<?php
/**
 * Executado quando o plugin é desinstalado via painel do WordPress.
 * Remove a tabela customizada e as opções criadas pelo plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table = $wpdb->prefix . 'favorites';

$wpdb->query( 'DROP TABLE IF EXISTS `' . esc_sql( $table ) . '`' ); 

delete_option( 'wpfav_db_version' );
