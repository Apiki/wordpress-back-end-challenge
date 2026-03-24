<?php
/**
 * Arquivo de desinstalação do plugin.
 *
 * Executado automaticamente pelo WordPress quando o plugin
 * é removido. Remove a tabela de favoritos do banco de dados.
 *
 * @package WordPressBackEndChallenge
 */

// Impede execução fora do contexto de desinstalação do WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

// Remove a tabela de favoritos do banco de dados.
$database = new WPBEC_Database();
$database->drop_table();
