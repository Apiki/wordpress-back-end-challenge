<?php
     
    /*
    Plugin Name: Favoritar Posts - WordPress Back-end Challenge
    Description: Plugin em WordPress que implemente a funcionalidade de favoritar posts para usuários logados usando a WP REST API.
    Possibilidade de favoritar e desfavoritar um post;
    Persistir os dados em uma tabela a parte;

    Version: 1.0
    Author: Pedro Santos
    License: GPL2
    */


    global $favoriteVersion;
    $favoriteVersion = '1.0';
    $user = 0 ;
    
    /**
     * somente usuários logados
     */

    add_filter( 'rest_authentication_errors', 'only_authorised_rest_access');

    function only_authorised_rest_access( $result )
    {
        if( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_unauthorised', __( 'Only authenticated users can access the REST API.', 'rest_unauthorised' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return $result;
    }

    /**
     * Cria a tabela de favoritos
     */

    function favorite_install() {

        global $wpdb;
        global $favoriteVersion;
    
        $table_name = $wpdb->prefix . 'favorite';
        
        $charset_collate = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            id_post INT NOT NULL,
            user_id INT NOT NULL,
            status INT NOT NULL,
            PRIMARY KEY  (id) ,
            UNIQUE (id_post)
        ) $charset_collate;";
    
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql );
    
        add_option( 'favoriteVersion', $favoriteVersion );
    }

    add_option( "favoriteVersion", $favoriteVersion );

    register_activation_hook( __FILE__, 'favorite_install' );


    /**
     * inicia o script e captura o id do usuário 
     */

    add_action('init','start');
    function start(){

        global $user ;
        
        $current_user = wp_get_current_user();

        $user = $current_user->id  ;

    }
       

    /**
     * adiciona o post como favorito 
     */


    function favorite_create($post  ) {
        global $wpdb;
        global $user;
                
        $table_name = $wpdb->prefix . 'favorite';
        
        $wpdb->insert( 
            $table_name, 
            array( 
                'id_post' => $post, 
                'status' => 1, 
                'user_id' => $user, 
            ) 
        );
    }


    /**
     * remove o post como favorito 
     */

    function favorite_destroy($id) {
        global $wpdb;
        global $user;

        $sql = " DELETE FROM  ".$wpdb->prefix ."favorite WHERE `id_post` =%d and user_id =%d";

        try {
            $wpdb->query($wpdb->prepare($sql, array( $id , $user )));

            return true;
        } catch (Exception $e) {

            return new WP_Error( 'no_posts', __('No favorite found'), array( 'status' => 404 ) ); // status can be changed to any number
        }
    }

    /**
     * verifica o status do post  
     */

    function get_favorite($id) {

        global $wpdb;
        global $user;

        $results = $wpdb->get_row( 
            $wpdb->prepare("SELECT status FROM {$wpdb->prefix}favorite WHERE id_post=%d and user_id=%d ", [ $id, $user ] ) 
        );
   
        $status = $results->status == 1 ? true : false ; 

        return $status ; 
    }


    /**
     * adiciona a cada post em wp-json/wp/v2/posts o status de "favorite" como "true" ou "false"
     */
    
    add_action( 'rest_api_init', 'add_custom_fields' );
    function add_custom_fields() {
        register_rest_field(
        'post', 
        'favorite', //New Field Name in JSON RESPONSEs
        array(
            'get_callback'    => 'get_custom_fields', 
            'update_callback' => null,
            'schema'          => null,
            )
        );
    }


    function get_custom_fields( $object, $field_name, $request ) {     
        return get_favorite($object['id'] ) ;
    };

    function getId( $request ) {    
        
        $id = $request['id'] ;

        return get_favorite($id ) ;
    };

    /**
     * lista de posts com favoritos
     */

    function favoritesList( ) {

        global $wpdb;

        global $user;
    
        $results = $wpdb->get_results( 
            $wpdb->prepare("SELECT {$wpdb->prefix}favorite.status as status, {$wpdb->prefix}posts.* FROM {$wpdb->prefix}favorite 
                inner join {$wpdb->prefix}posts  on {$wpdb->prefix}favorite.id_post = {$wpdb->prefix}posts.id and {$wpdb->prefix}favorite.status = 1 and {$wpdb->prefix}favorite.user_id = $user "
            ) 
        );
   
        return $results ;         
    }

    
    /**
     * atualizar 
     */
    function update( $request ) {

        $id = $request['id'] ;

        if (!empty( $id) ){
            
            return favorite_create($id ) ; 
        }
        
        return new WP_Error( 'no_posts', __('No post found'), array( 'status' => 404 ) ); // status can be changed to any number
    }

    /**
     * remover
     */

    function delete( $request ) {

        $id = $request['id'] ;

        if (!empty( $id) ){

            return favorite_destroy($id ) ; 

        }
        
        return new WP_Error( 'no_posts', __('No post found'), array( 'status' => 404 ) ); // status can be changed to any number
        
    }


    /** 
     * Rotas
     */
    add_action('rest_api_init',
        function () {
            register_rest_route(
                'wp/v2/',
                '/favorites/(?P<id>\d+)',
                array(
                  'methods' => 'GET',
                  'callback' => 'getId', // Esse callable será chamado para responder as chamadas para '/wp-json/wp/v2/favorites/<id>'.
                )
            );
        }
    );
    add_action('rest_api_init',
        function () {
            register_rest_route(
                'wp/v2/',
                '/favorites/(?P<id>\d+)',
                array(
                  'methods' => 'PUT',
                  'callback' => 'update', // Esse callable será chamado para responder as chamadas para '/wp-json/wp/v2/favorites/<id>'.
                )
            );
        }
    );

    add_action('rest_api_init',
        function () {
            register_rest_route(
                'wp/v2/',
                '/favorites/(?P<id>\d+)',
                array(
                  'methods' => "DELETE",
                  'callback' => 'delete', // Esse callable será chamado para responder as chamadas para '/wp-json/wp/v2/favorites/<id>'.
                )
            );
        }
    );

    add_action('rest_api_init',
        function () {
            register_rest_route(
                'wp/v2/',
                '/favorites/',
                array(
                  'methods' => 'GET',
                  'callback' => 'favoritesList', // Esse callable será chamado para responder as chamadas para '/wp-json/wp/v2/favorites/'.
                )
            );
        }
    );

