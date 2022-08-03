<?php
/**
 * Favorite Model WordPress Back-end Challenge.
 *
 * PHP version 7.4
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/wordpress-back-end-challenge
 */

namespace App\Models;

/**
 * Favorite Model Class.
 *
 * @category Challenge
 * @package  WP_Backend_Challenge
 * @author   Luis Paiva <contato@luispaiva.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/luispaiva/back-end-challenge/tree/luis-paiva
 */
class Favorite {

	/**
	 * WPDB instance.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		global $wpdb, $table_prefix;

		$this->db    = $wpdb;
		$this->table = $table_prefix . 'favorite_posts';
	}

	/**
	 * Create favorite post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id User ID.
	 *
	 * @return bool
	 */
	public function create( int $post_id, int $user_id ) {
		return $this->db->query(
			$this->db->prepare(
				"INSERT INTO {$this->table} ( post_id, user_id, liked ) VALUES (%d, %d, %d)",
				$post_id,
				$user_id,
				1
			)
		);
	}

	/**
	 * Read favorite post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id User ID.
	 *
	 * @return object
	 */
	public function read( $post_id, $user_id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT id, liked FROM {$this->table} WHERE post_id = %d AND user_id = %d",
				$post_id,
				$user_id
			)
		);
	}

	/**
	 * Update favorite post.
	 *
	 * @param int $id     Record ID.
	 * @param int $status Status.
	 *
	 * @return null|false
	 */
	public function update( int $id, int $status ) {
		return $this->db->query(
			$this->db->prepare(
				"UPDATE {$this->table} SET liked = %d WHERE id = %d",
				$status,
				$id
			)
		);
	}
}
