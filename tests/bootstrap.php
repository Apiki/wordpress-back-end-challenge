<?php
/**
 * Bootstrap para testes unitários com PHPUnit.
 * Define mocks das classes, constantes e funções globais do WordPress
 * para testar a lógica do plugin de forma isolada e ultra rápida.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Define constantes fundamentais do WordPress
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

// 1. Mock do Banco de Dados do WordPress (wpdb)
if (!class_exists('wpdb')) {
    class wpdb {
        public $prefix = 'wp_';
        
        public function prepare($query, ...$args) {
            // Simulação simples de preparado de query
            foreach ($args as $arg) {
                $pos = strpos($query, '%d');
                if ($pos !== false) {
                    $query = substr_replace($query, (string)$arg, $pos, 2);
                    continue;
                }
                $pos = strpos($query, '%s');
                if ($pos !== false) {
                    $query = substr_replace($query, "'" . addslashes((string)$arg) . "'", $pos, 2);
                }
            }
            return $query;
        }

        public function insert($table, $data, $format = null) {
            return true;
        }

        public function delete($table, $where, $where_format = null) {
            return true;
        }

        public function get_var($query) {
            return 0;
        }

        public function get_col($query) {
            return [];
        }

        public function query($query) {
            return true;
        }
    }
}

// 2. Mock do WP_Error do WordPress
if (!class_exists('WP_Error')) {
    class WP_Error {
        public $code;
        public $message;
        public $data;

        public function __construct($code = '', $message = '', $data = '') {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message() {
            return $this->message;
        }

        public function get_error_data() {
            return $this->data;
        }
    }
}

// 3. Mock da Resposta REST (WP_REST_Response)
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        public $data;
        public $status;
        public $headers = [];

        public function __construct($data = null, $status = 200, $headers = []) {
            $this->data = $data;
            $this->status = $status;
            $this->headers = $headers;
        }

        public function header($key, $value) {
            $this->headers[$key] = $value;
        }

        public function get_data() {
            return $this->data;
        }

        public function get_status() {
            return $this->status;
        }

        public function get_headers() {
            return $this->headers;
        }
    }
}

// 4. Mock da Requisição REST (WP_REST_Request)
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        private $params = [];

        public function __construct($method = 'GET', $params = []) {
            $this->params = $params;
        }

        public function get_param($key) {
            return isset($this->params[$key]) ? $this->params[$key] : null;
        }

        public function set_param($key, $value) {
            $this->params[$key] = $value;
        }
    }
}

// 5. Mock do Servidor REST (WP_REST_Server)
if (!class_exists('WP_REST_Server')) {
    class WP_REST_Server {
        const READABLE = 'GET';
        const CREATABLE = 'POST';
        const DELETABLE = 'DELETE';
    }
}

// 6. Mock do Post do WordPress (WP_Post)
if (!class_exists('WP_Post')) {
    class WP_Post {
        public $ID;
        public $post_status = 'publish';
        public $post_type = 'post';

        public function __construct($data = []) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}

// 7. Funções Globais Mockadas (Permitem testes customizarem retornos usando variáveis no escopo global)
$GLOBALS['wp_mock_actions'] = [];
$GLOBALS['wp_mock_rest_routes'] = [];
$GLOBALS['wp_mock_logged_in'] = true;
$GLOBALS['wp_mock_current_user_id'] = 1;
$GLOBALS['wp_mock_posts'] = [];
$GLOBALS['wp_mock_post_type_viewable'] = true;

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        $GLOBALS['wp_mock_actions'][$hook][] = $callback;
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        return true;
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        return true;
    }
}

if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = [], $override = false) {
        $GLOBALS['wp_mock_rest_routes']["$namespace/$route"] = $args;
        return true;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return $GLOBALS['wp_mock_logged_in'];
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return $GLOBALS['wp_mock_current_user_id'];
    }
}

if (!function_exists('get_post')) {
    function get_post($post_id) {
        if (isset($GLOBALS['wp_mock_posts'][$post_id])) {
            return $GLOBALS['wp_mock_posts'][$post_id];
        }
        return null;
    }
}

if (!function_exists('is_post_type_viewable')) {
    function is_post_type_viewable($post_type) {
        return $GLOBALS['wp_mock_post_type_viewable'];
    }
}

if (!function_exists('current_time')) {
    function current_time($type) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('absint')) {
    function absint($value) {
        return max(0, intval($value));
    }
}

if (!function_exists('rest_ensure_response')) {
    function rest_ensure_response($response) {
        if ($response instanceof WP_REST_Response) {
            return $response;
        }
        return new WP_REST_Response($response);
    }
}
