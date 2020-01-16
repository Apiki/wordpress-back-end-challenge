<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;
$table_name = $wpdb->prefix . 'wbec';

$sql = "DROP TABLE IF EXISTS $table_name;";

$wpdb->query($sql);