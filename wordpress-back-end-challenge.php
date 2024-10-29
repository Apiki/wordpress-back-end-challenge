<?php

/**
 * Plugin Name: Favoritar Posts
 * Description: Permite que usuários logados possam favoritar e desfavoritar posts.
 * Version: 1.0
 * Author: André Nascimento
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use LikePosts\Controllers\LikePostsController;
$like_posts_controller = new LikePostsController();

