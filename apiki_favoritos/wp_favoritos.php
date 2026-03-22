<?php
/**
 * Plugin Name: WP Favoritos Completo
 * Description: Sistema de favoritos com API REST, botão automático nos posts e dashboard do usuário
 * Version: 3.1
 */

if (!defined('ABSPATH')) exit;

// Ativa senhas de aplicação
add_filter('wp_is_application_passwords_available', '__return_true');

class WPFavoritosCompleto {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'favoritos';

        // Criar tabela ao ativar
        register_activation_hook(__FILE__, [$this, 'install']);

        // Rotas REST API
        add_action('rest_api_init', [$this, 'routes']);

        // Adiciona botão nos posts
        add_filter('the_content', [$this, 'add_button']);
        add_action('wp_enqueue_scripts', [$this, 'scripts']);

        // Dashboard do usuário
        add_action('admin_menu', [$this, 'dashboard_menu']);
    }

    // Criar tabela no banco
    public function install() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_post (user_id, post_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    // Rotas REST API
    public function routes() {
        register_rest_route('wp-favoritos/v1', '/favoritar', [
            'methods' => 'POST',
            'callback' => [$this, 'favoritar'],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('wp-favoritos/v1', '/desfavoritar', [
            'methods' => 'POST',
            'callback' => [$this, 'desfavoritar'],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('wp-favoritos/v1', '/status', [
            'methods' => 'GET',
            'callback' => [$this, 'status'],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('wp-favoritos/v1', '/list', [
            'methods' => 'GET',
            'callback' => [$this, 'listar'],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);
    }

    // Favoritar
    public function favoritar($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = isset($request['post_id']) ? intval($request['post_id']) : 0;

        if (!$user_id || !$post_id) {
            return new WP_Error('invalid_data', 'Usuário ou post inválido', ['status' => 400]);
        }

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table} WHERE user_id=%d AND post_id=%d",
            $user_id, $post_id
        ));

        if ($exists) return ['favorited' => true, 'message' => 'Já favoritado'];

        $wpdb->insert($this->table, [
            'user_id' => $user_id,
            'post_id' => $post_id
        ]);

        return ['favorited' => true, 'message' => 'Post favoritado!'];
    }

    // Desfavoritar
    public function desfavoritar($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = isset($request['post_id']) ? intval($request['post_id']) : 0;

        if (!$user_id || !$post_id) {
            return new WP_Error('invalid_data', 'Usuário ou post inválido', ['status' => 400]);
        }

        $wpdb->delete($this->table, [
            'user_id' => $user_id,
            'post_id' => $post_id
        ]);

        return ['favorited' => false, 'message' => 'Post desfavoritado!'];
    }

    // Status
    public function status($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = isset($request['post_id']) ? intval($request['post_id']) : 0;

        if (!$user_id || !$post_id) {
            return new WP_Error('invalid_data', 'Usuário ou post inválido', ['status' => 400]);
        }

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table} WHERE user_id=%d AND post_id=%d",
            $user_id, $post_id
        ));

        return ['favorited' => $exists ? true : false];
    }

    // Listar favoritos
    public function listar($request = null) {
        global $wpdb;
        $user_id = get_current_user_id();

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT f.post_id, p.post_title, p.post_date
            FROM {$this->table} f
            LEFT JOIN {$wpdb->posts} p ON p.ID = f.post_id
            WHERE f.user_id = %d
            ORDER BY f.created_at DESC
        ", $user_id));

        return $results;
    }

    // Botão automático nos posts
    public function add_button($content) {
        if (!is_single()) return $content;

        global $post, $wpdb;
        $user_id = get_current_user_id();
        $post_id = $post->ID;

        if (!$user_id) return $content . '<p><em>🔒 Faça login para favoritar</em></p>';

        $favorited = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table} WHERE user_id=%d AND post_id=%d",
            $user_id, $post_id
        )) ? true : false;

        $label = $favorited ? ' Desfavoritar' : ' Favoritar';

        $html = "<div style='margin:20px 0;'>
            <button class='favorito-btn' data-post='{$post_id}'>{$label}</button>
        </div>";

        return $html . $content;
    }

    // JS
    public function scripts() {
        wp_enqueue_script(
            'favoritos-js',
            plugin_dir_url(__FILE__) . 'favoritos.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('favoritos-js', 'wpFavoritos', [
            'url' => rest_url('wp-favoritos/v1'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    // Menu do dashboard
    public function dashboard_menu() {
        add_menu_page(
            'Meus Favoritos',
            'Meus Favoritos',
            'read',
            'meus-favoritos',
            [$this, 'dashboard_page'],
            'dashicons-heart',
            30
        );
    }

    // Página do dashboard
    public function dashboard_page() {
        $favoritos = $this->listar();

        echo '<div class="wrap">';
        echo '<h1>Meus Favoritos</h1>';

        if (!$favoritos) {
            echo '<p>Você ainda não favoritou nenhum post.</p>';
        } else {
            echo '<table class="widefat fixed striped">';
            echo '<thead><tr><th>ID</th><th>Título</th><th>Data</th><th>Ações</th></tr></thead>';
            echo '<tbody>';
            foreach ($favoritos as $fav) {
                $edit_link = get_edit_post_link($fav->post_id);
                $view_link = get_permalink($fav->post_id);
                echo '<tr>';
                echo "<td>{$fav->post_id}</td>";
                echo "<td>{$fav->post_title}</td>";
                echo "<td>{$fav->post_date}</td>";
                echo "<td>
                        <a href='{$view_link}' target='_blank'>Ver</a> | 
                        <a href='{$edit_link}' target='_blank'>Editar</a>
                      </td>";
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }

        echo '</div>';
    }
}

new WPFavoritosCompleto();