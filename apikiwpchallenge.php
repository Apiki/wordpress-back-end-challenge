<?php
/*
Plugin Name: Apiki WP Challenge
Description: codes for challenge
Author: Fernando Saling
*/

require_once plugin_dir_path(__FILE__) . 'install.php';

add_action("wp_ajax_des_Favoritar", "des_Favoritar");
add_action("wp_ajax_nopriv_des_Favoritar", "must_login");

// Função para consultar, gravar ou excluir o post favorito
function des_Favoritar() {
   
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "user_des_Favoritar_nonce")) {
      exit("Problemas na autenticação!");
    }

    $post_id = $_REQUEST['post_id'];

    $user_id = 0;
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
    } else {
        exit("Usuário não logado! Favor fazer login.");
    }
   
    global $wpdb;
    $table_des_Favoritar = $wpdb->prefix . "des_Favoritar";

    $resultsdes_Favoritar = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
            FROM $table_des_Favoritar
            WHERE post_id =  $post_id
            AND user_id = $user_id"
        )
    );
    $estavaFavoritado = count($resultsdes_Favoritar) > 0;
    if ($estavaFavoritado) {
        $wpdb->query( $wpdb->prepare( 
            "DELETE FROM $table_des_Favoritar 
            WHERE post_id = %d
            AND user_id = %s",
                $post_id, $user_id,
        ) );
    } else {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table_des_Favoritar
                 ( post_id, user_id )
                 VALUES ( %d, %d )",
                $post_id,
                $user_id,
            )
        );
    }
     
    $resultsdes_Favoritar = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * 
            FROM $table_des_Favoritar
            WHERE post_id =  $post_id
            AND user_id = $user_id"
        )
    );
    $atualizouFavoritado = count($resultsdes_Favoritar) > 0;

    if ($estavaFavoritado != $atualizouFavoritado) {
        if (count($resultsdes_Favoritar) == 0) {
            $result['status'] = "success";
            $result['des_Favoritar'] = 'Desfavoritou';
        } else {
            $result['status'] = "success";
            $result['des_Favoritar'] = 'Favoritou';
        }
    } else {
        $result['status'] = "error";
        $result['des_Favoritar'] = 'Problemas ao registrar favorito!Por favor, tente novamente.';
    }
   
   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }

   die();
}

// Função para usuários não logados
function must_login() {
    $result['status'] = "error";
    $result['des_Favoritar'] = 'Você deve estar logado para favoritar ou desfavoritar.';

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $result = json_encode($result);
        echo $result;
    }
    else {
        header("Location: ".$_SERVER["HTTP_REFERER"]);
    }
    die();
}

// Função para funcionar via AJAX
add_action( 'init', 'script_enqueuerapiki' );
function script_enqueuerapiki() {
   
    // Register the JS file with a unique handle, file location, and an array of dependencies
   wp_register_script( "des_Favoritar_script", plugin_dir_url(__FILE__).'des_Favoritar_script.js', array('jquery') );
   
   // localize the script to your domain name, so that you can reference the url to admin-ajax.php file easily
   wp_localize_script( 'des_Favoritar_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   
   // enqueue jQuery library and the script you registered above
   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'des_Favoritar_script' );
}