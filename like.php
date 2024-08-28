<?php
/*
Plugin Name: Favoritar posts
Description: Favoritar Post Wordpress para usuarios logados
Version: 1.0
Author: Guilherme Pontes
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

    // Passar o nonce e outros parâmetros para o JavaScript.
    $paths = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'imgUrl' => $plugin_dir . "img/",
        'restNonce' => wp_create_nonce('wp_rest') // Gera o nonce para REST API.
    );
    wp_localize_script('like-js', 'myAjax', $paths);
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');

require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
