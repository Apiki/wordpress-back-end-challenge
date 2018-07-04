<?php
/*
 *Plugin Name: FavUserApiki
 * Author: Guilherme Costa
 * Description: Estudo Api Wordpress
 * Version: 1.0
 * Author URI:https://github.com/guilhermehrcosta
 */
include_once 'DBUtil.php';
include_once 'ApiController.php';
function createTable(){
    $db = new DBUtil();
    $db->createTable();
}
function registerRoute() {
    $controller = new ApiController();
    $controller->register_routes();
}
register_activation_hook (__FILE__, 'createTable');
registerRoute();