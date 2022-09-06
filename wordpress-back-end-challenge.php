<?php 
/**
 * Plugin Name: Wordpress back end challenge
 * Plugin URI:	
 * Description:	Plugin do desafio da apiki com a função de favoritar posts
 * Version:		1.0.0
 * Author:		Felipe Peixoto
 * Author URI:	http://felipepeixoto.tecnologia.ws/
 */
if ( ! defined( 'WPINC' ) ) { die; }

define( 'WBEC_WP_VERSION', '1.0.0' );
define( 'WBEC_WP_PATH', plugin_dir_path(__FILE__) );

function wbec_activate() {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-wbec-active.php';
}
register_activation_hook( __FILE__, 'wbec_activate' );

if (is_admin()){
    require WBEC_WP_PATH . 'admin/class-wbec-admin.php';
}

add_action( 'wp_ajax_toggle_fav', 'wbec_toggle_fav' );
function wbec_toggle_fav(){
     global $wpdb;
     $id = $_POST['id'];
     $table_name = $wpdb->prefix . 'wbec_fav';

     $result = $wpdb->get_results ( "SELECT * FROM $table_name WHERE post_id = $id");
     if(empty($result)){
        if ( $wpdb->insert( $table_name, array(
            'post_id' => $id,
        ) ) === false ) {
            echo 'false';
        } else {
            echo 'true';
        }
    } else {
        $wpdb->delete($table_name, array( 'post_id' => $id ));
        echo 'true';
    }

     
    die();
}

add_action( 'rest_api_init', function() {
    register_rest_route( 'wbec/v1' , '/favs', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'wbec_get_favs',
        'permission_callback' => function() { return ''; }
    ));
});

function wbec_get_favs(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'wbec_fav';
    $result = $wpdb->get_results ( "SELECT * FROM $table_name");
    $favs = array();
    foreach ($result as $r){
        $favs[] = $r->post_id;
    }
    return json_encode($favs);
}