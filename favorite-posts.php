<?php
/**
 * Plugin Name:       Favorite Posts
 * Plugin URI:        https://apiki.com/desafio-favorite-posts/
 * Description:       Permite que usuários logados favoritem posts utilizando a WP REST API.
 * Version:           1.0.0
 * Author:            Marcelo Tomaz
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       favorite-posts
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-favorite-posts-activator.php';
register_activation_hook( __FILE__, array( 'Favorite_Posts_Activator', 'activate' ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-favorite-posts-deactivator.php';
register_deactivation_hook( __FILE__, array( 'Favorite_Posts_Deactivator', 'deactivate' ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-favorite-posts.php';

function run_favorite_posts() {
    $plugin = new Favorite_Posts();
    $plugin->run();
}
run_favorite_posts();