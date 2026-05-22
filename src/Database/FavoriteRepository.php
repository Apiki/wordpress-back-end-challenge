<?php

namespace Apiki\FavoritePosts\Database;

/**
 * Class FavoriteRepository
 *
 * Repositório responsável pela camada de persistência de dados de favoritos no banco de dados.
 * Utiliza o objeto $wpdb do WordPress de forma encapsulada para maior segurança e testabilidade.
 *
 * @package Apiki\FavoritePosts\Database
 */
class FavoriteRepository
{
    /**
     * Instância do objeto de banco de dados do WordPress ($wpdb).
     *
     * @var \wpdb
     */
    private $db;

    /**
     * Nome completo da tabela de favoritos (com prefixo).
     *
     * @var string
     */
    private $table_name;

    /**
     * FavoriteRepository constructor.
     *
     * @param \wpdb|null $db Injeção de dependência para facilitação de testes unitários isolados.
     */
    public function __construct($db = null)
    {
        global $wpdb;
        $this->db = $db ?: $wpdb;
        $this->table_name = $this->db->prefix . Migrator::TABLE_NAME;
    }

    /**
     * Adiciona um post aos favoritos do usuário.
     *
     * @param int $user_id ID do usuário logado.
     * @param int $post_id ID do post a ser favoritado.
     * @return bool Retorna true se foi favoritado com sucesso, false se já era favorito ou em caso de falha.
     */
    public function add($user_id, $post_id)
    {
        if ($this->is_favorited($user_id, $post_id)) {
            return false;
        }

        $result = $this->db->insert(
            $this->table_name,
            [
                'user_id' => (int) $user_id,
                'post_id' => (int) $post_id,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%d', '%s']
        );

        return $result !== false;
    }

    /**
     * Remove um post dos favoritos do usuário.
     *
     * @param int $user_id ID do usuário logado.
     * @param int $post_id ID do post a ser desfavoritado.
     * @return bool Retorna true se removido com sucesso, false se não era favorito ou em caso de falha.
     */
    public function remove($user_id, $post_id)
    {
        if (!$this->is_favorited($user_id, $post_id)) {
            return false;
        }

        $result = $this->db->delete(
            $this->table_name,
            [
                'user_id' => (int) $user_id,
                'post_id' => (int) $post_id,
            ],
            ['%d', '%d']
        );

        return $result !== false;
    }

    /**
     * Verifica se um post já está favoritado por um usuário específico.
     *
     * @param int $user_id ID do usuário.
     * @param int $post_id ID do post.
     * @return bool
     */
    public function is_favorited($user_id, $post_id)
    {
        $query = $this->db->prepare(
            "SELECT COUNT(1) FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
            (int) $user_id,
            (int) $post_id
        );

        return (bool) $this->db->get_var($query);
    }

    /**
     * Obtém a lista paginada de IDs de posts favoritados por um usuário específico.
     *
     * @param int $user_id ID do usuário.
     * @param int $limit Limite de registros a retornar (paginação).
     * @param int $offset Deslocamento inicial de registros (paginação).
     * @return array Array de IDs de posts favoritados.
     */
    public function get_user_favorites($user_id, $limit = 10, $offset = 0)
    {
        $query = $this->db->prepare(
            "SELECT post_id FROM {$this->table_name} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            (int) $user_id,
            (int) $limit,
            (int) $offset
        );

        $results = $this->db->get_col($query);

        return array_map('intval', $results ?: []);
    }

    /**
     * Conta o total de posts favoritados por um usuário específico.
     *
     * @param int $user_id ID do usuário.
     * @return int
     */
    public function count_user_favorites($user_id)
    {
        $query = $this->db->prepare(
            "SELECT COUNT(1) FROM {$this->table_name} WHERE user_id = %d",
            (int) $user_id
        );

        return (int) $this->db->get_var($query);
    }
}
