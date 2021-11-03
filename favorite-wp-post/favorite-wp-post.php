<?php

/**
 * Plugin Name:       Favorite Post
 * Description:       Given a post, the suscriber user toggle favorite status.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Yoelvis Alfredo Jimenez Rondon
 * Author URI:        https://alfredojry.github.io/about
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
 

// Instalação do plugin
// Padrão de sufixo: fwpp (Favorite WordPress Post)

global $fwpp_db_version;
$fwpp_db_version = '0.1';

function fwpp_install() {
	global $wpdb;
	global $fwpp_db_version;

	$table_name = $GLOBALS['wpdb']->prefix . 'favorites';
	
	$charset_collate = $GLOBALS['wpdb']->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		post_id INT NOT NULL UNIQUE,
		user_id INT NOT NULL,
		is_favorite BOOLEAN NOT NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'fwpp_db_version', $fwpp_db_version );
}

register_activation_hook( __FILE__, 'fwpp_install' );

// Filtrar usuários não logados
add_filter( 'rest_authentication_errors', function( $result ) {
    // If a previous authentication check was applied,
    // pass that result along without modification.
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }
    // No authentication has been performed yet.
    // Return an error if user is not logged in.
    if ( ! is_user_logged_in() ) {
        return new WP_Error('rest_not_logged_in', __( 'You are not currently logged in.' ),['status' => 401]);
    }
    // Our custom authentication check should have no effect
    // on logged-in requests
    return $result;
});

// incializando action para obter id de usuário logado
global $userId;
$userId = 0;
add_action('init', 'fwpp_set_user_id');
function fwpp_set_user_id() {
	global $userId;
	$userId = get_current_user_id();
}

// funções de manipulação da base de dados

function fwpp_check_favorite($data) {
	$table = $GLOBALS['wpdb']->prefix . 'favorites';
	$userId = $GLOBALS['userId'];
	$postId = $data['postId'];	// verificar se o post existe
	if (!get_post($postId)) {
		return new WP_Error('invalid_post_id', __( 'The post does not exist' ),['status' => 400]);
	}
	$query_text = "SELECT * FROM $table WHERE user_id = $userId AND post_id = $postId;";
	$row = $GLOBALS['wpdb']->get_row($query_text);
	$res = [
		'postId' => $postId,
		'isFavorite' => (bool) $row,
	];
	return $res;
}

function fwpp_add_favorite($data) {
	$table = $GLOBALS['wpdb']->prefix . 'favorites';
	$userId = $GLOBALS['userId'];
	$postId = $data['postId'];
	if (!get_post($postId)) {
		return new WP_Error('invalid_post_id', __( 'The post does not exist' ),['status' => 400]);
	}
	$newRow = [
		'user_id' => $userId,
		'post_id' => $postId,
		'is_favorite' => 1,
	];
	$GLOBALS['wpdb']->insert($table, $newRow);
	$res = [
		'postId' => $postId,
		'isFavorite' => true,
	];
	return $res;
}

function fwpp_delete_favorite($data) {
	$table = $GLOBALS['wpdb']->prefix . 'favorites';
	$userId = $GLOBALS['userId'];
	$postId = $data['postId'];
	if (!get_post($postId)) {
		return new WP_Error('invalid_post_id', __( 'The post does not exist' ),['status' => 400]);
	}
	$row = [
		'user_id' => $userId,
		'post_id' => $postId,
		'is_favorite' => 1,
	];
	$GLOBALS['wpdb']->delete($table, $row);
	$res = [
		'postId' => $postId,
		'isFavorite' => true,
	];
	return $res;
}

// hooks pra inicialização da API REST

add_action('rest_api_init', function () {
	register_rest_route('favorite-wp-post/v1', '/(?P<postId>\d+)', [
		'methods' => 'GET',
		'callback' => 'fwpp_check_favorite',
	]);
});

add_action('rest_api_init', function () {
	register_rest_route('favorite-wp-post/v1', '/(?P<postId>\d+)', [
		'methods' => 'POST',
		'callback' => 'fwpp_add_favorite',
	]);
});

add_action('rest_api_init', function () {
	register_rest_route('favorite-wp-post/v1', '/(?P<postId>\d+)', [
		'methods' => 'DELETE',
		'callback' => 'fwpp_delete_favorite',
	]);
});

