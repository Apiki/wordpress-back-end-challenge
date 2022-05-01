<?php

function my_plugin_activate() {  

  add_option( 'Activated_Plugin', 'bruno-plugin' );

	global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  
  $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}post_likes` (
    `ID` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `post_id` bigint(20) UNSIGNED NOT NULL,
    `like_status` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY  (`ID`)
  ) $charset_collate;";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
  
}
