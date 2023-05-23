<?php
/**
 * Plugin Name: Posts Favoritos
 * Description: Adicione Posts como favoritos
 * Version: 1.0
 * Author: Seu Nome
 * License: GPLv2 or later
 */

defined('ABSPATH') || exit;

/**
 * [Description PostsFavoritos]
 */
class PostsFavoritos {
    /**
     * @var string
     */
    private $table_name = 'fav_posts';

    /**
     * @var string
     */
    private $table_name_prefixed = '';
    /**
     * @var string
     */
    public $endpoint = 'fav_posts';
    public function __construct() {
        global $wpdb;
        $this->table_name_prefixed = $wpdb->prefix . $this->table_name;

        // inclui classe para registrar rotas da rest api
        require_once(plugin_dir_path(__FILE__) . 'inc/fav-posts-api-class.php');

        new PostsFavoritosEndpoints( $this );
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    // Método de ativação do plugin
    public function activate() {
        global $wpdb;

        $collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name_prefixed} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users} (ID),
            FOREIGN KEY (post_id) REFERENCES {$wpdb->posts} (ID)
        ) {$collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    /**
     * @param int $post_id
     * @param int $user_id
     * verifica se um post id foi favoritado pelo user id
     * @return boolean
     */
    public function is_post_favorito(int $post_id, int $user_id) {
        global $wpdb;
    
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name_prefixed} WHERE post_id = %d AND user_id = %d",
            $post_id,
            $user_id
        );
        $count = (int) $wpdb->get_var($query);
        
        return ! empty( $count );
    }

    /**
     * @param int $post_id
     * 
     * @return boolean
     */
    public function add_favorito(int $post_id, int $user_id) {
        global $wpdb;

        $data = array(
            'user_id' => $user_id,
            'post_id' => $post_id
        );
        return ! empty( $wpdb->insert($this->table_name_prefixed, $data) );
    }


    /**
     * @param int $post_id
     * @param int $user_id
     * 
     * @return boolean
     */
    public function remove_favorito(int $post_id, int $user_id) {
        if (!$this->is_post_favorito($user_id, $post_id)) {
            return false;
        }
    
        global $wpdb;
        
        $where = array(
            'user_id' => $user_id,
            'post_id' => $post_id
        );
    
        return ! empty( $wpdb->delete($this->table_name_prefixed, $where) );
    }

}
// Inicia o plugin
$posts_favoritos = new PostsFavoritos();