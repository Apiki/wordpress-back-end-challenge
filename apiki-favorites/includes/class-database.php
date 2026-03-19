<?php
/**
 * Classe responsável pela tabela customizada de favoritos.
 *
 * @package Apiki_Favorites
 */

defined( 'ABSPATH' ) || exit;

class Apiki_Favorites_Database {

	/**
	 * Retorna o nome da tabela.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'apiki_favorites';
	}

	/**
	 * Cria a tabela customizada no banco de dados.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_post (user_id, post_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Adiciona um post aos favoritos do usuário.
	 *
	 * @param int $user_id ID do usuário.
	 * @param int $post_id ID do post.
	 * @return bool|WP_Error
	 */
	public static function add_favorite( $user_id, $post_id ) {
		global $wpdb;

		if ( self::is_favorite( $user_id, $post_id ) ) {
			return new WP_Error(
				'already_favorited',
				'Este post já está nos seus favoritos.',
				array( 'status' => 400 )
			);
		}

		$result = $wpdb->insert(
			self::get_table_name(),
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
			),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Remove um post dos favoritos do usuário.
	 *
	 * @param int $user_id ID do usuário.
	 * @param int $post_id ID do post.
	 * @return bool|WP_Error
	 */
	public static function remove_favorite( $user_id, $post_id ) {
		global $wpdb;

		if ( ! self::is_favorite( $user_id, $post_id ) ) {
			return new WP_Error(
				'not_favorited',
				'Este post não está nos seus favoritos.',
				array( 'status' => 404 )
			);
		}

		$result = $wpdb->delete(
			self::get_table_name(),
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
			),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Verifica se um post é favorito do usuário.
	 *
	 * @param int $user_id ID do usuário.
	 * @param int $post_id ID do post.
	 * @return bool
	 */
	public static function is_favorite( $user_id, $post_id ) {
		global $wpdb;

		$table_name = self::get_table_name();

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND post_id = %d",
				$user_id,
				$post_id
			)
		);

		return absint( $count ) > 0;
	}

	/**
	 * Retorna os favoritos de um usuário.
	 *
	 * @param int $user_id ID do usuário.
	 * @return array
	 */
	public static function get_user_favorites( $user_id ) {
		global $wpdb;

		$table_name = self::get_table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, created_at FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);
	}
}
