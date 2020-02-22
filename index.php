<?php
/**
* Plugin Name: Ratein
* Author: bryan-fm
* Description: Plugin para desafio Back-end Apiki
* Version: 1.0
**/

//Includes
include('include/ativar.php');
include('include/func.php');
include('include/add_script.php');


//Hooks
register_activation_hook(__FILE__,'ativar_plugin');
add_action('wp_enqueue_scripts', 'include_js');
add_filter('the_content','botao_fav');
add_action('wp_ajax_fav_click', 'fav_click');