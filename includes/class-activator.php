<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apiki_Favorites_Activator {

	public static function activate() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'apiki_favorites';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			post_id bigint(20) unsigned NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_post (user_id, post_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );
	}
}
