<?php

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;

$table = $wpdb->prefix . 'favorites';
$wpdb->query("DROP TABLE IF EXISTS $table");

delete_option('wpfav_db_version');