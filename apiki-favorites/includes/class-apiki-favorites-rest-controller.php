<?php
if (!defined('ABSPATH')) {
    exit;
}

class Apiki_Favorites_REST_Controller extends WP_REST_Controller
{

    /**
     * @var string
     */
    protected $namespace = 'apiki/v1';

    /**
     * @var string
     */
    protected $rest_base = 'favorites';

    /**
     * @var Apiki_Favorites_Repository
     */
    private $repository;

    /**
     * @param Apiki_Favorites_Repository $repository
     */
    public function __construct(Apiki_Favorites_Repository $repository)
    {
        $this->repository = $repository;
    }

    public function register_routes(): void
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_items'),
                    'permission_callback' => array($this, 'permissions_check'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<post_id>\d+)',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_item'),
                    'permission_callback' => array($this, 'permissions_check'),
                    'args' => $this->get_post_id_args(),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_item'),
                    'permission_callback' => array($this, 'permissions_check'),
                    'args' => $this->get_post_id_args(),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array($this, 'delete_item'),
                    'permission_callback' => array($this, 'permissions_check'),
                    'args' => $this->get_post_id_args(),
                ),
            )
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_items($request): WP_Error|WP_REST_Response
    {
        $user_id = get_current_user_id();
        $favorites = $this->repository->get_user_favorites($user_id);
        $items = array();

        foreach ($favorites as $favorite) {
            $post = get_post((int)$favorite['post_id']);

            if (!$post || 'publish' !== $post->post_status) {
                continue;
            }

            $items[] = array(
                'post_id' => (int)$post->ID,
                'title' => get_the_title($post),
                'permalink' => get_permalink($post),
                'favorited' => true,
                'created_at' => mysql_to_rfc3339($favorite['created_at']),
            );
        }

        return new WP_REST_Response(
            array(
                'items' => $items,
                'total' => count($items),
            ),
            200
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function get_item($request): WP_Error|WP_REST_Response|WP_Post
    {
        $post_id = (int)$request->get_param('post_id');
        $post = $this->validate_post($post_id);

        if (is_wp_error($post)) {
            return $post;
        }

        $user_id = get_current_user_id();
        $favorited = $this->repository->exists($user_id, $post_id);

        return new WP_REST_Response(
            array(
                'post_id' => $post_id,
                'favorited' => $favorited,
            ),
            200
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response|WP_Post
     */
    public function create_item($request): WP_Error|WP_REST_Response|WP_Post
    {
        $post_id = (int)$request->get_param('post_id');
        $post = $this->validate_post($post_id);

        if (is_wp_error($post)) {
            return $post;
        }

        $user_id = get_current_user_id();
        $result = $this->repository->favorite($user_id, $post_id);

        if (!$result) {
            return new WP_Error(
                'apiki_favorites_insert_error',
                __('Não foi possível favoritar o post.', 'apiki-favorites'),
                array('status' => 500)
            );
        }

        return new WP_REST_Response(
            array(
                'message' => __('Post favoritado com sucesso.', 'apiki-favorites'),
                'post_id' => $post_id,
                'favorited' => true,
            ),
            201
        );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response|WP_Post
     */
    public function delete_item($request): WP_Error|WP_REST_Response|WP_Post
    {
        $post_id = (int)$request->get_param('post_id');
        $post = $this->validate_post($post_id);

        if (is_wp_error($post)) {
            return $post;
        }

        $user_id = get_current_user_id();
        $result = $this->repository->unfavorite($user_id, $post_id);

        if (false === $result) {
            return new WP_Error(
                'apiki_favorites_delete_error',
                __('Não foi possível desfavoritar o post.', 'apiki-favorites'),
                array('status' => 500)
            );
        }

        return new WP_REST_Response(
            array(
                'message' => __('Post desfavoritado com sucesso.', 'apiki-favorites'),
                'post_id' => $post_id,
                'favorited' => false,
            ),
            200
        );
    }

    /**
     * @return array
     */
    private function get_post_id_args(): array
    {
        return array(
            'post_id' => array(
                'required' => true,
                'validate_callback' => array($this, 'validate_post_id_param'),
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * @param $value
     * @return bool
     */
    public function validate_post_id_param($value): bool
    {
        return is_numeric($value) && (int)$value > 0;
    }

    /**
     * @return bool|WP_Error
     */
    public function permissions_check(): WP_Error|bool
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'apiki_favorites_forbidden',
                __('Usuário não autenticado.', 'apiki-favorites'),
                array('status' => rest_authorization_required_code())
            );
        }

        return true;
    }

    /**
     * @param int $post_id
     * @return WP_Post|WP_Error
     */
    private function validate_post(int $post_id): WP_Error|WP_Post
    {
        $post = get_post($post_id);

        if (!$post || 'post' !== $post->post_type) {
            return new WP_Error(
                'apiki_favorites_invalid_post',
                __('Post inválido.', 'apiki-favorites'),
                array('status' => 404)
            );
        }

        if ('publish' !== $post->post_status) {
            return new WP_Error(
                'apiki_favorites_unavailable_post',
                __('Post indisponível para favoritamento.', 'apiki-favorites'),
                array('status' => 404)
            );
        }

        return $post;
    }
}