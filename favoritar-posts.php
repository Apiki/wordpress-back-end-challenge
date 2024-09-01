<?php
/**
 * Plugin Name: Favoritar Posts
 * Plugin URI: https://github.com/arunbasillal/WordPress-Starter-Plugin
 * Description: Plugin para favoritar posts usando a WP Rest API
 * Author: Alan Pardini Sant Ana
 * Author URI: https://www.linkedin.com/in/alanpardinisantana/
 * Version: 1.0
 * Text Domain: favoritar-posts
 * Domain Path: /languages
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MAINDIR', __DIR__ );

require_once plugin_dir_path( __FILE__ ) . '/classes/CreateTable.php';
require_once plugin_dir_path( __FILE__ ) . '/classes/LoginNaWPRestAPI.php';
require_once plugin_dir_path( __FILE__ ) . '/classes/WPRestApiFavPost.php';
require_once plugin_dir_path( __FILE__ ) . '/classes/WPRestApiDesfavPost.php';
require_once plugin_dir_path( __FILE__ ) . '/classes/WPRestApiGetFavPosts.php';

