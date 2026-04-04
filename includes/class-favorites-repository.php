<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apiki_Favorites_Repository {

	protected $wpdb;

	protected $table_name;

	public function __construct( $database = null ) {
		global $wpdb;

		$this->wpdb       = $database ? $database : $wpdb;
		$this->table_name = $this->wpdb->prefix . 'apiki_favorites';
	}

	public function exists( $user_id, $post_id ) {
		$favorite_id = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->table_name} WHERE user_id = %d AND post_id = %d LIMIT 1",
				$user_id,
				$post_id
			)
		);

		return ! empty( $favorite_id );
	}

	public function insert( $user_id, $post_id ) {
		$result = $this->wpdb->insert(
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

		return false !== $result;
	}

	public function delete( $user_id, $post_id ) {
		$result = $this->wpdb->delete(
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

		return false !== $result && $result > 0;
	}
}
