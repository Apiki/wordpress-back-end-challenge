<?php

function create_custom_table(){
	global $wpdb;
	
	$nome_tabela = $wpdb->prefix . 'liked'; // Prefixo do WordPress + nome da tabela
	
	$sql = "CREATE TABLE IF NOT EXISTS $nome_tabela (
    id INT NOT NULL AUTO_INCREMENT,
    id_post INT NOT NULL,
    id_user INT NOT NULL,
    PRIMARY KEY (id)
	)";

	$wpdb->query($sql);
}

register_activation_hook( __FILE__, 'create_custom_table' );