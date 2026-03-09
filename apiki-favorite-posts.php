<?php
/**
 * Plugin Name: Apiki Favorite Posts
 * Description: Plugin para favoritar posts usando WP REST API
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Apiki_Favorite_Posts {

    private $table_name;

    public function __construct() {

        global $wpdb;

        $this->table_name = $wpdb->prefix . 'favorite_posts';

        register_activation_hook(__FILE__, array($this, 'create_table'));

        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function create_table() {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            post_id BIGINT(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

    public function register_routes() {

        register_rest_route('apiki/v1', '/favorite', array(
            'methods'  => 'POST',
            'callback' => array($this, 'toggle_favorite'),
            'permission_callback' => function () {
            return is_user_logged_in();
            }
        ));
    }

    public function toggle_favorite($request) {

        global $wpdb;

        $user_id = get_current_user_id();
        $post_id = intval($request['post_id']);

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );

        if ($exists) {

            $wpdb->delete(
                $this->table_name,
                array(
                    'user_id' => $user_id,
                    'post_id' => $post_id
                )
            );

            return array(
                'message' => 'Post desfavoritado'
            );
        }

        $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id
            )
        );

        return array(
            'message' => 'Post favoritado'
        );
    }
}

new Apiki_Favorite_Posts();