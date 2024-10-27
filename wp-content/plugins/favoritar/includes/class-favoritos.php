<?php

class Favoritos {

    public static function init() {
        add_action('init', [__CLASS__, 'create_favoritos_table']);
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function create_favoritos_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'favoritos';
        $charset_collate = $wpdb->get_charset_collate();
    
        // Verifica se a tabela já existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                post_id bigint(20) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
    
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }    

    public static function register_routes() {
        register_rest_route('favoritos/v1', '/add/', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'add_favorito'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);

        register_rest_route('favoritos/v1', '/remove/', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'remove_favorito'],
            'permission_callback' => [__CLASS__, 'check_permissions']
        ]);
    }

    public static function add_favorito($request) {
        $user_id = get_current_user_id();
        $post_id = $request['post_id'];

        if ($user_id && $post_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'favoritos';
            $wpdb->insert($table_name, [
                'user_id' => $user_id,
                'post_id' => $post_id
            ]);

            return new WP_REST_Response('Post favoritado', 200);
        }

        return new WP_REST_Response('Erro ao favoritar post', 400);
    }

    public static function remove_favorito($request) {
        $user_id = get_current_user_id();
        $post_id = $request['post_id'];

        if ($user_id && $post_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'favoritos';
            $wpdb->delete($table_name, [
                'user_id' => $user_id,
                'post_id' => $post_id
            ]);

            return new WP_REST_Response('Post desfavoritado', 200);
        }

        return new WP_REST_Response('Erro ao desfavoritar post', 400);
    }

    public static function check_permissions() {
        return is_user_logged_in();
    }
}

?>
