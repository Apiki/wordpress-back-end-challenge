<?php
/*
Plugin Name: Plugin de Favoritos
Description: Plugin para permitir que usuários logados favoritem posts.
Version: 1.0
Author: Edgar Filho
*/

class FavoritosPlugin {
    private $db_version;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->db_version = '1.0';
        $this->table_name = $wpdb->prefix . 'favoritos';

        register_activation_hook( __FILE__, array( $this, 'criar_tabela_favoritos' ) );

        add_action( 'rest_api_init', array( $this, 'registrar_rotas_favoritos' ) );
    }

    public function criar_tabela_favoritos() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (ID),
            UNIQUE KEY favoritos_unicos (post_id, user_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option( 'db_version', $this->db_version );
    }

    public function registrar_rotas_favoritos() {
        register_rest_route( 'favoritos/v1', '/favoritar/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array( $this, 'callback_favoritar_post' ),
            'permission_callback' => array( $this, 'verificar_jwt_token' )
        ));

        register_rest_route( 'favoritos/v1', '/desfavoritar/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array( $this, 'callback_desfavoritar_post' ),
            'permission_callback' => array( $this, 'verificar_jwt_token' )
        ));
    }

    public function verificar_jwt_token( $request ) {
        $token = null;

        // Pega o token do cabeçalho
        if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
            $headers = getallheaders();
            $auth_header = $headers['Authorization'];
            list( , $token ) = explode( ' ', $auth_header, 2 );
        }

        if ( ! $token ) {
            $error_data = array( 'status' => 401 );
            $error = new stdClass();
            $error->code = 'jwt_auth_no_token';
            $error->message = 'Token não fornecido.';
            $error->data = $error_data;
            return $error;
        }

        // Verifica e decodifica o token
        try {
            $secret_key = '12345678'; // Segredo do token

            list( $header, $payload, $signature ) = explode( '.', $token );

            $expectedSignature = base64UrlEncode( hash_hmac( 'sha256', "$header.$payload", $secret_key, true ) );

            if ( $signature !== $expectedSignature ) {
                $error_data = array( 'status' => 401 );
                $error = new stdClass();
                $error->code = 'jwt_auth_invalid_token';
                $error->message = 'Token inválido.';
                $error->data = $error_data;
                return $error;
                }
            // Decodificação do payload
            $payload = json_decode( base64UrlDecode( $payload ), true );

            // Verificação do payload
            $user_id = $payload['ID'];
            $user_login = $payload['user_login'];

            return $payload;
        } catch ( Exception $e ) {
            $error_data = array( 'status' => 401 );
            $error = new stdClass();
            $error->code = 'jwt_auth_invalid_token';
            $error->message = 'Token inválido.';
            $error->data = $error_data;
            return $error;
        }
    }

    public function base64UrlEncode($data) {
        $base64 = base64_encode($data);
        if ($base64 === false) {
            return false;
        }
        $base64Url = strtr($base64, '+/', '-_');
        return rtrim($base64Url, '=');
    }

    public function base64UrlDecode($base64Url) {
        $base64 = strtr($base64Url, '-_', '+/');
        return base64_decode($base64);
    }

    public function callback_favoritar_post($request) {
        global $wpdb;

        $post_id = $request['id'];
        $user_id = get_current_user_id();

        $wpdb->insert($this->table_name, array(
            'post_id' => $post_id,
            'user_id' => $user_id
        ));

        return 'Post favoritado com sucesso!';
    }

    public function callback_desfavoritar_post($request) {
        global $wpdb;

        $post_id = $request['id'];
        $user_id = get_current_user_id();

        $wpdb->delete($this->table_name, array(
            'post_id' => $post_id,
            'user_id' => $user_id
        ));

        return 'Post desfavoritado com sucesso!';
    }
}

$favoritos_plugin = new FavoritosPlugin();