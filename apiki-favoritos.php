<?php
/**
 * Plugin Name: Apiki Favoritos
 * Description: Permite que usuários logados favoritem e desfavoritem posts via WP REST API.
 * Version:     1.0.0
 * Author:      David Lucas 
 * License:     MIT
 */

declare(strict_types=1);

require_once plugin_dir_path(__FILE__) . 'src/class-tabela.php';
require_once plugin_dir_path(__FILE__) . 'src/class-favoritos.php';

register_activation_hook(__FILE__, ['Tabela', 'criar']);

add_action('rest_api_init', function () {
    $favoritos = new Favoritos();
    $favoritos->registrarRotas();
});
