<?php
class Apiki_Route_Controller extends WP_REST_Controller
{

    // Here initialize our namespace and resource name.
    public function __construct()
    {
        $this->namespace     = '/apikirestfavorite/v1';
        $this->resource_name = 'posts';
    }

    // Registrando as rotas do plugin
    public function register_routes()
    {

        register_rest_route(
            $this->namespace,
            '/' . $this->resource_name . '/(?P<postid>[\d]+)',
            array(
                array(
                    'methods'   => WP_REST_Server::CREATABLE,
                    'callback'  => array($this, 'setPostFavorite'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->resource_name . '/(?P<postid>[\d]+)'.'/(?P<userid>[\d]+)',
            array(
                array(
                    'methods'   => WP_REST_Server::READABLE,
                    'callback'  => array($this, 'getPostFavorite'),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->resource_name . '/(?P<postid>[\d]+)',
            array(
                array(
                    'methods'   => WP_REST_Server::DELETABLE,
                    'callback'  => array($this, 'unsetPostFavorite'),
                ),
            )
        );
    }

    /**
     * 
     *
     * @param WP_REST_Request $request Current request.
     */
    public function getPostFavorite($request)
    {
        $userid = (int) $request['userid'];
        $postid = (int) $request['postid'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'favorite';
        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE post_ID= '" . $postid . "' and user_ID= '" . $userid . "' ");
        if ($wpdb->num_rows == 0) {
            return new WP_REST_Response(
                array(
                    'status' => '200',
                    'response' => 'FALSE'
                )
            );
        } else {
            return new WP_REST_Response(
                array(
                    'status' => '200',
                    'response' => 'TRUE'
                )
            );
        }
    }

    /**
     *
     * @param WP_REST_Request $request Current request.
     */
    public function setPostFavorite($request)
    {

        $postid = (int) $request['postid'];
        $userid = (int) $request['user'];
        $parameters = $request->get_json_params();

        if (wp_create_nonce('wp_rest') == $request->get_header('X-WP-Nonce') && isset($userid) && $userid <> 0) {

            global $wpdb;
            $table_name = $wpdb->prefix . 'favorite';
            $data = $wpdb->get_results("SELECT * FROM $table_name WHERE post_ID= '" . $postid . "' and user_ID= '" . $userid . "' ");
            if ($wpdb->num_rows == 0) {
                $wpdb->query($wpdb->prepare("insert into " . $table_name . " (post_ID, user_ID) VALUES (" . $postid . " ," . $userid . ")"));
                return new WP_REST_Response(
                    array(
                        'status' => '200',
                        'response' => 'Post favoritado com sucesso.',
                        'body_response' => $parameters
                    )
                );
            } else {
                return new WP_REST_Response(
                    array(
                        'status' => '202',
                        'response' => 'Requisição aceita, mas não processada.',
                        'body_response' => $parameters
                    )
                );
            }
        } else {
            return new WP_REST_Response('Usuário não autorizado ou não logado.', '401');
        }
    }

    /**
     *
     * @param WP_REST_Request $request Current request.
     */
    public function unsetPostFavorite($request)
    {

        $postid = (int) $request['postid'];
        $userid = (int) $request['user'];
        $parameters = $request->get_json_params();

        if (wp_create_nonce('wp_rest') == $request->get_header('X-WP-Nonce') && isset($userid) && $userid <> 0) {

            global $wpdb;
            $table_name = $wpdb->prefix . 'favorite';

            $data = $wpdb->get_results("SELECT * FROM $table_name WHERE post_ID = '" . $postid . "' and user_ID= '" . $userid . "' ");

            if ($wpdb->num_rows > 0) {
                $wpdb->query($wpdb->prepare("delete from " . $table_name . " where post_ID =" . $postid . " and user_ID =" . $userid));
                return new WP_REST_Response(
                    array(
                        'status' => '200',
                        'response' => 'Post desfavoritado com sucesso.',
                        'body_response' => $parameters
                    )
                );    
            } else {
                return new WP_REST_Response(
                    array(
                        'status' => '202',
                        'response' => 'Requisição aceita, mas não processada.',
                        'body_response' => $parameters
                    )
                );
            }
        } else {
            return new WP_REST_Response('Usuário não autorizado ou não logado.', '401');
        }
    }
}

// Function to register our new routes from the controller.
function Apiki_REST_Favorite_Route_Controller()
{
    $controller = new Apiki_Route_Controller();
    $controller->register_routes();
}
