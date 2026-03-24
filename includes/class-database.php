<?php
/**
 * Classe responsável pelas operações com banco de dados.
 *
 * Gerencia a criação da tabela de favoritos e todas as
 * operações de leitura, inserção e remoção de registros.
 *
 * @package WordPressBackEndChallenge
 */

// Impede acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe WPBEC_Database.
 *
 * Responsável pela criação da tabela e operações CRUD
 * envolvendo favoritos de posts.
 */
class WPBEC_Database {

	/**
	 * Nome da tabela de favoritos (com prefixo do WordPress).
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Construtor – define o nome da tabela usando o prefixo do WordPress.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'favorites';
	}

	/**
	 * Cria a tabela de favoritos utilizando dbDelta.
	 *
	 * Deve ser chamada na ativação do plugin.
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			post_id BIGINT UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY user_post (user_id, post_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Remove a tabela de favoritos do banco de dados.
	 *
	 * Utilizada na desinstalação do plugin.
	 *
	 * @return void
	 */
	public function drop_table() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );
	}

	/**
	 * Verifica se um post já está favoritado pelo usuário.
	 *
	 * @param int $user_id ID do usuário.
	 * @param int $post_id ID do post.
	 * @return bool True se o registro existir; false caso contrário.
	 */
	public function is_favorited( $user_id, $post_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
				$user_id,
				$post_id
			)
		);

		return (int) $result > 0;
	}

	/**
	 * Adiciona um post aos favoritos do usuário.
	 *
	 * @param int $user_id ID do usuário.
	 * @param int $post_id ID do post.
	 * @return bool True em caso de sucesso; false em caso de falha.
	 */
	public function add_favorite( $user_id, $post_id ) {
		global $wpdb;

		$result = $wpdb->insert(
			$this->table_name,
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
	 * @return bool True em caso de sucesso; false em caso de falha.
	 */
	public function remove_favorite( $user_id, $post_id ) {
		global $wpdb;

		$result = $wpdb->delete(
			$this->table_name,
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
			),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Retorna a lista de IDs de posts favoritados pelo usuário.
	 *
	 * Utiliza a Transient API para cache, melhorando a performance.
	 *
	 * @param int $user_id ID do usuário.
	 * @return array Lista de IDs de posts favoritados.
	 */
	public function get_favorites( $user_id ) {
		$transient_key = 'wpbec_favorites_user_' . $user_id;
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$this->table_name} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);

		$favorites = array_map( 'intval', $results );

		// Armazena no cache por 1 hora.
		set_transient( $transient_key, $favorites, HOUR_IN_SECONDS );

		return $favorites;
	}

	/**
	 * Invalida o cache de favoritos de um usuário.
	 *
	 * Deve ser chamada sempre que os favoritos forem alterados.
	 *
	 * @param int $user_id ID do usuário.
	 * @return void
	 */
	public function clear_cache( $user_id ) {
		delete_transient( 'wpbec_favorites_user_' . $user_id );
	}
}
