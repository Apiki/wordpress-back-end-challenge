<?php
/**
 * Arquivo da classe WPRestApiFavPost
 * 
 * PHP version 7.4
 * 
 * @category WPRestApiFavPost
 * @package  WPPluginFavoritarPosts
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */

namespace App;

/**
 * Classe de favoritar o post na WP Rest API
 * 
 * PHP version 7.4
 * 
 * @category Class
 * @package  WPRestApiFavPost
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */
class WPRestApiFavPost
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
            '/favoritar-post',
            array(
                'methods' => 'POST',
                'callback' => [ $this, 'fav_post' ],
            )
        );
    }

    /**
     * Função de favoritar e retornar na WP Rest API
     *
     * @return string Retorna sucesso ou falha
     */
    public function fav_post() {
        $get_post = get_post( $_POST[ 'post_id' ] );
        if( $get_post ) {

            global $wpdb;

            $table_name = $wpdb->prefix . 'favoritar_posts';
            $user_ID = get_current_user_id();

            $wpdb->insert($table_name, array(
                "post_id" => $_POST[ 'post_id' ],
                "user_id" => $user_ID,
             ));

            return __( 'Post favoritado com sucesso!' );

        } else {
            return __( 'Post ID não existe.' );
        }
    }

}

$WPRestApiFavPost = new WPRestApiFavPost();
