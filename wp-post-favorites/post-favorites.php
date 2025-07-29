<?php
/**
 * Plugin Name: Post Favorites
 * Description: Permite que usuários logados favoritem posts via REST API.
 * Version: 1.0
 * Author: Gustavo Almeida
 */

if (!defined('ABSPATH')) {
    exit;
}

// Inclui funcionalidades do plugin
require_once plugin_dir_path(__FILE__) . 'includes/Install.php';
require_once plugin_dir_path(__FILE__) . 'includes/Routes.php';
require_once plugin_dir_path(__FILE__) . 'includes/FavoriteController.php';

// Ativa o plugin e cria a tabela
function pf_create_favorites_table() {
    Install::run();
}
register_activation_hook(__FILE__, 'pf_create_favorites_table');

// Registra as rotas da REST API
add_action('rest_api_init', ['Routes', 'register']);
