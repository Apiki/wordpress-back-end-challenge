<?php

    /*
    Plugin Name: Favoritar Posts - WordPress Back-end Challenge
    Description: Plugin em WordPress que implemente a funcionalidade de favoritar posts para usuários logados usando a WP REST API.
    Possibilidade de favoritar e desfavoritar um post;
    Persistir os dados em uma tabela a parte;
    Version: 1.0
    Author: Rodrigo Z Gaspar
    License: GPL2
    */

    global $pluginVersion;
    $pluginVersion = '1.0';
    $current_user = 0;

    // Autorizes only loggedin users 
    add_filter( 'rest_authentication_errors', 'only_loggedin_users');
    function only_loggedin_users( $ret )
    {
        if(!is_user_logged_in())
        {
            return new WP_Error( 'rest_unauthorised', __( 'Only authenticated users can access the REST API.', 'rest_unauthorised' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return $ret;
    }

    // Create favoritar table 
    function installFavoritar() {

        global $pluginVersion;
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');

        $table = $GLOBALS['wpdb']->prefix . 'favoritar';
        $charset = $GLOBALS['wpdb']->get_charset_collate();
        $query = "CREATE TABLE $table (
            id INT NOT NULL AUTO_INCREMENT,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            status BOOLEAN NOT NULL,
            PRIMARY KEY (id),
            UNIQUE (post_id)
        ) $charset;";
        dbDelta($query);
        add_option( 'pluginVersion', $pluginVersion );

    }

    add_option( "pluginVersion", $pluginVersion );
    register_activation_hook( __FILE__, 'installFavoritar' );

    // Starts the plugin
    add_action('init','start');
    function start(){
        $current_user = get_current_user_id();
    }

    function commutePost($post){

        $post = (is_numeric($post)) ? $post : $post['id'];
        if (get_post_status($post)) {
            return $post
        }else{
            return new WP_Error('no_posts', __('Post not found'), array('status' => 404)); 
        }

    }

    // Add post as favorite
    // Callable PUT favoritar/:id
    function favoritar($post) {

        $post = commutePost($post);
        $table = $GLOBALS['wpdb']->prefix . 'favoritar';
        $GLOBALS['wpdb']->insert( 
            $table, 
            array( 
                'user_id' => get_current_user_id(),
                'post_id' => $post, 
                'status' => 1
            ) 
        );

    }

    // Callable DELETE favoritar/:id
    // Removes post as favorite
    function desfavoritar($post) {

        $post = commutePost($post);
        $table = $GLOBALS['wpdb']->prefix . 'favoritar';
        $query = "DELETE FROM $table WHERE user_id = %d and post_id = %d";
        $prepared = $GLOBALS['wpdb']->prepare($query,array(get_current_user_id(),$post));

        try {
            $GLOBALS['wpdb']->query($prepared);
        } catch (Exception $x) {
            return new WP_Error('no_posts',__('Favorite not found'), array('status' => 404));
        }
        return true;

    }

    // Callable GET favoritar/:id
    // Check post status
    function checkFavoritarStatus($post) {

        $post = commutePost($post);
        $table = $GLOBALS['wpdb']->prefix . 'favoritar';
        $query = "SELECT status FROM $table WHERE user_id = %d and post_id = %d";
        $prepared = $GLOBALS['wpdb']->prepare($query,array(get_current_user_id(),$post));
        $row = $GLOBALS['wpdb']->get_row($prepared);
        return $row->status; 

    }

    // Set custom field favoritado on /posts calls
    function add_favoritado_fields() {

        register_rest_field('post', 'favoritado', array(
                'get_callback'    => 'get_favoritado_field', 
                'update_callback' => null,
                'schema'          => null,
            )
        );

    }
    add_action('rest_api_init', 'add_favoritado_fields');

    // Callable for custom field requests
    function get_favoritado_field($object, $field_name, $request) {     
        return checkFavoritarStatus($object);
    };

    // List posts favoritados
    function favoritarList() {

        $favoritarTable = $GLOBALS['wpdb']->prefix . 'favoritar';
        $postsTable = $GLOBALS['wpdb']->prefix . 'posts';
        $query = "SELECT f.status as status, p.* 
                FROM $favoritarTable f 
                INNER JOIN $postsTable p 
                ON f.post_id = p.id AND f.status = 1 AND f.user_id = %d";
        $prepared = $GLOBALS['wpdb']->prepare($query,get_current_user_id());

        return $GLOBALS['wpdb']->get_results($prepared);  
               
    }


    // Define Routes
    add_action('rest_api_init', function () {
        register_rest_route(
            'wp/v2/',
            '/favoritar/(?P<id>\d+)',
            array(
              'methods' => 'GET',
              'callback' => 'checkFavoritarStatus'
            )
        );
    });

    add_action('rest_api_init', function () {
        register_rest_route(
            'wp/v2/',
            '/favoritar/(?P<id>\d+)',
            array(
              'methods' => 'PUT',
              'callback' => 'favoritar'
            )
        );
    });

    add_action('rest_api_init',function () {
        register_rest_route(
            'wp/v2/',
            '/favoritar/(?P<id>\d+)',
            array(
              'methods' => "DELETE",
              'callback' => 'desfavoritar'
            )
        );
    });

    add_action('rest_api_init', function () {
        register_rest_route(
            'wp/v2/',
            '/favoritar/',
            array(
              'methods' => 'GET',
              'callback' => 'favoritarList'
            )
        );
    });