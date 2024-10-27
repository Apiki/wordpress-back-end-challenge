<?php
/*
Plugin Name: Favoritar
Description: Plugin para favoritar posts.
Version: 1.0
Author: Rene Ballesteros Machado Junior
*/

// Include the main class
require_once plugin_dir_path(__FILE__) . 'includes/class-favoritos.php';

// Initialize the plugin
add_action('plugins_loaded', ['Favoritos', 'init']);
?>
