<?php
/*
Plugin Name: Favoritar Post
Description: Plugin criado favoritar ou desfavoritar um Post.
Version: 1.0
Author: Hector Jaime Rondon Castillo
*/

if ( ! class_exists( 'FavoritePost' ) ) {
    class FavoritePost {
        private $tableName;
        private $prefixRoute;

        public function __construct() {
            global $wpdb;
            $this->tableName = $wpdb->prefix . 'favorite_post';
            $this->prefixRoute  = 'api/v1';

            register_activation_hook(__FILE__, [$this, 'createTable']);
            add_action('rest_api_init', [$this, 'registerRoutesAPI']);
            add_filter('rest_authentication_errors', [$this, 'checkTokenAuth']);
        }

        public function createTable() {
            global $wpdb;
            $charsetCollate = $wpdb->get_charset_collate();

            $sqlCreate = "CREATE TABLE IF NOT EXISTS $this->tableName (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) NOT NULL,
                post_id BIGINT(20) NOT NULL,
                active TINYINT(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sqlCreate);
        }

        public function registerRoutesAPI() {
            register_rest_route($this->prefixRoute, '/favoritePosts/', [
                'methods' => 'GET',
                'callback' => [$this, 'getFavoritePosts'],
                'permission_callback' => '__return_true'
            ]);
            register_rest_route($this->prefixRoute, '/favoritePosts/(?P<postId>\d+)', [
                'methods' => 'GET',
                'callback' => [$this, 'getFavoritePost'],
                'permission_callback' => '__return_true'
            ]);
            register_rest_route($this->prefixRoute, '/favoritePosts/', [
                'methods' => 'POST',
                'callback' => [$this, 'createFavoritePost'],
                'permission_callback' => [$this, 'checkPermissions']
            ]);
            register_rest_route($this->prefixRoute, '/favoritePosts/(?P<postId>\d+)', [
                'methods' => 'DELETE',
                'callback' => [$this, 'statusFavoritePost'],
                'permission_callback' => [$this, 'checkPermissions']
            ]);
            register_rest_route($this->prefixRoute, '/login/', [
                'methods' => 'POST',
                'callback' => [$this, 'login']
            ]);
        }

        public function getFavoritePosts(WP_REST_Request $request) {
            global $wpdb;
            $sqlPrepare = $wpdb->prepare("SELECT * FROM $this->tableName");
            $entries    = $wpdb->get_results($sqlPrepare);

            return rest_ensure_response($entries);
        }

        public function getFavoritePost(WP_REST_Request $request) {
            global $wpdb;
            $postId = $request['postId'];
            $sqlPrepare = $wpdb->prepare("SELECT * FROM $this->tableName WHERE post_id = %d", $postId);
            $entry      = $wpdb->get_row($sqlPrepare);

            if (empty($entry)) {
                return new WP_Error('no_entry', 'Post favorito não encontrada', ['status' => 404]);
            }

            return rest_ensure_response($entry);
        }

        public function createFavoritePost(WP_REST_Request $request) {
            try {
                global $wpdb;
                $userId = get_current_user_id();
                $postId = absint($request->get_param('postId'));
                $active = !empty($request->get_param('active')) ? $request->get_param('active') : 1;
                $validPostId = $this->processValidate("required|number", $postId, 'postId');
                if (!$validPostId['valid']) {
                    return new WP_Error('no_entry', $validPostId["message"], ['status' => 400]);
                }

                $findPostFav = $this->getFavoritePost($request);
                if(!is_wp_error($findPostFav)){
                    $requestUpdate = new WP_REST_Request();
                    $requestUpdate->set_method('PATCH');
                    $requestUpdate->set_param('postId', $postId);
                    $responseUpdate = $this->statusFavoritePost($requestUpdate);
                    return rest_ensure_response($responseUpdate);
                }
                $wpdb->insert(
                    $this->tableName,
                    ['user_id' => $userId,
                    'post_id' => $postId,
                    'active' => $active]
                );

                $id = $wpdb->insert_id;
                $prepareSql = $wpdb->prepare("SELECT * FROM $this->tableName WHERE id = %d", $id);
                $entry      = $wpdb->get_row($prepareSql);

                return rest_ensure_response($entry);
            } catch (Exception $e) {
                // Manejar la excepción y devolver un WP_Error
                return new WP_Error('processing_error', $e->getMessage(), ['status' => 500]);
            }
        }

        public function statusFavoritePost(WP_REST_Request $request) {
            global $wpdb;
            $method     = $request->get_method();
            $verbHttp   = ["DELETE" => 0, "PATCH" => 1];
            $actionRqt  = ["DELETE" => "Excluir", "PATCH" => "Ativar"];
            $postId     = $request->get_param('postId');
            $deleted = $wpdb->update(
                $this->tableName,
                [ 'active' => $verbHttp[$method]],
                ['post_id' => $postId]
            );

            if ($deleted === false) {
                return new WP_Error('db_delete_error', "Erro ao {$actionRqt[$method]} o registro", ['status' => 500]);
            }

            return rest_ensure_response(['status' => $actionRqt[$method]]);
        }

        public function login(WP_REST_Request $request) {
            $username = sanitize_user($request->get_param('username'));
            $password = $request->get_param('password');

            $user = wp_authenticate($username, $password);

            if (is_wp_error($user)) {
                return new WP_Error('authentication_error', 'Credenciais inválidas', ['status' => 403]);
            }

            $token = $this->generateToken($user->ID);
            return rest_ensure_response(['token' => $token]);
        }

        private function generateToken($user_id) {
            $issued_at = time();
            $payload = array(
                'iss' => get_bloginfo('url'),
                'iat' => $issued_at,
                'user_id' => $user_id
            );

            $payload_base64 = base64_encode(json_encode($payload));
            return $payload_base64;
        }

        private function decodeToken($token) {
            $decoded = base64_decode($token);
            $payload = json_decode($decoded);

            return $payload;
        }

        public function checkTokenAuth($result) {
            if (!empty($result) || empty($_SERVER['HTTP_AUTHORIZATION'])) {
                return $result;
            }

            $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
            if (empty($token)) {
                return new WP_Error('no_token', 'Token não fornecido', ['status' => 401]);
            }

            try {
                $payload = $this->decodeToken($token);
                if (!$payload || !isset($payload->user_id)) {
                    return new WP_Error('invalid_token', 'Token inválido', ['status' => 401]);
                }

                $user = get_user_by('id', $payload->user_id);
                if (!$user) {
                    return new WP_Error('user_not_found', 'Usuário não encontrado', ['status' => 404]);
                }

                wp_set_current_user($user->ID);
            } catch (Exception $e) {
                return new WP_Error('token_error', 'Erro ao verificar token', ['status' => 401]);
            }

            return $result;
        }

        public function checkPermissions() {
            return current_user_can('edit_posts');
        }

        public function processValidate($arg, $value, $nameVar ) {
            $arrayTypeValidated  = explode("|", $arg);
            foreach ($arrayTypeValidated as $keyType => $type) {
                $validated  = $this->validParameter($value, $type, $nameVar);
                if (!$validated['valid']) {
                    return ["valid" => false, 
                            'type' => explode(":", $type)[0], 
                            'message' => $validated['message']];
                }
            }
            return ["valid" => true, 'type' => '', 'message' => 'valid'];
        }

        public function validParameter($value, $arg, $nameVar) {
            $arrayArgType   = explode(":", $arg); 
            $type           = $arrayArgType[0];
            $args           = isset($arrayArgType[1]) ? $arrayArgType[1] : '';
            switch ($type) {
            case 'number':
                if (!is_numeric($value) || (float)$value <= 0) {
                    return [
                        "valid" => false, 
                        'message' => "O campo '$nameVar' deve ser um Número válido!"
                    ];
                }
                break;
            case 'required':
                if (empty($value) || is_null($value)) {
                    return [
                        "valid" => false, 
                        'message' => "O campo '{$nameVar}' é obrigatório."
                    ];
                }
                break;
            case 'length':
                if (strlen($value) != $args) {
                    return [
                        "valid" => false, 
                        'message' => "O campo '$nameVar' deve ter $args caracteres."
                    ];
                }
                break;
            default:
                return [
                    "valid" => false, 
                    'message' => "Error validação não registrada"
                ];
                break;
            }

            return ["valid" => true, 
                    'message' => "Field validated"];

        }
    }

    new FavoritePost();
}
