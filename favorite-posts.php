<?php

/**
 * @package FavoritePosts
 * Plugin Name: Favorite Posts
 * Description: A useful and straightforward plugin that allows users of your website to favorite posts.
 * Version: 1.0.0
 * Author: Henrique Ferreira
 * License: GPLv2
*/

if (!defined('ABSPATH') || !function_exists('add_action')) {
    die();
}

require_once plugin_dir_path(__FILE__) . 'FavoritePostsPlugin.php';

$plugin = new FavoritePostsPlugin();

register_activation_hook(__FILE__, [$plugin, 'activate']);
register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);

// Inserts or Deletes a favorite
add_action('rest_api_init', function () use ($plugin) {
    register_rest_route(
        'wp/v2',
        '/posts/(?P<id>[\d]+)/favorite', [
            'methods' => 'POST',
            'callback' => [$plugin, 'favorite']
        ]
    );
});

// Gets all users that favorited a post
add_action('rest_api_init', function () use ($plugin) {
    register_rest_route(
        'wp/v2',
        '/posts/(?P<id>[\d]+)/favorite', [
            'methods' => 'GET',
            'callback' => [$plugin, 'favorites']
        ]
    );
});