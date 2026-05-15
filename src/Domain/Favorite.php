<?php

declare(strict_types=1);

namespace Apiki\Favorites\Domain;

defined('ABSPATH') || exit;

/**
 * Immutable value object representing a single favorite record.
 *
 * Readonly properties guarantee that once a Favorite is built — typically
 * by the repository when reading from the DB — nothing further down the
 * call stack can mutate it. The same pattern used in the back-end PHP
 * challenge (ExchangeInput): a typed VO is safer to pass between layers
 * than a loose associative array.
 */
final class Favorite
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly int $post_id,
        public readonly string $created_at,
    ) {
    }

    /**
     * Builds a Favorite from a $wpdb row (object or assoc array).
     *
     * Centralizes the row-to-object conversion in one place — the
     * repository never has to remember the column names individually.
     *
     * @param object|array<string, mixed> $row
     */
    public static function from_row(object|array $row): self
    {
        $data = (array) $row;

        return new self(
            (int) $data['id'],
            (int) $data['user_id'],
            (int) $data['post_id'],
            (string) $data['created_at'],
        );
    }

    /**
     * Returns the public REST representation.
     *
     * @return array{id: int, user_id: int, post_id: int, created_at: string}
     */
    public function to_array(): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'post_id'    => $this->post_id,
            'created_at' => $this->created_at,
        ];
    }
}
