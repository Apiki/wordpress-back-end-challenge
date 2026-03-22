<?php
/**
 * Registro e callbacks dos endpoints REST do plugin.
 *
 * @package WPFavoritesPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPFAV_API {

    /**
     * Registra os endpoints da REST API.
     *
     * @return void
     */
    public static function register_routes(): void {
        register_rest_route(
            'wpfav/v1',
            '/toggle',
            [
                'methods'             => WP_REST_Server::CREATABLE, 
                'callback'            => [ self::class, 'toggle' ],
                'permission_callback' => [ self::class, 'user_is_logged_in' ],
                'args'                => [
                    'post_id' => [
                        'required'          => true,
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => static function ( $value ) {
                            return is_numeric( $value ) && (int) $value > 0;
                        },
                        'description'       => __( 'ID do post a favoritar/desfavoritar.', 'wp-favorites-plugin' ),
                    ],
                ],
            ]
        );

        register_rest_route(
            'wpfav/v1',
            '/list',
            [
                'methods'             => WP_REST_Server::READABLE, // 'GET'
                'callback'            => [ self::class, 'get_favorites' ],
                'permission_callback' => [ self::class, 'user_is_logged_in' ],
            ]
        );
    }

    /**
     * Callback do endpoint POST /toggle.
     *
     * @param WP_REST_Request $request Objeto da requisição.
     *
     * @return WP_REST_Response|WP_Error
     */
    public static function toggle( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $post_id = (int) $request->get_param( 'post_id' );

        return WPFAV_Favorites::toggle_favorite( $user_id, $post_id );
    }

    /**
     * Callback do endpoint GET /list.
     *
     * @return WP_REST_Response
     */
    public static function get_favorites(): WP_REST_Response {
        $user_id = get_current_user_id();

        return WPFAV_Favorites::get_user_favorites( $user_id );
    }

    /**
     * Permission callback reutilizável: verifica se o usuário está logado.
     *
     * @return bool|WP_Error
     */
    public static function user_is_logged_in() {
        if ( is_user_logged_in() ) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            __( 'Você precisa estar logado para usar esta funcionalidade.', 'wp-favorites-plugin' ),
            [ 'status' => 401 ]
        );
    }
}
