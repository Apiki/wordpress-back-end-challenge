<?php

/**
 * Plugin Name: Plugin do Bruno
 * Description: Plugin feito para o desafio da Apiki
 * Version: 1.0
 * Author: Bruno Andrade
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once( plugin_dir_path(__FILE__).'/includes/bruno-plugin-tables.php');
require_once( plugin_dir_path(__FILE__).'/includes/bruno-plugin-like.php');
require_once( plugin_dir_path(__FILE__).'/includes/bruno-plugin-endpoint.php');

register_activation_hook( __FILE__, 'my_plugin_activate' );


?>