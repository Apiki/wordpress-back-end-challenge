<?php

namespace MateusAvila;

class UninstallWordpressPlugin
{
    public function __construct()
    {
      register_uninstall_hook( MAINDIR . '/mateus-avila-isidoro.php', [ 'MateusAvila\UninstallWordpressPlugin', 'remove_table' ] );    
    }

    /**
     * remove the table
     *
     * @return void remove a tabela criada, limpando os dados do plugin
     */
    public function remove_table()
    {
        global $wpdb;
        $table = $wpdb->prefix."favorite";
        $sql = "DROP TABLE IF EXISTS $table";
        $wpdb->query( $sql );
    }
}

$app = new UninstallWordpressPlugin();