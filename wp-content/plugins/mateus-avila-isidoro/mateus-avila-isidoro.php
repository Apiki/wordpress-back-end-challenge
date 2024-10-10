<?php
/**
 * Plugin Name: Mateus Ávila Isidoro
 * Description: Plugin para atender os requisitos da vaga de Dev Backend da Apiki
 * Author: Mateus Ávila Isidoro
 * Author URI: https://www.linkedin.com/in/mateusavilaisidoro/
 * Version: 1.0
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MAINDIR', __DIR__ );

// importando as classes
require_once plugin_dir_path( __FILE__ ) . '/classes/install.php';
require_once plugin_dir_path( __FILE__ ) . '/classes/uninstall.php';
require_once plugin_dir_path( __FILE__ ) . '/classes/routes.php';