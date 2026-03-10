<?php
/**
 * Class to handle favorites database operations.
 *
 * @package Apiki_Favorites
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apiki Favorites Repository Class.
 */
class Apiki_Favorites_Repository {

	/**
	 * Database table name.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'apiki_favorites';
	}

	/**
	 * Add a favorite for a user and post.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return bool True on success, false on failure.
	 */
	public function add_favorite( $user_id, $post_id ) {
		global $wpdb;

		$result = $wpdb->insert(
			$this->table_name,
			array(
				'user_id'    => $user_id,
				'post_id'    => $post_id,
				'created_at' => current_time( 'mysql' ),
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);

		return $result !== false;
	}

	/**
	 * Remove a favorite for a user and post.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return bool True on success, false on failure.
	 */
	public function remove_favorite( $user_id, $post_id ) {
		global $wpdb;

		$result = $wpdb->delete(
			$this->table_name,
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
			),
			array(
				'%d',
				'%d',
			)
		);

		return $result !== false;
	}

	/**
	 * Check if a post is already favorited by a user.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return bool True if already favorited, false otherwise.
	 */
	public function is_favorited( $user_id, $post_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
			$user_id,
			$post_id
		);

		$count = $wpdb->get_var( $query );

		return (int) $count > 0;
	}

	/**
	 * Get favorite ID by user and post.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return int|null Favorite ID or null if not found.
	 */
	public function get_favorite_id( $user_id, $post_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT id FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
			$user_id,
			$post_id
		);

		return $wpdb->get_var( $query );
	}
}
