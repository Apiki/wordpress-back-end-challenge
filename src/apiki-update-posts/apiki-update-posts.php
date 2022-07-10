<?php

/*
 * Plugin Name: Apiki Update Posts
 * */

class Apiki_Update_Post
{
    public function __construct()
    {
        $this->define_contants();
        $this->define_hooks();
    }

    public function define_contants()
    {
        defined('AUP_PATH') || define('AUP_PATH', plugin_dir_path(__FILE__));
        defined('AUP_URL') || define('AUP_URL', plugin_dir_path(__FILE__));
    }

    public static function define_db_tables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $apiki_table = $wpdb->prefix . 'apiki_posts';
        $posts_table = $wpdb->prefix . 'posts';
        $users_table = $wpdb->prefix . 'users';

        $create_table_sql = "
            CREATE TABLE IF NOT EXISTS $apiki_table (
                id INT PRIMARY KEY AUTO_INCREMENT,
                used_int TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                post_id BIGINT UNSIGNED,
                user_id BIGINT UNSIGNED,
                FOREIGN KEY(post_id) REFERENCES $posts_table(id),
                FOREIGN KEY(user_id) REFERENCES $users_table(id)
            ) $charset_collate;
        ";

        $wpdb->query($create_table_sql);
    }

    public static function remove_db_tables()
    {
        global $wpdb;
        $apiki_table = $wpdb->prefix . 'apiki_posts';
        $drop_table_sql = "
            DROP TABLE IF EXISTS $apiki_table;
        ";

        $wpdb->query($drop_table_sql);
    }

    public function define_hooks()
    {
        add_action('rest_api_init', [$this, 'rest_api_init_callback']);
    }

    public static function activate()
    {
        self::define_db_tables();
    }

    public static function deactivate()
    {
        self::remove_db_tables();
    }

    public static function uninstall()
    {
        self::remove_db_tables();
    }

    public function rest_api_init_callback()
    {
        register_rest_route(
            'apiki',
            '/v1/posts/(?P<postId>\d+)',
            [
                'methods' => 'PUT',
                'callback' => [$this,'apiki_api_update_post_callback'],
            ]
        );
    }

    public function apiki_api_update_post_callback(WP_REST_Request $request)
    {
        $params = $request->get_params();
        $body_json = $request->get_body();
        $response = new WP_REST_Response();

        $reponse_data = [];

        if(empty($params) || !isset($params['postId']) || $params['postId'] == 0) {
            $response->set_status(400);
            $reponse_data['message'] = 'Id requerido';
            $response->set_data($reponse_data);
            return $response;
        }
        if(empty($body_json)) {
            $response->set_status(400);
            $reponse_data['message'] = 'Necessário dados do post';
            $response->set_data($reponse_data);
            return $response;
        }
        if(!is_user_logged_in()) {
            $response->set_status(403);
            $response->set_data("Não logado");
            return $response;
        }

        $body = json_decode($body_json);
        $post_id = $params['postId'];
        $user_id = get_current_user_id();
        $post_data = [];
        $post_data['ID'] = $post_id;
        $post_data['post_title'] = $body->title;
        $post_data['post_content'] = $body->content;
        $post_updated = wp_update_post($post_data);

        if(!$post_updated) {
            $response->set_status(400);
            $reponse_data['message'] = 'Erro na atualização do post';
            $response->set_data($reponse_data);
            return $response;
        }

        global $wpdb;
        $db_table = $wpdb->prefix . 'apiki_posts';
        $wpdb->insert(
            $db_table,
            [
                'post_id' => $post_id,
                'user_id' => $user_id
            ]
        );
        $response->set_status(200);
        $reponse_data['message'] = 'Post atualizado';
        $response->set_data($reponse_data);
        return $response;
    }
}

if(class_exists('Apiki_Update_Post')) {
    register_activation_hook(__FILE__, ['Apiki_Update_Post', 'activate']);
    register_deactivation_hook(__FILE__, ['Apiki_Update_Post', 'deactivate']);
    register_uninstall_hook(__FILE__, ['Apiki_Update_Post', 'uninstall']);


    $apiki_update_post = new Apiki_Update_Post();
}
