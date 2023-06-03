<?php
/*
Plugin Name: Like
Description: Like nos posts
Version: 1.0
Author: André Nascimento
*/

function active_plugin() {
	require_once plugin_dir_path(__FILE__) . 'includes/install.php';
	create_custom_table();
}
register_activation_hook(__FILE__, 'active_plugin');

function enqueue_scripts() {
	$plugin_dir = plugin_dir_url(__FILE__);

	wp_enqueue_script('like-js', $plugin_dir . 'js/script.js', array('jquery'), '1.0', true);
	wp_enqueue_style('like-style', $plugin_dir . 'css/style.css', array(), '1.0');
	// Passando o valor do admin-ajax.php para o JavaScript
	$paths = array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'imgUrl' => plugin_dir_url(dirname(__FILE__)) . "like/img/"
	);
	wp_localize_script('like-js', 'myAjax', $paths);
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');


require_once plugin_dir_path(__FILE__) . 'includes/functions.php';





