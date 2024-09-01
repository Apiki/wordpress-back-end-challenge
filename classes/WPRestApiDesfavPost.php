<?php
/**
 * Arquivo da classe WPRestApiDesfavPost
 * 
 * PHP version 7.4
 * 
 * @category WPRestApiDesfavPost
 * @package  WPPluginFavoritarPosts
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */

namespace App;

/**
 * Classe de desfavoritar o post na WP Rest API
 * 
 * PHP version 7.4
 * 
 * @category Class
 * @package  WPRestApiDesfavPost
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */
class WPRestApiDesfavPost
{

    /**
     * Construct
     */
    public function __construct()
    {
        /**
         * Hooks
         */
        add_action( 'rest_api_init', [ $this, 'create_custon_endpoint' ] );
    }

    /**
     * Função de criação do endpoint
     *
     * @return null Não retorna nada
     */
    public function create_custon_endpoint(){
        register_rest_route(
            'plugin-fav-post',
            '/desfavoritar-post',
            array(
                'methods' => 'POST',
                'callback' => [ $this, 'desfav_post' ],
            )
        );
    }

    /**
     * Função de desfavoritar e retornar na WP Rest API
     *
     * @return string Retorna sucesso ou falha
     */
    public function desfav_post() {
        $get_post = get_post( $_POST[ 'post_id' ] );
        if( $get_post ) {

            global $wpdb;

            $table_name = $wpdb->prefix . 'favoritar_posts';
            $user_ID = get_current_user_id();

            $wpdb->delete($table_name, array(
                "post_id" => $_POST[ 'post_id' ],
                "user_id" => $user_ID,
             ));

            return __( 'Post defavoritado com sucesso!' );

        } else {
            return __( 'Post ID não existe.' );
        }
    }

}

$WPRestApiDesfavPost = new WPRestApiDesfavPost();
