<?php

/**
 * @package like_post
 * @version 1.0.0
 */
/*
Plugin Name: Like Post
Plugin URI: https://www.linkedin.com/in/leonardo-mazza-886162128/
Description: Like/unlike posts
Author: Leonardo Mazza
Version: 1.0.0
Author URI: https://www.linkedin.com/in/leonardo-mazza-886162128/
*/

require ('vendor/autoload.php');

use Mazza\WordpressBackEndChallenge\CreateTable;
use Mazza\WordpressBackEndChallenge\LikeRestAPI;

if (!defined('ABSPATH')) {
    exit;
}

global $jal_db_version;
$jal_db_version = '1.0';

/*
 * Call the Class to create REST API
 */
$rest_api = new LikeRestAPI();


/*
 * Call the Class to create db table
 */
$create_db = new CreateTable();
$create_db->jal_install();
