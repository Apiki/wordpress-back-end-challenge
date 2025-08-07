<?php

/**
 * Classe principal do plugin WordPress Back End Challenge
 * PHP version 8.1
 *
 * @category Wpbackendchallenge
 * @package  Wpbackendchallenge/includes
 * @author   Gustavo Dias <gustavodiasdsc@gmail.com>
 * @license  GPL v2 or later
 * @link     https://github.com/gustavodscruz/wordpress-back-end-challenge
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal do plugin WordPress Back End Challenge
 *
 * @category Wpbackendchallenge
 * 
 * @package WordPress_Back_End_Challenge
 * 
 * @author Gustavo Dias <gustavodiasdsc@gmail.com>
 * 
 * @license http://opensource.org/licenses/MIT MIT
 * 
 * @link https://github.com/gustavodiasdsc/wordpress-back-end-challenge
 */
class WP_Backend_Challenge
{

    /**
     * Instância única da classe
     */
    private static $_instance = null;
    private $_table_name;

    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct()
    {
        global $wpdb;
        $this->_table_name = $wpdb->prefix . 'wp_backend_challenge';
        $this->_initHooks();
    }

    /**
     * Retorna a instância única da classe
     * 
     * @return WP_Backend_Challenge
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Inicializa os hooks do WordPress
     * 
     * @return void
     */
    private function _initHooks()
    {
        add_action('admin_notices', array($this, 'displayAdminNotice'));
        add_filter('the_content', array($this, 'addFavoriteButton'));
        add_action('wp_ajax_wpb_toggle_favorite', array($this, 'toggleFavorite'));
        add_action(
            'wp_ajax_nopriv_wpb_toggle_favorite',
            array($this, 'toggleFavorite')
        );
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'handleAdminActions'));
        add_shortcode('wpb_user_favorites', array($this, 'userFavoritesShortcode'));
    }

    /**
     * Exibe uma notificação na área de administração
     *
     * @return void
     */
    public function displayAdminNotice()
    {
        $show_notice = get_transient('wpb_activation_notice');

        if (!$show_notice) {
            return;
        }

        delete_transient('wpb_activation_notive');

        $message = esc_html__(
            'Plugin WordPress Back End Challenge ativado com sucesso!',
            'wp-backend-challenge'
        );

        echo '<div class="notice notice-success is-dismissible">
            <p>' . $message . '</p>
        </div>';
    }

    /**
     * Cria tabela no mysql
     *
     * @return void
     */
    public static function createTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_backend_challenge';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            post_id bigint(20) UNSIGNED NOT NULL,
            date_favorited datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (user_id, post_id)
        ) $charset_collate;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Adiciona botão de favorito
     *
     * @param string $content Conteúdo em html
     * 
     * @return string $content Conteúdo em html com o botão
     */
    public function addFavoriteButton(string $content)
    {
        if (!(is_singular('post') && is_user_logged_in())) {
            return $content;
        }

        $user_id = get_current_user_id();
        $post_id = get_the_ID();

        $is_favorited = $this->_isPostFavorited($user_id, $post_id);
        $button_text = $is_favorited
            ? __('Desfavoritar', 'wp_backend_challenge')
            : __('Favoritar', 'wp_backend_challenge');
        $button_class = $is_favorited ? 'favorited' :  '';
        $button_html = '<p><a href="#" class="wpb-favorite-button '
            . $button_class
            . '" data-post-id="'
            . $post_id
            . '">'
            . $button_text
            . '</a></p>';
        return $content . $button_html;
    }

    /**
     * Função AJAX para favoritar ou desfavoritar.
     *
     * @return void
     */
    public function toggleFavorite()
    {
        check_ajax_referer('wpb_favorite_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Você precisa estar logado']);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $user_id = get_current_user_id();

        if ($post_id === 0) {
            wp_send_json_error(['message' => 'ID do post inválido']);
        }

        global $wpdb;
        /** 
         * Objeto wpdb;
         * 
         * @var wpdb $wpdb 
         * */

        $is_favorited = $this->_isPostFavorited($user_id, $post_id);

        if ($is_favorited) {
            $result = $wpdb->delete(
                $this->_table_name,
                [
                    'user_id' => $user_id,
                    'post_id' => $post_id
                ]
            );
        } else {
            $result = $wpdb->insert(
                $this->_table_name,
                [
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                    'date_favorited' => current_time('mysql')
                ]
            );
        }

        if ($result !== false) {
            $new_status = !$is_favorited;
            $button_text = $new_status
                ? __('Desfavoritar', 'wp_backend_challenge')
                : __('Favoritar', 'wp_backend_challenge');

            wp_send_json_success(
                [
                    'favorited' => $new_status,
                    'button_text' => $button_text
                ]
            );
        } else {
            wp_send_json_error(['message' => 'Erro ao atualizar favorito']);
        }
    }

    /**
     * Função que verifica pelos ids se o post está favoritado ou não
     *
     * @param string|integer $user_id Id do usuário
     * @param string|integer $post_id Id do post
     * 
     * @return bool retorna verdadeiro se o post estiver favoritado
     */
    private function _isPostFavorited(string|int $user_id, string|int $post_id)
    {
        /**
         * Banco de dados do wordpress
         * 
         * @var wpdb $wpdb
         */
        global $wpdb;
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->_table_name} WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );
        return $count > 0;
    }

    /**
     * Enfileira scripts e estilos
     *
     * @return void
     */
    public function enqueueScripts()
    {
        if (!is_singular('post')) {
            return;
        }

        wp_enqueue_script(
            'wpb-favorites',
            WP_BACKEND_CHALLENGE_PLUGIN_URL . 'assets/js/favorites.js',
            array('jquery'),
            WP_BACKEND_CHALLENGE_VERSION,
            true
        );

        wp_localize_script(
            'wpb-favorites',
            'wpbFavorites',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpb_favorite_nonce')
            )
        );

        wp_enqueue_style(
            'wpb-favorites-style',
            WP_BACKEND_CHALLENGE_PLUGIN_URL . 'assets/css/favorites.css',
            array(),
            WP_BACKEND_CHALLENGE_VERSION
        );
    }

    /**
     * Adiciona menu de administração
     *
     * @return void
     */
    public function addAdminMenu()
    {
        add_menu_page(
            __('Favoritos', 'wp_backend_challenge'),
            __('Favoritos', 'wp_backend_challenge'),
            'manage_options',
            'wpb-favorites',
            array($this, 'adminPage'),
            'dashicons-heart',
            30
        );
    }

    /**
     * Página de administração dos favoritos
     *
     * @return void
     */
    public function adminPage()
    {
        global $wpdb;
        /** 
         * Objeto wpdb;
         * 
         * @var wpdb $wpdb 
         * */

        $favorites = $wpdb->get_results(
            "SELECT f.*, u.display_name, p.post_title 
             FROM {$this->_table_name} f
             LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
             LEFT JOIN {$wpdb->posts} p ON f.post_id = p.ID
             ORDER BY f.date_favorited DESC"
        );

        include WP_BACKEND_CHALLENGE_PLUGIN_PATH . 'admin/favorites-page.php';
    }

    /**
     * Retorna posts favoritos de um usuário
     *
     * @param integer $user_id ID do usuário
     * 
     * @return array Array com os posts favoritos
     */
    public function getUserFavorites(int $user_id)
    {
        global $wpdb;
        /** 
         * Objeto wpdb;
         * 
         * @var wpdb $wpdb 
         * */

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.* FROM {$wpdb->posts} p
                 INNER JOIN {$this->_table_name} f ON p.ID = f.post_id
                 WHERE f.user_id = %d AND p.post_status = 'publish'
                 ORDER BY f.date_favorited DESC",
                $user_id
            )
        );
    }

    /**
     * Conta total de favoritos de um post
     *
     * @param integer $post_id ID do post
     * 
     * @return integer Número de favoritos
     */
    public function getPostFavoritesCount(int $post_id)
    {
        global $wpdb;
        /** 
         * Objeto wpdb;
         * 
         * @var wpdb $wpdb 
         * */

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->_table_name} WHERE post_id = %d",
                $post_id
            )
        );
    }

    /**
     * Manipula ações administrativas
     *
     * @return void
     */
    public function handleAdminActions()
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $page = isset($_GET['page']) ? $_GET['page'] : '';

        if ($page !== 'wpb-favorites' || $action !== 'delete') {
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id === 0) {
            return;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_favorite_' . $id)) {
            wp_die(__('Erro de segurança.', 'wp_backend_challenge'));
        }

        global $wpdb;
        /**
         * Banco de dados WordPress
         * 
         * @var wpdb $wpdb
         */

        $result = $wpdb->delete($this->_table_name, array('id' => $id));

        if ($result !== false) {
            $redirect_url = add_query_arg(
                'deleted',
                '1',
                admin_url('admin.php?page=wpb-favorites')
            );
        } else {
            $redirect_url = add_query_arg(
                'error',
                '1',
                admin_url('admin.php?page=wpb-favorites')
            );
        }

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Shortcode para exibir favoritos do usuário atual
     *
     * @param array $atts Atributos do shortcode
     * 
     * @return string HTML do shortcode
     */
    public function userFavoritesShortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'limit' => 10,
                'show_excerpt' => 'true',
                'show_date' => 'true'
            ),
            $atts,
            'wpb_user_favorites'
        );

        if (!is_user_logged_in()) {
            return '<p>' . __('Você precisa estar logado para ver seus favoritos.', 'wp_backend_challenge') . '</p>';
        }

        $user_id = get_current_user_id();
        $favorites = $this->getUserFavorites($user_id);

        if (empty($favorites)) {
            return '<div class="wpb-user-favorites">
                <h3>' . __('Meus Favoritos', 'wp_backend_challenge') . '</h3>
                <p class="no-favorites">' . __('Você ainda não tem posts favoritos.', 'wp_backend_challenge') . '</p>
            </div>';
        }

        $html = '<div class="wpb-user-favorites">';
        $html .= '<h3>' . __('Meus Favoritos', 'wp_backend_challenge') . '</h3>';
        $html .= '<div class="wpb-favorites-grid">';

        $limit = intval($atts['limit']);
        $count = 0;

        foreach ($favorites as $post) {
            if ($count >= $limit) {
                break;
            }

            $html .= '<div class="wpb-favorite-post">';
            $html .= '<h4><a href="' . get_permalink($post->ID) . '">' . esc_html($post->post_title) . '</a></h4>';

            if ($atts['show_date'] === 'true') {
                $html .= '<p class="post-date">' . get_the_date('', $post->ID) . '</p>';
            }

            if ($atts['show_excerpt'] === 'true') {
                $excerpt = wp_trim_words($post->post_content, 20);
                $html .= '<p class="post-excerpt">' . esc_html($excerpt) . '</p>';
            }

            $html .= '<p><a href="' . get_permalink($post->ID) . '" class="read-more">' . __('Ler mais', 'wp_backend_challenge') . '</a></p>';
            $html .= '</div>';

            $count++;
        }

        $html .= '</div></div>';

        return $html;
    }
}
