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

        // Lógica para favoritar o post e persistir os dados na tabela personalizada
        // ...

        return new WP_REST_Response( array( 'message' => 'Post favourited successfully.' ), 200 );
    }

    /**
     * Método para desfavoritar um post
     */
    public static function unfavourite_post( $request ) {
        $user_id = get_current_user_id();
        $post_id = $request['post_id'];

        // Lógica para desfavoritar o post e remover os dados da tabela personalizada
        // ...

        return new WP_REST_Response( array( 'message' => 'Post unfavourited successfully.' ), 200 );
    }
}