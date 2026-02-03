<?php
/**
 * Plugin Name: Favorite Posts
 * Description: Permite favoritar posts via REST API.
 * Version: 1.0
 * Author: Pedro Marcusso
 */

defined('ABSPATH') || exit;

$includes = plugin_dir_path(__FILE__) . 'includes/';
require_once $includes . 'install.php';
require_once $includes . 'functions.php';
require_once $includes . 'routes.php';

register_activation_hook(__FILE__, 'fp_create_favorites_table');
