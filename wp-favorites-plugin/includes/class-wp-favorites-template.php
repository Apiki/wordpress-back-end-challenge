<?php
/**
 * Template functions for WP Favorites Plugin
 *
 * @package WP_Favorites_Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template functions class
 */
class WP_Favorites_Template {
    
    /**
     * Initialize template functions
     */
    public static function init() {
        add_action('wp_favorites_button', array(__CLASS__, 'favorite_button'));
        add_shortcode('wp_favorites_button', array(__CLASS__, 'favorite_button_shortcode'));
        add_shortcode('wp_favorites_list', array(__CLASS__, 'favorites_list_shortcode'));
    }
    
    /**
     * Display favorite button
     *
     * @param array $args Button arguments
     */
    public static function favorite_button($args = array()) {
        $defaults = array(
            'post_id' => get_the_ID(),
            'show_count' => false,
            'button_text' => __('Favorite', 'wp-favorites-plugin')
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Load template
        $template_path = WP_FAVORITES_PLUGIN_PATH . 'templates/favorite-button.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
    
    /**
     * Favorite button shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public static function favorite_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
            'show_count' => 'false',
            'button_text' => __('Favorite', 'wp-favorites-plugin')
        ), $atts);
        
        $atts['show_count'] = filter_var($atts['show_count'], FILTER_VALIDATE_BOOLEAN);
        
        ob_start();
        self::favorite_button($atts);
        return ob_get_clean();
    }
    
    /**
     * Favorites list shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public static function favorites_list_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your favorites.', 'wp-favorites-plugin') . '</p>';
        }
        
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'limit' => 10,
            'show_excerpt' => 'false',
            'show_date' => 'false'
        ), $atts);
        
        $favorites = WP_Favorites_Helpers::get_user_favorites($atts['user_id']);
        
        if (empty($favorites)) {
            return '<p>' . __('No favorite posts found.', 'wp-favorites-plugin') . '</p>';
        }
        
        // Limit results
        $favorites = array_slice($favorites, 0, (int) $atts['limit']);
        
        $output = '<div class="wp-favorites-list">';
        $output .= '<h3>' . __('Your Favorite Posts', 'wp-favorites-plugin') . '</h3>';
        $output .= '<ul>';
        
        foreach ($favorites as $post_id) {
            $post = get_post($post_id);
            if (!$post) continue;
            
            $output .= '<li>';
            $output .= '<a href="' . get_permalink($post_id) . '">' . get_the_title($post_id) . '</a>';
            
            if (filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN)) {
                $output .= ' <span class="favorite-date">(' . get_the_date('', $post_id) . ')</span>';
            }
            
            if (filter_var($atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN)) {
                $excerpt = wp_trim_words(get_the_excerpt($post_id), 20);
                $output .= '<p class="favorite-excerpt">' . $excerpt . '</p>';
            }
            
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Get template path
     *
     * @param string $template_name Template name
     * @return string
     */
    public static function get_template_path($template_name) {
        return WP_FAVORITES_PLUGIN_PATH . 'templates/' . $template_name . '.php';
    }
    
    /**
     * Load template
     *
     * @param string $template_name Template name
     * @param array $args Template arguments
     */
    public static function load_template($template_name, $args = array()) {
        $template_path = self::get_template_path($template_name);
        
        if (file_exists($template_path)) {
            extract($args);
            include $template_path;
        }
    }
} 