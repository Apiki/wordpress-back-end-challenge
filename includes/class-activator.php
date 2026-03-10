<?php
/**
 * Class to handle plugin activation and database table creation.
 *
 * @package Apiki_Favorites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apiki Favorites Activator Class.
 */
class Apiki_Favorites_Activator {

	/**
	 * Run activation tasks.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_table();
	}

	/**
	 * Create the favorites table in the database.
	 *
	 * @return void
	 */
	private static function create_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'apiki_favorites';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			post_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY unique_user_post (user_id, post_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
