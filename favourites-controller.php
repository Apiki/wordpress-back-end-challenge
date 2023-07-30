<?php
/**
 * Classe FavouritesController
 */
class FavouritesController {

    /**
     * Método para registrar as rotas da API do plugin
     */
    public static function register_routes() {
        register_rest_route(
            'wp-favourites/v1',
            '/favourite/(?P<post_id>\d+)',
            array(
                'methods' => 'POST',
                'callback' => array( 'FavouritesController', 'favourite_post' ),
                'permission_callback' => array( 'FavouritesController', 'check_user_logged_in' ),
            )
        );

        register_rest_route(
            'wp-favourites/v1',
            '/unfavourite/(?P<post_id>\d+)',
            array(
                'methods' => 'POST',
                'callback' => array( 'FavouritesController', 'unfavourite_post' ),
                'permission_callback' => array( 'FavouritesController', 'check_user_logged_in' ),
            )
        );
    }

    /**
     * Método para verificar se o usuário está logado
     */
    public static function check_user_logged_in() {
        if ( is_user_logged_in() ) {
            return true;
        } else {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You are not authorized to access this endpoint.', 'wp-favourites' ), array( 'status' => 401 ) );
        }
    }

    /**
     * Método para favoritar um post
     */
    public static function favourite_post( $request ) {
        $user_id = get_current_user_id();
        $post_id = $request['post_id'];

        // Verificar se o post já está favoritado pelo usuário
        if ( self::is_post_favourited( $user_id, $post_id ) ) {
            return new WP_Error( 'post_already_favourited', esc_html__( 'This post is already favourited by the user.', 'wp-favourites' ), array( 'status' => 400 ) );
        }

        // Adicionar o post aos favoritos do usuário na tabela personalizada
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_favourites';
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
            )
        );

        return new WP_REST_Response( array( 'message' => 'Post favourited successfully.' ), 200 );
    }

    /**
     * Método para desfavoritar um post
     */
    public static function unfavourite_post( $request ) {
        $user_id = get_current_user_id();
        $post_id = $request['post_id'];

        // Verificar se o post está favoritado pelo usuário
        if ( ! self::is_post_favourited( $user_id, $post_id ) ) {
            return new WP_Error( 'post_not_favourited', esc_html__( 'This post is not favourited by the user.', 'wp-favourites' ), array( 'status' => 400 ) );
        }

        // Remover o post dos favoritos do usuário na tabela personalizada
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_favourites';
        $wpdb->delete(
            $table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
            )
        );

        return new WP_REST_Response( array( 'message' => 'Post unfavourited successfully.' ), 200 );
    }

    /**
     * Método para verificar se um post está favoritado pelo usuário
     */
    private static function is_post_favourited( $user_id, $post_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_favourites';
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND post_id = %d",
                $user_id,
                $post_id
            )
        );

        return (int) $result > 0;
    }
}