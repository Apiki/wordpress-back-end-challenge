<?php

declare(strict_types=1);

namespace Apiki\Favorites\Domain;

use RuntimeException;

defined('ABSPATH') || exit;

/**
 * Domain-specific exception for favorite-related business rule violations.
 *
 * Carries the appropriate HTTP status code in getCode(), so the REST
 * controller can map it directly to a WP_Error response without having
 * to inspect the message.
 *
 * Using a dedicated exception class (rather than a generic RuntimeException)
 * lets the controller `catch (FavoriteException $e)` and trust that it's
 * a known business case — never accidentally swallowing an unrelated
 * runtime error.
 */
final class FavoriteException extends RuntimeException
{
    public static function already_exists(int $user_id, int $post_id): self
    {
        return new self(
            sprintf('User %d has already favorited post %d.', $user_id, $post_id),
            409
        );
    }

    public static function not_found(int $user_id, int $post_id): self
    {
        return new self(
            sprintf('Favorite not found for user %d on post %d.', $user_id, $post_id),
            404
        );
    }

    public static function post_not_published(int $post_id): self
    {
        return new self(
            sprintf('Post %d does not exist or is not published.', $post_id),
            404
        );
    }
}
