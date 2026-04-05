<?php

declare(strict_types=1);

namespace ApikiFavorites;

final class Assets
{
    public static function enqueue(): void
    {
        if (!is_singular('post')) {
            return;
        }

        $pluginUrl = plugin_dir_url(dirname(__DIR__) . '/apiki-favorites.php');
        $pluginDir = plugin_dir_path(dirname(__DIR__) . '/apiki-favorites.php');

        wp_enqueue_style(
            'apiki-favorites',
            $pluginUrl . 'dist/index.css',
            [],
            (string) filemtime($pluginDir . 'dist/index.css')
        );

        wp_enqueue_script(
            'apiki-favorites',
            $pluginUrl . 'dist/index.js',
            [],
            (string) filemtime($pluginDir . 'dist/index.js'),
            true
        );

        wp_localize_script('apiki-favorites', 'apikiFavorites', [
            'restUrl' => rest_url('apiki-favorites/v1/favorites'),
            'nonce' => wp_create_nonce('wp_rest'),
            'isLoggedIn' => is_user_logged_in(),
        ]);
    }

    public static function appendButton(string $content): string
    {
        if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        if (!is_user_logged_in()) {
            return $content;
        }

        $postId = get_the_ID();

        if ($postId === false) {
            return $content;
        }

        $isFavorite = Database::isFavorite(get_current_user_id(), $postId);
        $activeClass = $isFavorite ? ' apiki-favorite-btn--active' : '';
        $label = $isFavorite
            ? esc_attr__('Remove from favorites', 'apiki-favorites')
            : esc_attr__('Add to favorites', 'apiki-favorites');

        $button = sprintf(
            '<button class="apiki-favorite-btn%s" data-post-id="%d" aria-label="%s" title="%s">
                <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                        2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
                        C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
                        c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </button>',
            esc_attr($activeClass),
            $postId,
            $label,
            $label
        );

        return $content . $button;
    }
}
