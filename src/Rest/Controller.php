<?php

namespace Apiki\FavoritePosts\Rest;

use Apiki\FavoritePosts\Database\FavoriteRepository;

/**
 * Class Controller
 *
 * Controlador responsável por registrar e processar os endpoints da WP REST API
 * para favoritar e desfavoritar posts de usuários autenticados.
 *
 * Segue estritamente as diretrizes da classe WP_REST_Controller do WordPress.
 *
 * @package Apiki\FavoritePosts\Rest
 */
class Controller
{
    /**
     * O namespace da API REST do plugin.
     *
     * @var string
     */
    protected $namespace = 'apiki/v1';

    /**
     * O recurso base da API REST.
     *
     * @var string
     */
    protected $rest_base = 'favorites';

    /**
     * Instância do repositório de favoritos.
     *
     * @var FavoriteRepository
     */
    private $repository;

    /**
     * Controller constructor.
     *
     * @param FavoriteRepository|null $repository Injeção de dependência para facilitação de testes unitários.
     */
    public function __construct($repository = null)
    {
        $this->repository = $repository ?: new FavoriteRepository();
    }

    /**
     * Registra as rotas da WP REST API.
     *
     * Rota 1: GET  /apiki/v1/favorites          - Obtém lista paginada de favoritos do usuário logado.
     * Rota 2: POST /apiki/v1/favorites          - Favorita um post enviado como JSON (e.g. {"post_id": 123}).
     * Rota 3: DELETE /apiki/v1/favorites/{id}   - Remove um post dos favoritos com base no ID da URL (RESTful).
     */
    public function register_routes()
    {
        // Rota Geral para listar e favoritar
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => \WP_REST_Server::READABLE, // GET
                    'callback'            => [$this, 'get_items'],
                    'permission_callback' => [$this, 'check_permission'],
                    'args'                => [
                        'page' => [
                            'description'       => 'Página atual dos resultados.',
                            'type'              => 'integer',
                            'default'           => 1,
                            'sanitize_callback' => 'absint',
                            'validate_callback' => function($value) {
                                return is_numeric($value) && $value > 0;
                            }
                        ],
                        'per_page' => [
                            'description'       => 'Quantidade de registros por página.',
                            'type'              => 'integer',
                            'default'           => 10,
                            'sanitize_callback' => 'absint',
                            'validate_callback' => function($value) {
                                return is_numeric($value) && $value > 0 && $value <= 100;
                            }
                        ]
                    ]
                ],
                [
                    'methods'             => \WP_REST_Server::CREATABLE, // POST
                    'callback'            => [$this, 'create_item'],
                    'permission_callback' => [$this, 'check_permission'],
                    'args'                => [
                        'post_id' => [
                            'description'       => 'ID do post que será favoritado.',
                            'type'              => 'integer',
                            'required'          => true,
                            'sanitize_callback' => 'absint',
                            'validate_callback' => function($value) {
                                return is_numeric($value) && $value > 0;
                            }
                        ]
                    ]
                ]
            ]
        );

        // Rota específica para desfavoritar (padrão RESTful via ID no path parameter)
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<post_id>\d+)',
            [
                [
                    'methods'             => \WP_REST_Server::DELETABLE, // DELETE
                    'callback'            => [$this, 'delete_item'],
                    'permission_callback' => [$this, 'check_permission'],
                    'args'                => [
                        'post_id' => [
                            'description'       => 'ID do post a ser desfavoritado.',
                            'type'              => 'integer',
                            'required'          => true,
                            'sanitize_callback' => 'absint',
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Validação global de permissão REST. Encoraja que o usuário esteja autenticado no sistema.
     *
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function check_permission($request)
    {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                'Desculpe, você precisa estar autenticado para realizar esta ação.',
                ['status' => 401]
            );
        }
        return true;
    }

    /**
     * Retorna a lista de posts favoritados do usuário logado (GET).
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_items($request)
    {
        $user_id = get_current_user_id();
        $page = (int) $request->get_param('page');
        $per_page = (int) $request->get_param('per_page');
        $offset = ($page - 1) * $per_page;

        $favorites = $this->repository->get_user_favorites($user_id, $per_page, $offset);
        $total_favorites = $this->repository->count_user_favorites($user_id);
        $total_pages = $total_favorites > 0 ? ceil($total_favorites / $per_page) : 1;

        $response = rest_ensure_response($favorites);

        // Adiciona cabeçalhos HTTP padrões de paginação da WP REST API
        $response->header('X-WP-Total', $total_favorites);
        $response->header('X-WP-TotalPages', (int) $total_pages);

        return $response;
    }

    /**
     * Adiciona um post aos favoritos do usuário logado (POST).
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_item($request)
    {
        $post_id = (int) $request->get_param('post_id');
        $user_id = get_current_user_id();

        // 1. Valida se o post existe no WordPress
        $post = get_post($post_id);
        if (!$post) {
            return new \WP_Error(
                'apiki_post_not_found',
                'O post informado não foi encontrado.',
                ['status' => 404]
            );
        }

        // 2. Valida se o post está publicado e pertence a um tipo público e visualizável
        if ($post->post_status !== 'publish' || !is_post_type_viewable($post->post_type)) {
            return new \WP_Error(
                'apiki_invalid_post',
                'Não é permitido favoritar posts privados, rascunhos ou de tipos não públicos.',
                ['status' => 400]
            );
        }

        // 3. Tenta salvar o favorito
        $added = $this->repository->add($user_id, $post_id);

        if (!$added) {
            // Se falhou, checamos se é porque já existe
            if ($this->repository->is_favorited($user_id, $post_id)) {
                return new \WP_Error(
                    'apiki_already_favorited',
                    'Este post já está nos seus favoritos.',
                    ['status' => 400]
                );
            }

            return new \WP_Error(
                'apiki_db_error',
                'Erro interno ao salvar favorito no banco de dados.',
                ['status' => 500]
            );
        }

        return new \WP_REST_Response(
            [
                'success' => true,
                'message' => 'Post favoritado com sucesso.',
                'post_id' => $post_id,
            ],
            201
        );
    }

    /**
     * Remove um post dos favoritos do usuário logado (DELETE).
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item($request)
    {
        $post_id = (int) $request->get_param('post_id');
        $user_id = get_current_user_id();

        // Tenta remover
        $removed = $this->repository->remove($user_id, $post_id);

        if (!$removed) {
            return new \WP_Error(
                'apiki_not_favorited',
                'Este post não está na sua lista de favoritos.',
                ['status' => 404]
            );
        }

        return new \WP_REST_Response(
            [
                'success' => true,
                'message' => 'Post removido dos favoritos com sucesso.',
                'post_id' => $post_id,
            ],
            200
        );
    }
}
