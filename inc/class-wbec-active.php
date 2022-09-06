<?php
class WBEC_Active {

	public static function init() {
		self::installDB();
	}

	private static function installDB() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'wbec_fav';

		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  post_id int(11) NOT NULL DEFAULT 0,
		  PRIMARY KEY (id)
		) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );
	}


}
WBEC_Active::init();