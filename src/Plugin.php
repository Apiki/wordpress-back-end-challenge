<?php

declare(strict_types=1);

namespace Apiki\Favorites;

use Apiki\Favorites\Repository\FavoritesRepository;
use Apiki\Favorites\REST\FavoritesController;

defined('ABSPATH') || exit;

/**
 * Plugin bootstrap.
 *
 * This is intentionally NOT a Singleton. There's no mutable state to
 * protect — Plugin::boot() is just a stateless composition root that
 * wires dependencies together at the right WordPress lifecycle hook.
 *
 * Singletons in plugins create global state that is hard to mock in tests
 * and impossible to swap at runtime. The equivalent goal (one initialization
 * per request) is already guaranteed by WordPress: this file is loaded once
 * via the plugin loader, and hooks are registered exactly once.
 */
final class Plugin
{
    public const VERSION        = '1.0.0';
    public const REST_NAMESPACE = 'apiki-favorites/v1';
    public const TABLE_NAME     = 'apiki_favorites';
    public const TEXT_DOMAIN    = 'apiki-favorites';

    /**
     * Wires the plugin into WordPress.
     *
     * `global $wpdb` is read exactly ONE TIME, here at the composition root.
     * Every collaborator that needs database access receives the same
     * instance via constructor injection — no repository, controller, or
     * service ever calls `global $wpdb` itself.
     */
    public static function boot(): void
    {
        add_action('rest_api_init', static function (): void {
            global $wpdb;

            $repository = new FavoritesRepository(
                $wpdb,
                $wpdb->prefix . self::TABLE_NAME
            );

            $controller = new FavoritesController($repository);
            $controller->register_routes();
        });
    }

    /**
     * Returns the full table name (with WordPress prefix).
     *
     * Helper for code paths that don't have direct access to $wpdb,
     * such as the uninstall.php file.
     */
    public static function table_name(\wpdb $wpdb): string
    {
        return $wpdb->prefix . self::TABLE_NAME;
    }
}
