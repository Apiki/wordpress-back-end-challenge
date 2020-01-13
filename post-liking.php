<?php
/**
 * Post Liking
 * 
 * @package             post_liking
 * @author              Amstrong Martins
 * @copyright           2019 Amstrong Martins
 * @license             GPL-3.0-or-later
 * 
 * @wordpress-plugin
 * Plugin Name:         Post Liking
 * Description:         Plugin que permite um usuário logado favoritar posts e armazena os dados em uma tabela do BD
 * Version:             1.0.0
 * Requires at least:   5.3.2
 * Requires PHP:        7.3.11
 * Author:              Amstrong Martins
 * License:             GPL v3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 */
require_once( plugin_dir_path(__FILE__).'post-liking-route-controller.php' );

function postliking_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix.'postliking';
    $sql = "CREATE TABLE $table_name (
        id int(10) NOT NULL AUTO_INCREMENT,
        post_id int(10) NOT NULL UNIQUE,
        post_title varchar(55) NOT NULL,
        post_url varchar(255) DEFAULT '' NOT NULL,
        time_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH.'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function postliking_uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix.'postliking';
    $sql = "DROP TABLE IF EXISTS $table_name;";

    $wpdb->query($sql);
}

function post_liking_register_routes() {
    $controller = new Post_Liking_Route_Controller();
    $controller->register_routes();
}

register_activation_hook( __FILE__, 'postliking_install' );
register_uninstall_hook( __FILE__, 'postliking_uninstall' );
add_action( 'rest_api_init', 'post_liking_register_routes' );

function add_item_bar( WP_Admin_Bar $admin_bar ) {
    if( !current_user_can( 'manage_options' ) ) {
        return;
    }

    if( !is_single() ) {
        return;
    }

    $is_favorite = post_liking_check_favorite();

    if( $is_favorite === false ) {
        $admin_bar->add_menu( array(
            'id' => 'unfavorite',
            'parent' => null,
            'group' => null,
            'title' => 'Favoritar post',
            'href' => '#',
            'meta' => array(
                'onclick' => 'pl_favoritar_post()'
            )
        ) );
    } else {
        $admin_bar->add_menu( array(
            'id' => 'favorite',
            'parent' => null,
            'group' => null,
            'title' => 'Desfavoritar post',
            'href' => '#',
            'meta' => array(
                'onclick' => 'pl_desfavoritar_post()'
            )
        ) );
    }
}

function post_liking_enqueue_styles_scripts() {
    wp_enqueue_style( 'post_liking_style', plugins_url().'/post-liking/post-liking-style.css' );
}

add_action( 'admin_bar_menu', 'add_item_bar', 90 );
add_action( 'wp_enqueue_scripts', 'post_liking_enqueue_styles_scripts' );

function post_liking_check_favorite() {
    global $wpdb;
    $table_name = $wpdb->prefix.'postliking';
    $post_id = get_the_ID();
    if( $post_id !== false ) {
        $result = $wpdb->get_row( "SELECT post_id FROM $table_name WHERE post_id = ".$post_id );
        
        if($result === null) {
            return false;
        } else {
            return true;
        }
    }
    return false;
}

add_action( 'the_post', 'post_liking_expose_post_data' );

function post_liking_expose_post_data() {
    wp_register_script( 'post_liking_script', plugins_url().'/post-liking/post-liking-script.js' );
    
    $post_data = array(
        'post_id' => get_the_ID(),
        'post_title' => get_the_title(),
        'post_url' => get_permalink(),
    );
    
    wp_localize_script( 'post_liking_script', 'post_object', $post_data );

    wp_enqueue_script( 'post_liking_script');
}

?>