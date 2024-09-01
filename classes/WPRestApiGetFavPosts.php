<?php
/**
 * Arquivo da classe WPRestApiGetFavPosts
 * 
 * PHP version 7.4
 * 
 * @category WPRestApiGetFavPosts
 * @package  WPPluginFavoritarPosts
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */

namespace App;

/**
 * Classe de listar posts favoritos
 * 
 * PHP version 7.4
 * 
 * @category Class
 * @package  WPRestApiGetFavPosts
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */
class WPRestApiGetFavPosts
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
            '/listar-posts',
            array(
                'methods' => 'GET',
                'callback' => [ $this, 'get_fav_posts' ],
            )
        );
    }

    /**
     * Função para listar os posts na WP Rest API
     *
     * @return array Retorna os posts
     */
    public function get_fav_posts() {
        global $wpdb;

        $user_ID = get_current_user_id();
        $table_name = $wpdb->prefix . 'favoritar_posts';
        $query = $wpdb->get_results( "SELECT * FROM $table_name WHERE user_id = $user_ID" );
        
        if( !$query ) {
            return __( 'Nenhum post favoritado.' );
        }

        $post_ids = [];
        foreach ( $query as $value ) {
            $post_ids[] = $value->post_id;
        }

        $args = array(
            'post_type' => array( 'post' ),
            'orderby' => 'DESC',
            'post__in' => $post_ids
        );

        $posts = get_posts( $args );
        return $posts;
    }

}

$WPRestApiGetFavPosts = new WPRestApiGetFavPosts();
