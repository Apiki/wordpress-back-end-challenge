<?php

declare(strict_types=1);

namespace Apiki\Favorites\Repository;

use Apiki\Favorites\Domain\Favorite;
use Apiki\Favorites\Domain\FavoriteException;
use wpdb;

defined('ABSPATH') || exit;

/**
 * Persistence layer for favorites.
 *
 * IMPORTANT DESIGN NOTE
 * ---------------------
 * This class deliberately AVOIDS calling `global $wpdb` inside its methods.
 * `wpdb` is injected exactly once via the constructor and stored as a
 * readonly property. Reasons:
 *
 *   1. Testability — we can pass a mock/fake wpdb in unit tests without
 *      touching the global scope.
 *   2. Single source of truth — the database handle is captured once;
 *      every method uses the same instance.
 *   3. Honesty — the constructor signature truthfully declares "I need
 *      a wpdb to work". Hidden globals lie about dependencies.
 *
 * The table name is also injected, not built inside each method, for the
 * same reasons: explicit > implicit, and prefixes are decided by the
 * composition root.
 */
// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are log text, not HTML output.
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- The SQL is built via \$wpdb->prepare() into a local variable, which the sniffer cannot trace.
final class FavoritesRepository
{
    public function __construct(
        private readonly wpdb $wpdb,
        private readonly string $table,
    ) {
    }

    /**
     * Adds a favorite. Throws if it already exists.
     *
     * The pre-check is convenience for a clean 409 error message. The
     * real protection against duplicates is the UNIQUE INDEX on
     * (user_id, post_id) at the DB level — defense in depth.
     *
     * @throws FavoriteException If the favorite already exists.
     * @throws \RuntimeException If the database insert fails.
     */
    public function add(int $user_id, int $post_id): Favorite
    {
        if ($this->find($user_id, $post_id) !== null) {
            throw FavoriteException::already_exists($user_id, $post_id);
        }

        $created_at = current_time('mysql', true);

        $inserted = $this->wpdb->insert(
            $this->table,
            [
                'user_id'    => $user_id,
                'post_id'    => $post_id,
                'created_at' => $created_at,
            ],
            ['%d', '%d', '%s']
        );

        if ($inserted === false) {
            if (stripos($this->wpdb->last_error, 'duplicate') !== false) {
                throw FavoriteException::already_exists($user_id, $post_id);
            }

            throw new \RuntimeException(
                'Failed to insert favorite: ' . $this->wpdb->last_error
            );
        }

        return new Favorite(
            (int) $this->wpdb->insert_id,
            $user_id,
            $post_id,
            $created_at
        );
    }

    /**
     * Removes a favorite. Throws if it doesn't exist.
     *
     * @throws FavoriteException If the favorite does not exist.
     * @throws \RuntimeException If the database delete fails.
     */
    public function remove(int $user_id, int $post_id): void
    {
        $deleted = $this->wpdb->delete(
            $this->table,
            [
                'user_id' => $user_id,
                'post_id' => $post_id,
            ],
            ['%d', '%d']
        );

        if ($deleted === false) {
            throw new \RuntimeException(
                'Failed to delete favorite: ' . $this->wpdb->last_error
            );
        }

        if ($deleted === 0) {
            throw FavoriteException::not_found($user_id, $post_id);
        }
    }

    /**
     * Returns a single favorite, or null if absent.
     */
    public function find(int $user_id, int $post_id): ?Favorite
    {
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $this->wpdb->prepare(
            "SELECT id, user_id, post_id, created_at FROM {$this->table}
             WHERE user_id = %d AND post_id = %d LIMIT 1",
            $user_id,
            $post_id
        );
        $row = $this->wpdb->get_row($sql);
        // phpcs:enable

        if ($row === null) {
            return null;
        }

        return Favorite::from_row($row);
    }

    /**
     * Returns the user's favorites, most recent first.
     *
     * @return list<Favorite>
     */
    public function list_for_user(int $user_id, int $page, int $per_page): array
    {
        $offset = max(0, ( $page - 1 ) * $per_page);

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $this->wpdb->prepare(
            "SELECT id, user_id, post_id, created_at FROM {$this->table}
             WHERE user_id = %d
             ORDER BY created_at DESC, id DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $per_page,
            $offset
        );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is built via $wpdb->prepare() above.
        $rows = $this->wpdb->get_results($sql);
        if ( ! is_array($rows)) {
            $rows = [];
        }
        // phpcs:enable

        return array_map(
            static fn ($row): Favorite => Favorite::from_row($row),
            $rows
        );
    }

    /**
     * Counts the user's favorites — used for pagination headers.
     */
    public function count_for_user(int $user_id): int
    {
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d",
            $user_id
        );
        // phpcs:enable

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is built via $wpdb->prepare() above.
        return (int) $this->wpdb->get_var($sql);
    }
}
