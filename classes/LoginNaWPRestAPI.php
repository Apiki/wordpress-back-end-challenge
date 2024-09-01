<?php
/**
 * Arquivo da classe LoginNaWPRestAPI
 * 
 * PHP version 7.4
 * 
 * @category LoginNaWPRestAPI
 * @package  WPPluginFavoritarPosts
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */

namespace App;

/**
 * Login na WP Rest API
 * 
 * PHP version 7.4
 * 
 * @category Class
 * @package  LoginNaWPRestAPI
 * @author   Alan Pardini Sant Ana <alanps2012@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://www.linkedin.com/in/alanpardinisantana/
 */
class LoginNaWPRestAPI
{

    /**
     * Construct
     */
    public function __construct()
    {
        /**
         * Hooks
         */
        add_filter( 'rest_authentication_errors', [ $this, 'rest_auth' ] );
        add_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
    }

    /**
     * Função de retorno de não logado
     *
     * @return null Não retorna nada
     */
    public function rest_auth( $result ) {
        // If a previous authentication check was applied,
        // pass that result along without modification.
        if ( true === $result || is_wp_error( $result ) ) {
            return $result;
        }
    
        // No authentication has been performed yet.
        // Return an error if user is not logged in.
        if ( ! is_user_logged_in() ) {
            return new \WP_Error(
                'rest_not_logged_in',
                __( 'You are not currently logged in.' ),
                array( 'status' => 401 )
            );
        }
    
        // Our custom authentication check should have no effect
        // on logged-in requests
        return $result;
    }

    /**
     * Função de determinação do usuário logado
     *
     * @return null Não retorna nada
     */
    public function json_basic_auth_handler( $user ) {
        global $wp_json_basic_auth_error;
    
        $wp_json_basic_auth_error = null;
    
        // Don't authenticate twice
        if ( ! empty( $user ) ) {
            return $user;
        }
    
        // Check that we're trying to authenticate
        if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            return $user;
        }
    
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
    
        /**
         * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
         * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
         * recursion and a stack overflow unless the current function is removed from the determine_current_user
         * filter during authentication.
         */
        remove_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
    
        $user = wp_authenticate( $username, $password );
    
        add_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
    
        if ( is_wp_error( $user ) ) {
            $wp_json_basic_auth_error = $user;
            return null;
        }
    
        $wp_json_basic_auth_error = true;
    
        return $user->ID;
    }

}

$LoginNaWPRestAPI = new LoginNaWPRestAPI();
