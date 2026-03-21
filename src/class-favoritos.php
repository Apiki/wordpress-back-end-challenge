<?php
/**
 * Classe responsável pelas rotas e lógica de favoritos.
 *
 * @package ApikiFavoritosPosts
 */

declare(strict_types=1);

/**
 * Favoritos via WP REST API.
 */
class Favoritos
{
    /**
     * Namespace da API.
     *
     * @var string
     */
    private $_namespace = 'apiki/v1';

    /**
     * Nome da tabela.
     *
     * @var string
     */
    private $_tabela;

    /**
     * Construtor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->_tabela = $wpdb->prefix . Tabela::NOME;
    }

    /**
     * Registra as rotas da REST API.
     *
     * @return void
     */
    public function registrarRotas()
    {
        register_rest_route($this->_namespace, '/favoritos', [
            'methods'             => 'GET',
            'callback'            => [$this, 'listar'],
            'permission_callback' => [$this, 'usuarioLogado'],
        ]);

        register_rest_route($this->_namespace, '/favoritos/(?P<post_id>\d+)', [
            'methods'             => 'POST',
            'callback'            => [$this, 'favoritar'],
            'permission_callback' => [$this, 'usuarioLogado'],
        ]);

        register_rest_route($this->_namespace, '/favoritos/(?P<post_id>\d+)/deletar', [
            'methods'             => 'POST',
            'callback'            => [$this, 'desfavoritar'],
            'permission_callback' => [$this, 'usuarioLogado'],
        ]);
    }

    /**
     * Verifica se o usuário está logado.
     *
     * @return bool
     */
    public function usuarioLogado()
    {
        return is_user_logged_in();
    }

    /**
     * Lista os posts favoritados pelo usuário logado.
     *
     * @return WP_REST_Response
     */
    public function listar()
    {
        global $wpdb;

        $usuarioId  = get_current_user_id();
        $favoritos  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, criado_em FROM {$this->_tabela}
                WHERE usuario_id = %d ORDER BY criado_em DESC",
                $usuarioId
            )
        );

        return rest_ensure_response($favoritos);
    }

    /**
     * Favorita um post para o usuário logado.
     *
     * @param WP_REST_Request $requisicao dados da requisição.
     *
     * @return WP_REST_Response
     */
    public function favoritar($requisicao)
    {
        global $wpdb;

        $postId    = (int) $requisicao['post_id'];
        $usuarioId = get_current_user_id();

        if (!get_post($postId)) {
            return new WP_REST_Response(
                ['mensagem' => 'Post não encontrado.'],
                404
            );
        }

        $jaFavoritado = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->_tabela}
                WHERE usuario_id = %d AND post_id = %d",
                $usuarioId,
                $postId
            )
        );

        if ($jaFavoritado) {
            return new WP_REST_Response(
                ['mensagem' => 'Post já está nos favoritos.'],
                409
            );
        }

        $wpdb->insert(
            $this->_tabela,
            [
                'usuario_id' => $usuarioId,
                'post_id'    => $postId,
            ],
            ['%d', '%d']
        );

        return new WP_REST_Response(
            ['mensagem' => 'Post favoritado com sucesso.'],
            201
        );
    }

    /**
     * Desfavorita um post para o usuário logado.
     *
     * @param WP_REST_Request $requisicao dados da requisição.
     *
     * @return WP_REST_Response
     */
    public function desfavoritar($requisicao)
    {
        global $wpdb;

        $postId    = (int) $requisicao['post_id'];
        $usuarioId = get_current_user_id();

        $deletado = $wpdb->delete(
            $this->_tabela,
            [
                'usuario_id' => $usuarioId,
                'post_id'    => $postId,
            ],
            ['%d', '%d']
        );

        if (!$deletado) {
            return new WP_REST_Response(
                ['mensagem' => 'Post não está nos favoritos.'],
                404
            );
        }

        return new WP_REST_Response(
            ['mensagem' => 'Post desfavoritado com sucesso.'],
            200
        );
    }
}
