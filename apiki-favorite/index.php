<?php
/*
	Plugin Name: Apiki Favorite
	Description: Usuários logados poderão favoritar os posts.
	Version: 1.0
	Author: Gabriel Palhares
	Text Domain: apiki_favorite
*/

if (!function_exists('add_action')) {
	echo "Opa! Eu sou só um plugin, não posso ser chamado diretamente!";
	exit;
}

// Setup
define('AF_PLUGIN_URL', __FILE__);


include('inc/activate.php');
register_activation_hook(AF_PLUGIN_URL, 'af_activate');

include('inc/load-scripts.php');
add_action('wp_enqueue_scripts', 'af_enqueue_scripts', 100);

include('inc/insert-button.php');
add_filter('the_content', 'af_insert_button');

include('inc/api.php');
include('inc/api-endpoints.php');
add_action('rest_api_init', 'set_endpoints');

// Shortcodes