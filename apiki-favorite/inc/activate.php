<?php

function af_activate()
{
	// Impede de funcionar em versões < que 3.5
	if (version_compare(get_bloginfo('version'), '3.5', '<')) {
		wp_die('Você precisa atualizar o wordpress para usar este plugin.');
	}

	create_plugin_table();
}

function create_plugin_table()
{
	global $wpdb;

	$sql = "CREATE TABLE " . $wpdb->prefix . "apiki_favorites (
		ID BIGINT(20) NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) NOT NULL,
		post_id BIGINT(20) NOT NULL,
		PRIMARY KEY (ID),
		KEY (user_id),
		KEY (post_id)
	) " . $wpdb->get_charset_collate();

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}