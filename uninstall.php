<?php
// Impede o acesso direto ao arquivo
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Incluir o arquivo com a definição da função custom_dbDelta
require_once plugin_dir_path( __FILE__ ) . 'wp-favourites.php';

// Remover a tabela personalizada
WP_Favourites::remove_custom_table();