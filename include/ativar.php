<?php

function ativar_plugin()
{
    global $wpdb;

    $fav = "CREATE TABLE ".$wpdb->prefix."fav_post(
        ID int(10) NOT NULL AUTO_INCREMENT,
        post_id int(10) NOT NULL,
        user_id int(10) NOT NULL,
        PRIMARY KEY (ID)
    ) ".$wpdb->get_charset_collate();

    require_once(ABSPATH.'/wp-admin/includes/upgrade.php');
    dbDelta($fav);
}
