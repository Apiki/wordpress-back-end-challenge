<?php

declare(strict_types=1);

namespace ApikiFavorites;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

final class RestController
{
    private const NAMESPACE = 'apiki-favorites/v1';
    private const ROUTE = '/favorites';

    public static function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'getFavorites'],
                'permission_callback' => [self::class, 'checkPermission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [self::class, 'addFavorite'],
                'permission_callback' => [self::class, 'checkPermission'],
                'args' => self::postIdArgs(),
            ],
            [
                'methods' => 'DELETE',
                'callback' => [self::class, 'removeFavorite'],
                'permission_callback' => [self::class, 'checkPermission'],
                'args' => self::postIdArgs(),
            ],
        ]);
    }

    public static function checkPermission(): bool|WP_Error
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_unauthorized',
                __('You must be logged in.', 'apiki-favorites'),
                ['status' => 401]
            );
        }

        return true;
    }

    public static function getFavorites(WP_REST_Request $request): WP_REST_Response
    {
        $favorites = Database::getFavorites(get_current_user_id());

        return new WP_REST_Response($favorites, 200);
    }

    public static function addFavorite(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $postId = absint($request->get_param('post_id'));
        $userId = get_current_user_id();

        if (get_post($postId) === null) {
            return new WP_Error(
                'rest_not_found',
                __('Post not found.', 'apiki-favorites'),
                ['status' => 404]
            );
        }

        $insertId = Database::addFavorite($userId, $postId);

        if ($insertId === false) {
            return new WP_Error(
                'rest_conflict',
                __('Post already favorited.', 'apiki-favorites'),
                ['status' => 409]
            );
        }

        return new WP_REST_Response([
            'id' => $insertId,
            'post_id' => $postId,
            'created_at' => current_time('mysql'),
        ], 201);
    }

    public static function removeFavorite(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $postId = absint($request->get_param('post_id'));
        $userId = get_current_user_id();

        $deleted = Database::removeFavorite($userId, $postId);

        if (!$deleted) {
            return new WP_Error(
                'rest_not_found',
                __('Favorite not found.', 'apiki-favorites'),
                ['status' => 404]
            );
        }

        return new WP_REST_Response(['deleted' => true], 200);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function postIdArgs(): array
    {
        return [
            'post_id' => [
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'validate_callback' => static function (mixed $value): bool {
                    return is_numeric($value) && (int) $value > 0;
                },
            ],
        ];
    }
}
