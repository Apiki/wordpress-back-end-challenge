<?php
// Função para criar a tabela de Favoritar/Desfavoritar no banco de dados
global $apikiwp_version;
$apikiwp_version = '1.0';

function apikiwp_install() {
	global $wpdb;
	global $apikiwp_version;

	$table_name = $wpdb->prefix . "des_Favoritar";
	
	$charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        post_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL
      ) $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
      
	add_option( 'apikiwp_version', $apikiwp_version );
}

register_activation_hook( __FILE__, 'apikiwp_install' );