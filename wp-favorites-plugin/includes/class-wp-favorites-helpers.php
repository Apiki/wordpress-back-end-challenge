<?php
/**
 * Helper functions for WP Favorites Plugin
 *
 * @package WP_Favorites_Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper functions class
 */
class WP_Favorites_Helpers {
    
    /**
     * Check if current post is favorited by current user
     *
     * @param int $post_id Post ID
     * @return bool
     */
    public static function is_post_favorited($post_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        $user_id = get_current_user_id();
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ));
        
        return $result > 0;
    }
    
    /**
     * Get user's favorite posts
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return array
     */
    public static function get_user_favorites($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return array();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        
        $favorites = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
        
        return $favorites;
    }
    
    /**
     * Get favorite count for a post
     *
     * @param int $post_id Post ID
     * @return int
     */
    public static function get_favorite_count($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d",
            $post_id
        ));
        
        return (int) $count;
    }
    
    /**
     * Sanitize post ID
     *
     * @param mixed $post_id
     * @return int|false
     */
    public static function sanitize_post_id($post_id) {
        $post_id = absint($post_id);
        return $post_id > 0 ? $post_id : false;
    }
    
    /**
     * Validate post exists
     *
     * @param int $post_id Post ID
     * @return bool
     */
    public static function post_exists($post_id) {
        return get_post($post_id) !== null;
    }
    
    /**
     * Get REST API base URL
     *
     * @return string
     */
    public static function get_rest_base_url() {
        return rest_url('wp-favorites/v1');
    }
    
    /**
     * Get nonce for AJAX requests
     *
     * @return string
     */
    public static function get_nonce() {
        return wp_create_nonce('wp_rest');
    }
} 