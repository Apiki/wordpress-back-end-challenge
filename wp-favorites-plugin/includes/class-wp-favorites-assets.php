<?php
/**
 * Assets management for WP Favorites Plugin
 *
 * @package WP_Favorites_Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets management class
 */
class WP_Favorites_Assets {
    
    /**
     * Initialize assets
     */
    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public static function enqueue_scripts() {
        // Enqueue CSS
        wp_enqueue_style(
            'wp-favorites-style',
            WP_FAVORITES_PLUGIN_URL . 'assets/css/wp-favorites.css',
            array(),
            WP_FAVORITES_PLUGIN_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'wp-favorites-script',
            WP_FAVORITES_PLUGIN_URL . 'assets/js/wp-favorites.js',
            array('jquery'),
            WP_FAVORITES_PLUGIN_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('wp-favorites-script', 'wpFavorites', array(
            'restUrl' => rest_url('wp-favorites/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'isLoggedIn' => is_user_logged_in(),
            'strings' => array(
                'favorited' => __('Post added to favorites!', 'wp-favorites-plugin'),
                'unfavorited' => __('Post removed from favorites!', 'wp-favorites-plugin'),
                'error' => __('An error occurred. Please try again.', 'wp-favorites-plugin'),
                'loginRequired' => __('Please log in to favorite posts.', 'wp-favorites-plugin')
            )
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public static function enqueue_admin_scripts($hook) {
        // Only enqueue on plugin settings page
        if (strpos($hook, 'wp-favorites') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wp-favorites-admin-style',
            WP_FAVORITES_PLUGIN_URL . 'assets/css/wp-favorites-admin.css',
            array(),
            WP_FAVORITES_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'wp-favorites-admin-script',
            WP_FAVORITES_PLUGIN_URL . 'assets/js/wp-favorites-admin.js',
            array('jquery'),
            WP_FAVORITES_PLUGIN_VERSION,
            true
        );
    }
    
    /**
     * Get asset URL
     *
     * @param string $path Asset path
     * @return string
     */
    public static function get_asset_url($path) {
        return WP_FAVORITES_PLUGIN_URL . 'assets/' . ltrim($path, '/');
    }
    
    /**
     * Get asset path
     *
     * @param string $path Asset path
     * @return string
     */
    public static function get_asset_path($path) {
        return WP_FAVORITES_PLUGIN_PATH . 'assets/' . ltrim($path, '/');
    }
} 