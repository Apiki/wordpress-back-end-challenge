<?php
/**
 * Arquivo da classe InstallWordpressPlugin
 * 
 * PHP version 8.3.7
 * 
 * @category InstallWordpressPlugin
 * @package  Mateus Ávila Isidoro
 * @author   Mateus Ávila Isidoro <mateus@mateusavila.com.br>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/mateusavilaisidoro
 */

namespace MateusAvila;

class InstallWordpressPlugin
{
    public function __construct()
    {
        // Usa o hook correto para ativação
        register_activation_hook( MAINDIR . '/mateus-avila-isidoro.php', [ 'MateusAvila\InstallWordpressPlugin', 'create_table' ] ); 
    }

    /**
     * Create new table when the user activates the plugin
     *
     * @return void apenas cria o banco
     */
    public static function create_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . "favorite";
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table ( 
          id BIGINT(20) NOT NULL AUTO_INCREMENT, 
          post_id BIGINT(20) NOT NULL, 
          user_id BIGINT(20) NOT NULL, 
          fav_date DATETIME NOT NULL,
          PRIMARY KEY (id)
        ) $charset;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

$app = new InstallWordpressPlugin();
