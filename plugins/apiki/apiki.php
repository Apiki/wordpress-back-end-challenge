<?php

/**
 * Plugin Name: WordPress Back-end Challenge
 * Version: 0.0.1
 * Plugin URI: 
 * Description: Apiki challenge
 * Author: Wayne Rocha
 * Author URI: 
 *
 * @package WordPress
 * @author Wayne Rocha
 * @since 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Apiki_REST {
    private string $api_route_namespace = 'apiki/v1';
    private $table_name;

    public function __construct($table_name) {
        $this->table_name = $table_name;
    }

    public function register_routes(){

        register_rest_route($this->api_route_namespace, '/favorite_posts',
            [
                'methods' => "POST",
                'callback' => [$this, 'toggle_favorite'],
                'permission_callback' => function () {
                    return is_user_logged_in();
                },
            ],
            true
        );
    }

    public function toggle_favorite($request) {
        $user_id = get_current_user_id();
        $post_id = intval($request->get_param('post_id'));

        if (!get_post($post_id)) {
            return new WP_Error('invalid_post', 'O post não existe.', ['status' => 404]);
        }

        global $wpdb;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ));

        if ($exists) {
            $wpdb->delete($this->table_name, ['user_id' => $user_id, 'post_id' => $post_id]);
            return rest_ensure_response(['status' => 'removed']);
        }

        $result = $wpdb->insert($this->table_name, ['user_id' => $user_id, 'post_id' => $post_id]);

        if ($result === false) {
            return new WP_Error('db_error', __('Erro ao favoritar o post.', 'wp-apiki'), ['status' => 500]);
        }
        return rest_ensure_response(['status' => 'added']);
    }
}

class WP_Apiki_Plugin {
    public string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'apiki_favorites';
    }

    public function create_table() {
        global $wpdb;

	    if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") != $this->table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$this->table_name} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) UNSIGNED NOT NULL,
                post_id BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY user_post (user_id, post_id)
            ) $charset_collate;";
    
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

    }
}

function wp_plugin_init() {
    $apiki_plugin = new WP_Apiki_Plugin();
    $apiki_rest = new WP_Apiki_REST($apiki_plugin->table_name);

    register_activation_hook(__FILE__, [$apiki_plugin, 'create_table']);
    add_action("rest_api_init", [$apiki_rest, "register_routes"]);
}

add_action('plugins_loaded', 'wp_plugin_init');
