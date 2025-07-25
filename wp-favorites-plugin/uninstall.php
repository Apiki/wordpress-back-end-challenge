<?php
/**
 * Uninstall WP Favorites Plugin
 *
 * @package WP_Favorites_Plugin
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete custom table
global $wpdb;
$table_name = $wpdb->prefix . 'user_favorites';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
delete_option('wp_favorites_version');
delete_option('wp_favorites_settings');

// Clear any cached data that has been removed
wp_cache_flush(); 