<?php
/**
 * Arquivo da classe CreateTable
 * 
 * PHP version 7.4
 * 
 * @category CreateTable
 * @package  WPPluginFavoritarPosts
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */

namespace App;

/**
 * Classe de criação da nova tabela no banco de dados
 * 
 * PHP version 7.4
 * 
 * @category Class
 * @package  CreateTable
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */
class CreateTable
{

    /**
     * Construct
     */
    public function __construct()
    {
        /**
         * Hooks
         */
        register_activation_hook( MAINDIR . '/favoritar-posts.php', [ $this, 'create_new_table' ] );
    }

    /**
     * Função de criação da nova tabela
     *
     * @return null Não retorna nada
     */
    public function create_new_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'favoritar_posts';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        post_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        $wpdb->query( $sql );
    }

}

$CreateTable = new CreateTable();
