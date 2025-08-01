<?php
/**
 * Plugin Name: Apiki Favorites
 * Description: Allows logged-in users to favorite posts.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL-2.0+
 */

defined('ABSPATH') or die('Direct access not allowed');

// Define plugin constants
define('APIKI_FAVORITES_VERSION', '1.0.0');
define('APIKI_FAVORITES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('APIKI_FAVORITES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once APIKI_FAVORITES_PLUGIN_DIR . 'includes/class-database.php';
require_once APIKI_FAVORITES_PLUGIN_DIR . 'includes/class-rest-api.php';

// Initialize the plugin
class Apiki_Favorites_Plugin {
    public function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        new Apiki_Favorites_Database();
        new Apiki_Favorites_REST_API();

        // Add authentication filter
        add_filter('rest_authentication_errors', [$this, 'handle_rest_authentication'], 9);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Add favorite button to posts
        add_filter('the_content', [$this, 'add_favorite_button']);
    }
    
    public function activate() {
        Apiki_Favorites_Database::create_table();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }

    public function handle_rest_authentication($result) {
        if (!empty($result)) {
            return $result;
        }
        
        if (!isset($_SERVER['REQUEST_URI'])) {
            return $result;
        }
        
        if (strpos($_SERVER['REQUEST_URI'], 'apiki-favorites/v1') !== false) {
            return $result;
        }
        
        return $result;
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'apiki-favorites-css',
            APIKI_FAVORITES_PLUGIN_URL . 'assets/css/favorites.css',
            [],
            APIKI_FAVORITES_VERSION
        );

        wp_enqueue_script(
            'apiki-favorites-js',
            APIKI_FAVORITES_PLUGIN_URL . 'assets/js/favorites.js',
            ['jquery'],
            APIKI_FAVORITES_VERSION,
            true
        );

        wp_localize_script('apiki-favorites-js', 'apikiFavorites', [
            'nonce' => wp_create_nonce('wp_rest'),
            'isLoggedIn' => is_user_logged_in()
        ]);
    }

    public function add_favorite_button($content) {
        if (!is_singular() || !is_user_logged_in()) {
            return $content;
        }

        $button = sprintf(
            '<button class="apiki-favorite-btn" data-post-id="%d" aria-pressed="false" title="Add to favorites">♥</button>',
            get_the_ID()
        );

        return $content . $button;
    }
}

new Apiki_Favorites_Plugin();