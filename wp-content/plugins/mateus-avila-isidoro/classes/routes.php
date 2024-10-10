<?php
/**
 * Arquivo da classe InstallWordpressPlugin
 * 
 * PHP version 8.3.7
 * 
 * @category InstallWordpressPlugin
 * @package  Mateus Ávila Isidoro
 * @author   Mateus Ávila Isidoro <mateus@mateusavila.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/mateusavilaisidoro
 */

namespace MateusAvila;

class RoutesWordpressPlugin
{

    /**
     * Construtor da classe.
     *
     */
    public function __construct()
    {
        add_action('send_headers', [$this, 'allow_cors']);
        $this->add_default_routes();
    }

    /**
     * allow CORS
     *
     * @return void
     */
    public function allow_cors()
    {
        header("Access-Control-Allow-Origin: *");
    }
    
    /**
     * Define the routes of the application
     *
     * @return void 
     */
    public function add_default_routes()
    {
        add_action('rest_api_init', function() {
            register_rest_route('api', '/login', array(
                'methods' => 'POST',
                'callback' => [$this, 'login_user']),
            );
            register_rest_route('api', '/logoff', array(
                'methods' => 'POST',
                'callback' => [$this, 'logoff_user']),
            );
            register_rest_route('api', '/favorite', array(
                'methods' => 'POST',
                'callback' => [$this, 'favorite_post'])
            );
        });   
    }

    /**
     * Make WP login
     *
     * @return void process the login in Rest API
     */
    public function login_user()
    {
        $get = file_get_contents('php://input');
        $g = json_decode($get, true);

        if(empty($g['user_login'])) {
            return wp_send_json(array(
            "title" => "Erro!",
            "text" => "É necessário preencher o username"
            ), 422);
        }

        if(empty($g['user_password'])) {
            return wp_send_json(array(
            "title" => "Erro!",
            "text" => "É necessário preencher a senha"
            ), 422);
        }

        $creds = array(
            'user_login' => sanitize_text_field($g['user_login']),
            'user_password' => sanitize_text_field($g['user_password']),
            'remember' => true
        );

        $user = wp_signon($creds, false);

        if ( is_wp_error( $user ) ) {
            return wp_send_json(array(
                'logged' => false,
                "title" => "Erro!",
                "text" => $user->get_error_message()
            ), 422);
        }

        $token = wp_generate_password(32, false);
        update_user_meta($user->ID, 'auth_token', $token);

        return wp_send_json(array(
            'logged' => true,
            'message' => 'Login executado com sucesso!',
            'token' => $token
        ), 200);
    }

    /**
     * Get the Token
     *
     * @return array<int, string>|string|null returns the user token or null
     */
    public function get_the_token()
    {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

        return $token;
    }

    /**
     * Logoff the user
     *
     * @return string with the logoff information
     */
    public function logoff_user()
    {
        $token = $this->get_the_token();

        if ($token) {
            $this->invalidate_token($token);
        }

        wp_logout();
        return wp_send_json(array(
            'logged' => false,
            'message' => 'Logoff executado com sucesso!'
        ), 200);
    }

    /**
     * Favorite/unfavorite a post
     *
     * @return string favorite/unfavorite the post
     */
    public function favorite_post($request)
    {
        $token = $this->get_the_token();
        if (!$token) {
            return wp_send_json(array(
                "title" => "Erro!",
                "text" => 'Token de autenticação não fornecido'
            ), 401);
        }

        $user = $this->get_user_by_token($token);
        if (!$user) {
            return wp_send_json(array(
                "title" => "Erro!",
                "text" => 'Token de autenticação inválido'
            ), 401);
        }

        $get = file_get_contents('php://input');
        $g = json_decode($get, true);

        if(empty($g['post_id']) || !(is_numeric($g['post_id']))) {
            return wp_send_json(array(
              'success' => false,
              "title" => "Erro!",
              "text" => "É necessário enviar a ID do POST"
            ), 422);
        }

        $get_post = get_post($g['post_id']);
        if (!$get_post) {
          return wp_send_json(array(
            'success' => false,
            "title" => "Erro!",
            "text" => "Este post não existe na nossa plataforma"
          ), 422);
        }

        // verificar se existe o registro
        global $wpdb;
        $table = $wpdb->prefix."favorite";
        $user_id = (int) $user->ID;
        $post_id = (int) $g['post_id'];

        // Sanitização adicional
        $user_id = sanitize_key($user_id);
        $post_id = sanitize_key($post_id);

        $results = $wpdb->get_results($wpdb->prepare("SELECT id FROM $table WHERE `post_id`=%d and `user_id`=%d", $post_id, $user_id));

        if ($results) {
            $wpdb->delete($table, array('post_id' => $post_id, 'user_id' => $user_id));
            return wp_send_json(array(
                'success' => true,
                "title" => "Sucesso!",
                "text" => "Você desfavoritou este post"
            ), 200);
        }

        $wpdb->insert($table, array('post_id' => $post_id, 'user_id' => $user_id, 'fav_date' => current_time('mysql')), array('%d', '%d', '%s'));
        return wp_send_json(array(
            'success' => true,
            "title" => "Sucesso!",
            "text" => "Você favoritou este post"
        ), 200);
    }

    /**
     * Invalidate the given token
     *
     * @param string $token The token to invalidate
     * @return void
     */
    private function invalidate_token($token)
    {
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'auth_token' AND meta_value = %s",
            $token
        ));

        if ($user_id) {
            delete_user_meta($user_id, 'auth_token');
        }
    }

     /**
     * Create a valid token
     *
     * @param string $token The token to validate
     * @return void
     */
    private function get_user_by_token($token)
    {
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'auth_token' AND meta_value = %s",
            $token
        ));

        return $user_id ? get_user_by('id', $user_id) : null;
    }
}

$app = new RoutesWordpressPlugin();