<?php
/**
 * Plugin configuration
 *
 * @package WP_Favorites_Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin configuration
return array(
    'version' => '1.0.0',
    'name' => 'WP Favorites Plugin',
    'description' => 'A WordPress plugin that allows logged-in users to favorite and unfavorite posts using WP REST API',
    'author' => 'WordPress Back-end Challenge',
    'license' => 'GPL v2 or later',
    'text_domain' => 'wp-favorites-plugin',
    'domain_path' => '/languages',
    
    // Database
    'table_name' => 'user_favorites',
    
    // REST API
    'rest_namespace' => 'wp-favorites/v1',
    'rest_routes' => array(
        'favorite' => '/favorite',
        'unfavorite' => '/unfavorite',
        'favorites' => '/favorites'
    ),
    
    // Assets
    'assets' => array(
        'css' => array(
            'wp-favorites-style' => 'assets/css/wp-favorites.css'
        ),
        'js' => array(
            'wp-favorites-script' => 'assets/js/wp-favorites.js'
        )
    ),
    
    // Shortcodes
    'shortcodes' => array(
        'wp_favorites_button' => 'Favorite Button',
        'wp_favorites_list' => 'Favorites List'
    ),
    
    // Hooks
    'hooks' => array(
        'wp_favorites_post_favorited' => 'Fired when a post is favorited',
        'wp_favorites_post_unfavorited' => 'Fired when a post is unfavorited'
    ),
    
    // Requirements
    'requirements' => array(
        'php' => '5.6',
        'wordpress' => '4.7',
        'plugins' => array()
    )
); 