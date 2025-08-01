<?php
/**
 * Plugin Name: Post Favorites
 * Description: Permite que usuários logados favoritem posts via REST API.
 * Version: 1.0
 * Author: Guilherme Ramos
 */

if (!defined('ABSPATH')) {
    exit;
}

class PostFavoritesPlugin {
    private static $instance;

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_favorites_table']);
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function create_favorites_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'post_favorites';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_favorite (user_id, post_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function register_routes() {
        register_rest_route('favorites/v1', '/toggle/(?P<post_id>\d+)', [
            'methods'  => 'POST',
            'callback' => [$this, 'toggle_favorite'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('favorites/v1', '/list', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_user_favorites'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public function toggle_favorite($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = (int)$request['post_id'];
        $table = $wpdb->prefix . 'post_favorites';

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND post_id = %d",
            $user_id, $post_id
        ));

        if ($exists) {
            $wpdb->delete($table, ['id' => $exists]);
            return ['favorited' => false];
        } else {
            $wpdb->insert($table, [
                'user_id' => $user_id,
                'post_id' => $post_id,
            ]);
            return ['favorited' => true];
        }
    }

    public function get_user_favorites() {
        global $wpdb;
        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'post_favorites';

        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM $table WHERE user_id = %d",
            $user_id
        ));

        return wp_list_pluck($favorites, 'post_id');
    }
}

PostFavoritesPlugin::get_instance();
