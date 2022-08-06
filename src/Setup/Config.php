<?php
/**
 * Initial setup for the WP Backend Challenge plugin.
 *
 * @package WP_Backend_Challenge
 * @author  Luis Paiva <contato@luispaiva.com.br>
 *
 * @version 1.0.0
 */

namespace App\Setup;

/**
 * Class Config.
 */
class Config {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Loads the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain( 'wp-back-end-challenge', false, PLUGIN_DIRECTORY_PATH . '/languages' );
	}

	/**
	 * Activation the plugin.
	 *
	 * @return void
	 */
	public static function activation(): void {
		self::create_tables();
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public static function deactivation(): void {
		flush_rewrite_rules();
	}

	/**
	 * Uninstall the plugin.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			die;
		}
	}

	/**
	 * Create the tables.
	 *
	 * @return void
	 */
	public static function create_tables(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( self::get_schema() );
	}

	/**
	 * Get the schema for the database.
	 *
	 * @return string
	 */
	private static function get_schema(): string {
		global $wpdb;

		$collate = $wpdb->get_charset_collate();
		$tables  = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}favorite_posts (
            id INTEGER NOT NULL AUTO_INCREMENT,
            post_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            liked TINYINT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$collate};";

		return $tables;
	}

}
