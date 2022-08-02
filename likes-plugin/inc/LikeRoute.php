<?php

namespace inc;

/**
 * Class LikeRoute
 *
 * @category Api Route
 * @package  Back-end
 * @author   Nick Granados <internickbr@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/internick2017/wordpress-back-end-challenge
 */
class LikeRoute
{
    /**
     * Função constructor init api
     *
     *
     */
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'postLikePluginRoute'));
    }

    /**
     * Registrar rota and methods da api
     *
     * @return void
     */
    public function postLikePluginRoute(): void
    {
        register_rest_route('likeuri/v1', 'manageLike', array(
            'methods' => 'POST',
            'callback' => array($this, 'createLike')
        ));
        register_rest_route('likeuri/v1', 'manageLike', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'deleteLike')
        ));
    }

    /**
     * Função que cria o like
     *
     * @param $data
     * @return int|void
     */
    public function createLike($data)
    {
        if (is_user_logged_in()) {
            global $wpdb;
            $tableName = $wpdb->prefix . "likes";
            $user = sanitize_text_field($data['user']);
            $post = sanitize_text_field($data['post']);
            $like = array(
                'post_id' => $post,
                'user_id' => $user,
            );
            $wpdb->insert($tableName, $like);
            return $wpdb->insert_id;
        }

        die('Só usuário logado pode curtir a postagem');
    }

    /**
     * Função que exclui o like
     *
     * @param $data
     * @return string|void
     */
    public function deleteLike($data)
    {
        global $wpdb;
        $like = sanitize_text_field($data['like']);
        $userID = get_current_user_id();
        $post = sanitize_text_field($data['post']);
        $tableName = $wpdb->prefix . "likes";
        $result = $wpdb->get_results("SELECT * FROM $tableName WHERE user_id = $userID AND post_id = $post");
        if (is_user_logged_in() && get_current_user_id() == $result[0]->user_id) {
            $wpdb->delete($tableName, array('id' => $like));
            return 'like apagado';
        }
        die('Só usuário logado pode curtir a postagem');
    }
}



