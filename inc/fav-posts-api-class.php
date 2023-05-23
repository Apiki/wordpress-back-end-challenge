<?php
/**
 * [Description PostsFavoritosEndpoints]
 */
class PostsFavoritosEndpoints extends WP_REST_Controller
{
    private $posts_favoritos_plugin;

    public function __construct($posts_favoritos_plugin)
    {
        $this->posts_favoritos_plugin = $posts_favoritos_plugin;
        add_action('rest_api_init', array($this, 'register_endpoints'));
        add_filter('wp_is_application_passwords_available', '__return_true');
    }

    /**
     * registra as rotas
     * @return void
     */
    public function register_endpoints()
    {
        register_rest_route(
            $this->posts_favoritos_plugin->endpoint,
            '/add',
            array(
                'methods' => 'POST',
                'permission_callback' => 'is_user_logged_in',
                'callback' => array($this, 'add_favorito'),
                'args' => array(
                    'post_id' => array(
                        'type' => 'integer',
                        'description' => 'Id do Post',
                        'required' => true,
                    )
                )
            )
        );

        register_rest_route(
            $this->posts_favoritos_plugin->endpoint,
            '/remove',
            array(
                'methods' => 'POST',
                'permission_callback' => 'is_user_logged_in',
                'callback' => array($this, 'remove_favorito'),
                'args' => array(
                    'post_id' => array(
                        'type' => 'integer',
                        'description' => 'Id do Post',
                        'required' => true,
                    )
                )
            )
        );
    }

    /**
     * @param object $request
     * 
     * @return [type]
     */
    public function add_favorito($request)
    {
        $current_user_id = get_current_user_id();
        $post_id = (int) $request->get_param('post_id');
        if (empty($post_id)) {
            return new WP_Error(
                'rest_invalid_param',
                'Parametro post_id invalido',
                array('status' => 400)
            );
        }
        if ($this->posts_favoritos_plugin->is_post_favorito($post_id, $current_user_id)) {
            return new WP_Error(
                'rest_invalid_param',
                'Post id já adicionado aos favoritos',
                array('status' => 400)
            );
        }
        if ($this->posts_favoritos_plugin->add_favorito($post_id, $current_user_id)) {
            $response = rest_ensure_response(
                array(
                    'message' => 'Adicionado com sucesso',
                    'data' => array(
                        'post_id' => $post_id,
                        'user_id' => $current_user_id
                    )
                )
            );
            $response->set_status(200);
            return $response;
        }

        return new WP_Error(
            'rest_invalid_param',
            'Verifique os parametros e tente novamente',
            array('status' => 400)
        );
    }

    // Método para remover um post dos favoritos
    public function remove_favorito($request)
    {
        $post_id = $request->get_param('post_id');
        $current_user_id = get_current_user_id();
        $post_id = (int) $request->get_param('post_id');
        if (empty($post_id)) {
            return new WP_Error(
                'rest_invalid_param',
                'Parametro post_id invalido',
                array('status' => 400)
            );
        }
        if (!$this->posts_favoritos_plugin->is_post_favorito($post_id, $current_user_id)) {
            return new WP_Error(
                'rest_invalid_param',
                'Post id não adicionado aos favoritos',
                array('status' => 400)
            );
        }
        if ($this->posts_favoritos_plugin->remove_favorito($post_id, $current_user_id)) {
            $response = rest_ensure_response(
                array(
                    'message' => 'Removido com sucesso',
                    'data' => array(
                        'post_id' => $post_id,
                        'user_id' => $current_user_id
                    )
                )
            );
            $response->set_status(200);
            return $response;
        }
        return new WP_Error(
            'rest_invalid_param',
            'Verifique os parametros e tente novamente',
            array('status' => 400)
        );
    }
}