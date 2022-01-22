<?php

function my_plugin_activate() {

  

  add_option( 'Activated_Plugin', 'bruno-plugin' );

	global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  
  $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}post_likes` (
    `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `post_id` bigint(20) UNSIGNED NOT NULL,
    `like_status` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY  (`ID`)
  ) $charset_collate;";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

function my_plugin_uninstall(){
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  
  $sql = "DROP TABLE `{$wpdb->base_prefix}post_likes
  $charset_collate;";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

	
// register_activation_hook( plugin_dir_path(__FILE__).'bruno-plugin.php', 'delete_table' );