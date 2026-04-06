<?php
/**
 * Plugin Name:     To Like Plugin
 * Description:     WordPress plugin that allows authenticated users to favorite and unfavorite posts via the REST API, with data persistence in a custom table.
 * Author:          Lucas Felype Fonseca Santos
 * Text Domain:     to-like-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         To_Like_Plugin
 */

defined('ABSPATH') || exit;

define('TLP_PLUGIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'includes/rest-routes.php';


class To_Like_Plugin
{
	public function activate()
	{
		$this->create_favorites_table();
	}

	public function deactivate()
	{
		return;
	}

	private function create_favorites_table()
	{
		global $wpdb;

		$table_name = self::get_table_name();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_post_unique (user_id, post_id)
		) {$charset_collate};";

		dbDelta($sql);
	}

	private static function get_table_name()
	{
		global $wpdb;

		return $wpdb->prefix . 'user_favorite_challenge';
	}

	public static function add_favorite($user_id, $post_id)
	{
		if (self::favorite_exists($user_id, $post_id)) {
			return false;
		}

		global $wpdb;

		$table_name = self::get_table_name();

		$inserted = $wpdb->insert(
			$table_name,
			[
				'user_id'    => $user_id,
				'post_id'    => $post_id,
				'created_at' => current_time('mysql'),
			],
			[
				'%d',
				'%d',
				'%s',
			]
		);

		return $inserted !== false;
	}

	public static function favorite_exists($user_id, $post_id)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE user_id = %d AND post_id = %d",
				$user_id,
				$post_id
			)
		);

		return !is_null($result);
	}

	public static function remove_favorite($user_id, $post_id)
	{
		if (!self::favorite_exists($user_id, $post_id)) {
			return false;
		}

		global $wpdb;

		$table_name = self::get_table_name();

		$deleted = $wpdb->delete(
			$table_name,
			[
				'user_id' => $user_id,
				'post_id' => $post_id,
			],
			[
				'%d',
				'%d',
			]
		);

		return $deleted !== false;
	}

	public static function get_user_favorites($user_id)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, created_at FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);

		if (empty($results)) {
			return [];
		}

		$favorites = [];

		foreach ($results as $result) {
			$post = get_post($result->post_id);

			if (!$post || 'publish' !== $post->post_status) {
				continue;
			}

			$favorites[] = [
				'post_id'    => $post->ID,
				'title'      => get_the_title($post->ID),
				'link'       => get_permalink($post->ID),
				'created_at' => $result->created_at,
			];
		}

		return $favorites;
	}
}

$to_like_plugin = new To_Like_Plugin();

register_activation_hook(TLP_PLUGIN_FILE, [$to_like_plugin, 'activate']);
register_deactivation_hook(TLP_PLUGIN_FILE, [$to_like_plugin, 'deactivate']);
