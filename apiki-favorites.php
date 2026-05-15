<?php

/**
 * Plugin Name:       Apiki Favorites
 * Plugin URI:        https://github.com/apiki/wordpress-back-end-challenge
 * Description:       Allows logged-in users to favorite and unfavorite posts through the WP REST API. Data is persisted in a custom database table.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Lucas Marques
 * License:           MIT
 * Text Domain:       apiki-favorites
 *
 * @package Apiki\Favorites
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Hard fail (with admin notice) if the PHP version is below the minimum
 * required. We use readonly properties and constructor property promotion,
 * which require PHP 8.1+. Activating on an older runtime would result in
 * a fatal parse error elsewhere, so we stop early and tell the user.
 */
if (PHP_VERSION_ID < 80100) {
    add_action('admin_notices', static function (): void {
        $message = sprintf(
            /* translators: %s is the current PHP version */
            esc_html__('Apiki Favorites requires PHP 8.1 or higher. You are running PHP %s.', 'apiki-favorites'),
            esc_html(PHP_VERSION)
        );
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $message already built with esc_html__() above.
            $message
        );
    });
    return;
}

if ( ! file_exists(__DIR__ . '/vendor/autoload.php')) {
    add_action('admin_notices', static function (): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('Apiki Favorites: run "composer install" in the plugin directory.', 'apiki-favorites')
        );
    });
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

use Apiki\Favorites\Activator;
use Apiki\Favorites\Plugin;

register_activation_hook(__FILE__, [Activator::class, 'activate']);

Plugin::boot();
