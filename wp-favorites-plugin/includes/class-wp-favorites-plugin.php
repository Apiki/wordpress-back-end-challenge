<?php
/**
 * Main plugin class
 *
 * @package WP_Favorites_Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
class WP_Favorites_Plugin {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        register_activation_hook(WP_FAVORITES_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WP_FAVORITES_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        $this->create_table();
        $this->register_rest_routes();
        $this->init_assets();
        $this->init_templates();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_table();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
    
    /**
     * Create custom table
     */
    private function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'user_favorites';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Verify table was created
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            error_log('WP Favorites Plugin: Failed to create table ' . $table_name);
        }
    }
    
    /**
     * Check if table exists and create if not
     */
    private function ensure_table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            $this->create_table();
        }
    }
    
    /**
     * Register REST API routes
     */
    private function register_rest_routes() {
        add_action('rest_api_init', function () {
            register_rest_route('wp-favorites/v1', '/favorite', array(
                'methods' => 'POST',
                'callback' => array($this, 'favorite_post'),
                'permission_callback' => array($this, 'check_user_logged_in'),
            ));
            
            register_rest_route('wp-favorites/v1', '/unfavorite', array(
                'methods' => 'POST',
                'callback' => array($this, 'unfavorite_post'),
                'permission_callback' => array($this, 'check_user_logged_in'),
            ));
            
            register_rest_route('wp-favorites/v1', '/favorites', array(
                'methods' => 'GET',
                'callback' => array($this, 'get_user_favorites'),
                'permission_callback' => array($this, 'check_user_logged_in'),
            ));
        });
    }
    
    /**
     * Check if user is logged in
     */
    public function check_user_logged_in() {
        return is_user_logged_in();
    }
    
    /**
     * Favorite a post
     */
    public function favorite_post($request) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('post_id');
        
        if (!$post_id || !get_post($post_id)) {
            return new WP_Error('invalid_post', 'Invalid post ID', array('status' => 400));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        
        // Ensure table exists
        $this->ensure_table_exists();
        
        // Check if already favorited
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ));
        
        if ($existing) {
            return array(
                'success' => true,
                'message' => 'Post already favorited',
                'post_id' => $post_id
            );
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
            ),
            array('%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to favorite post', array('status' => 500));
        }
        
        do_action('wp_favorites_post_favorited', $user_id, $post_id);
        
        return array(
            'success' => true,
            'message' => 'Post favorited successfully',
            'post_id' => $post_id
        );
    }
    
    /**
     * Unfavorite a post
     */
    public function unfavorite_post($request) {
        $user_id = get_current_user_id();
        $post_id = $request->get_param('post_id');
        
        if (!$post_id) {
            return new WP_Error('invalid_post', 'Invalid post ID', array('status' => 400));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        
        // Ensure table exists
        $this->ensure_table_exists();
        
        // Check if already favorited
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ));
        
        if (!$existing) {
            return array(
                'success' => true,
                'message' => 'Post not favorited',
                'post_id' => $post_id
            );
        }
        
        $result = $wpdb->delete(
            $table_name,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
            ),
            array('%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('database_error', 'Failed to unfavorite post', array('status' => 500));
        }
        
        do_action('wp_favorites_post_unfavorited', $user_id, $post_id);
        
        return array(
            'success' => true,
            'message' => 'Post unfavorited successfully',
            'post_id' => $post_id
        );
    }
    
    /**
     * Get user favorites
     */
    public function get_user_favorites($request) {
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favorites';
        
        // Ensure table exists
        $this->ensure_table_exists();
        
        $favorites = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
        
        return array(
            'success' => true,
            'favorites' => $favorites
        );
    }
    
    /**
     * Initialize assets
     */
    private function init_assets() {
        require_once WP_FAVORITES_PLUGIN_PATH . 'includes/class-wp-favorites-assets.php';
        WP_Favorites_Assets::init();
    }
    
    /**
     * Initialize template functions
     */
    private function init_templates() {
        require_once WP_FAVORITES_PLUGIN_PATH . 'includes/class-wp-favorites-helpers.php';
        require_once WP_FAVORITES_PLUGIN_PATH . 'includes/class-wp-favorites-template.php';
        WP_Favorites_Template::init();
    }
} 