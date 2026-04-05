<?php

declare(strict_types=1);

namespace ApikiFavorites;

final class Plugin
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function boot(): void
    {
        register_activation_hook(
            dirname(__DIR__) . '/apiki-favorites.php',
            [Database::class, 'createTable']
        );

        add_action('rest_api_init', [RestController::class, 'registerRoutes']);
        add_action('wp_enqueue_scripts', [Assets::class, 'enqueue']);
        add_filter('the_content', [Assets::class, 'appendButton']);
    }
}
