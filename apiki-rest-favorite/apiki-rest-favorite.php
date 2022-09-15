<?php
/**
 * @package apiki-rest-favorite
 */
/*
Plugin Name: Apiki REST Favorite
Plugin URI: http://apiki.com
Description: Plugin para favoritar posts usando REST API
Version: 1.0
Author: Werner Max Bohling
Author URI: http://apikki.com
License: GPLv2 or later
Text Domain: apiki
*/

/*
Apiki REST Favorite is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Apiki REST Favorite is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Apiki REST Favorite.
*/

// Não mostrar informações se chamado diretamente
if ( !function_exists( 'add_action' ) ) {
    exit;
}

define( 'FAVORITE_VERSION', '1.0' );
define( 'FAVORITE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if (! class_exists('ApikiRESTFavorite')) {

    class ApikiRESTFavorite {

        private static $instance;

        private function __construct() {
            register_activation_hook( __FILE__, array($this, 'apiki_rest_favorite_activation'));
            register_deactivation_hook( __FILE__, array($this, 'apiki_rest_favorite_deactivation'));
            register_uninstall_hook( __FILE__, array($this, 'apiki_rest_favorite_uninstall'));
        }

        /**
         * If an instance exists, this returns it.  If not, it creates one and
         * retuns it.
         *
         * @return self
         */
        public static function getInstance() {
            if ( !self::$instance )
                  self::$instance = new self;
            return self::$instance;
        }

        /**
         * Activate the plugin.
         */
        public static function apiki_rest_favorite_activation() {
            // cria a tabela para colocar os posts favoritados 
            global $wpdb;
            $table_name = $wpdb->prefix . "favorite"; 
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_ID bigint(20) NOT NULL,
            user_ID bigint(20) NOT NULL,
            PRIMARY KEY  (id)) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }

        /**
         * Deactivate the plugin.
         */
        public static function apiki_rest_favorite_deactivation() { 
        }

        /**
         * Uninstall the plugin.
         */
        public static function apiki_rest_favorite_uninstall() {
            // dropa a tabela que contem os posts favoritados
            global $wpdb;
            $table_name = $wpdb->prefix. "favorite";
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "DROP TABLE IF EXISTS $table_name";
            $wpdb->query($sql);
        }

        /**
         * init
         *
         * @return void
         */
        public static function init() {

            add_action('admin_enqueue_scripts', 'favorite_script');
            function favorite_script() {
                wp_register_script( 
                    'apiki-favorite-jquery-script',
                    plugins_url( '/apiki-rest-favorite-script.js', __FILE__ ), 
                    array( 'wp-api', 'jquery' )
                 );                   
                wp_enqueue_script( 'apiki-favorite-jquery-script', true );

                wp_localize_script( 'apiki-favorite-jquery-script', 'apikiScriptVars', [
                    'user' => get_current_user_id(),
                    'nonce' => wp_create_nonce('wp_rest'),
                ] );
            }    
            
            /* Add custom column to post list */
            function add_custom_column( $columns ) {
                return array_merge( $columns, 
                    array( 'sticky' => 'Favorito' ) );
            }
            add_filter( 'manage_posts_columns' , 'add_custom_column' );

            function favorite_custom_column ( $column, $post_id ) {
                echo "<input type='checkbox'
                    class='favoriteCheckbox'
                    id='{$post_id}'
                    value='{$post_id}'>";                    
            }
            add_action ( 'manage_posts_custom_column', 'favorite_custom_column', 10, 2 );

            require_once( FAVORITE__PLUGIN_DIR . 'apiki-rest-favorite-route-controller.php' );
            add_action( 'rest_api_init', 'Apiki_REST_Favorite_Route_Controller' );

        }
    }

    // Instantiate our class
    $plugin = ApikiRESTFavorite::getInstance();
    $plugin::init();

}
?>