<?php
/**
 * Plugin Name: Favorite Posts
 * Description: Funcionalidade de favoritar posts para usuários logados usando a WP REST API.
 * Version: 1.0
 * Author: Júlio Freitas
 */

require_once(plugin_dir_path(__FILE__) . '/Database.php');
require_once(plugin_dir_path(__FILE__) . '/FavoritePostsAPI.php');
require_once(plugin_dir_path(__FILE__) . '/FavoritePostsMenu.php');

function initializeDatabaseOnActivate() {
    Database::createTable();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'initializeDatabaseOnActivate');

function deleteDatabaseOnUninstall() {
    Database::dropTable();
    flush_rewrite_rules();
}
register_uninstall_hook(__FILE__, 'deleteDatabaseOnUninstall');

new FavoritePostsAPI();
new FavoritePostsMenu();