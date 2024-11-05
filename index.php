<?php
/*
 * Plugin Name:       Favorite or unfavorite plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      >= 7.2
 * Author:            Alexsander Kafka
 * Author URI:        https://author.example.com/
 * License:           GPL v2
 * Text Domain:       favorite-unfavorite
 * Domain Path:       /languages
 */



require_once __DIR__ . '/vendor/autoload.php';

use Alexs\FavoriteUnfavorite\routes\Routes;
use Alexs\FavoriteUnfavorite\db\DataBase;

$db = new DataBase();
$db->init();

add_action( 'rest_api_init',  [Routes::class, 'registerRoutes']);