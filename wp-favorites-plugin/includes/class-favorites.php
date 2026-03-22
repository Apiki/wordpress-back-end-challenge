<?php
/**
 * Regras de negócio: favoritar, desfavoritar e listar posts.
 *
 * @package WPFavoritesPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPFAV_Favorites {

    /**
     * Alterna o estado de favorito de um post para um usuário.
     *
     * @param int $user_id ID do usuário logado.
     * @param int $post_id ID do post a favoritar/desfavoritar.
     *
     * @return WP_REST_Response|WP_Error
     */
    public static function toggle_favorite( int $user_id, int $post_id ) {
        $user_id = absint( $user_id );
        $post_id = absint( $post_id );

        // Valida se o post existe e é do tipo 'post'.
        if ( ! self::post_is_valid( $post_id ) ) {
            return new WP_Error(
                'invalid_post',
                __( 'O post informado não existe ou não é válido.', 'wp-favorites-plugin' ),
                [ 'status' => 404 ]
            );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'favorites';

        // Verifica se o favorito já existe.
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table} WHERE user_id = %d AND post_id = %d LIMIT 1",
                $user_id,
                $post_id
            )
        );

        if ( $exists ) {
            $deleted = $wpdb->delete(
                $table,
                [
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                ],
                [ '%d', '%d' ]
            );

            if ( false === $deleted ) {
                return new WP_Error(
                    'db_error',
                    __( 'Erro ao remover o favorito.', 'wp-favorites-plugin' ),
                    [ 'status' => 500 ]
                );
            }

            return new WP_REST_Response(
                [
                    'status'  => 'removed',
                    'post_id' => $post_id,
                    'message' => __( 'Post removido dos favoritos.', 'wp-favorites-plugin' ),
                ],
                200
            );
        }

        // Insere o novo favorito declarando os formatos de cada coluna.
        $inserted = $wpdb->insert(
            $table,
            [
                'user_id' => $user_id,
                'post_id' => $post_id,
            ],
            [ '%d', '%d' ]
        );

        if ( false === $inserted ) {
            return new WP_Error(
                'db_error',
                __( 'Erro ao salvar o favorito.', 'wp-favorites-plugin' ),
                [ 'status' => 500 ]
            );
        }

        return new WP_REST_Response(
            [
                'status'  => 'added',
                'post_id' => $post_id,
                'message' => __( 'Post adicionado aos favoritos.', 'wp-favorites-plugin' ),
            ],
            201
        );
    }

    /**
     * Retorna todos os posts favoritados por um usuário.
     *
     * @param int $user_id ID do usuário.
     *
     * @return WP_REST_Response
     */
    public static function get_user_favorites( int $user_id ): WP_REST_Response {
        $user_id = absint( $user_id );

        global $wpdb;
        $table = $wpdb->prefix . 'favorites';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, created_at FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            )
        );

        return new WP_REST_Response(
            [
                'total'     => count( $results ),
                'favorites' => $results,
            ],
            200
        );
    }

    /**
     * Verifica se um post_id é válido: existe, é do tipo 'post' e está publicado.
     *
     * @param int $post_id ID do post.
     *
     * @return bool
     */
    private static function post_is_valid( int $post_id ): bool {
        if ( $post_id <= 0 ) {
            return false;
        }

        $post = get_post( $post_id );

        return ( $post instanceof WP_Post && 'post' === $post->post_type && 'publish' === $post->post_status );
    }
}
