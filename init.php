<?php
/*------------------------------------------------------------------------------------------------------------------
Plugin Name: Apki Bookmark Posts
Description: Plugin para favoritar posts através da WP Rest API.
Version: 1.0.0
Author: Daniel Ferraz Ramos
Author URI: https://art2web.com.br
---------------------------------------------------------------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('BOOKMARKPOSTS_PLUGIN_URL', plugins_url('', __FILE__));
define('BOOKMARKPOSTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once( BOOKMARKPOSTS_PLUGIN_DIR . '/inc/load-db.php');
register_activation_hook(__FILE__, 'bookmarkposts_install');

function get_current_userid() {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		return $current_user->ID;
	} else {
		return false;
	}
}
add_action('init', 'get_current_userid');

function load_plugin_bookmarkposts() {
	define('BOOKMARKPOSTS_CURRENT_USERID', get_current_userid());
	if(BOOKMARKPOSTS_CURRENT_USERID !== false) {
		global $wpdb;
		define('BOOKMARKPOSTS_TABLE', $wpdb->prefix.'bookmarkposts');

		require_once( BOOKMARKPOSTS_PLUGIN_DIR . '/inc/load-plugin.php');
		require_once( BOOKMARKPOSTS_PLUGIN_DIR . '/inc/load-assets.php');
		require_once( BOOKMARKPOSTS_PLUGIN_DIR . '/inc/load-api.php');
	}
}
add_action('plugins_loaded', 'load_plugin_bookmarkposts');
