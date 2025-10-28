<?php
/*
Plugin Name: Favoritar Posts via REST API
Description: Permite que usuários logados favoritem posts usando a WP REST API.
Version: 1.0
Author: Napoleão Junio Soares Coelho
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-favoritos-repository.php';
require_once __DIR__ . '/includes/class-favoritos-rest-controller.php';
require_once __DIR__ . '/includes/class-favoritar-posts-plugin.php';

Favoritar_Posts_Plugin::init();
register_activation_hook(__FILE__, ['Favoritar_Posts_Plugin', 'activate']);
register_uninstall_hook(__FILE__, ['Favoritar_Posts_Plugin', 'uninstall']);