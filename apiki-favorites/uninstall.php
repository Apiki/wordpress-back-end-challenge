<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'apiki_favorites';

// Don't do this in production.
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );