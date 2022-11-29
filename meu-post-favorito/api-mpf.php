<?php

/**
 * API Meu post favorito Plugin
 *
 * @package    MPF
 * @link       #
 * @since      0.1.0
 * @author     Bruno Lima
 *
 * Plugin Name:       Meu post favorito
 * Description:       Teste apiki
 * Version:           0.1.0
 * Author:            Bruno Lima
 * Author URI:        #
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Nome da tabela contendo os favoritos
if ( ! defined( 'MPF_LIKEPOSTS' ) ) {
	define( 'MPF_LIKEPOSTS', 'mpf_likepost' );
}


/**
 * Runs during API Meu post favorito activation.
 * This action is documented in includes/class-mpf-activator.php
 */
function activate_api_mpf() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mpf-activator.php';
	mpf_Activator::activate();
}

/**
 * Runs during API Meu post favorito deactivation.
 * This action is documented in includes/class-mpf-deactivator.php
 */
function deactivate_api_mpf() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mpf-deactivator.php';
	mpf_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_api_mpf' );
register_deactivation_hook( __FILE__, 'deactivate_api_mpf' );

/**
 * A classe principal do plugin 
 * 
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mpf.php';

/**
 * Inicializador
 *
 * @since    0.1.0
 */
function run_api_mpf() {
	
	$plugin = new MPF();
	$plugin->run();

}
run_api_mpf();
