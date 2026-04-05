<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Favorite posts functionality for logged-in users via WP REST API.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Author: Hahuma <firminopetterson@gmail.com>
 * License: MIT
 * Text Domain: apiki-favorites
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

ApikiFavorites\Plugin::instance()->boot();
