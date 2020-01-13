<?php

class Post_Liking_Route_Controller extends WP_REST_Controller 
{
    public function register_routes() {
        $version = '1';
        $namespace = 'post-liking/v'.$version;
        $base = 'favorites';
        
        register_rest_route( $namespace, '/'.$base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_items' ),
                #'permission_callback' => array( $this, 'get_items_permissions_check' )
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'create_item' ),
                #'permission_callback' => array( $this, 'create_item_permissions_check' )
            )
        ) );

        register_rest_route( $namespace, '/'.$base.'/(?P<post_id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_item' ),
                #'permission_callback' => array( $this, 'get_item_permissions_check' )
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_item' ),
                #'permission_callback' => array( $this, 'delete_item_permissions_check' )
            )
        ) );
    }
    
    public function get_items( $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix.'postliking';

        $results = $wpdb->get_results( "SELECT * FROM $table_name", 'ARRAY_A' );
        
        $response = array();

        foreach($results as $item) {
            $itemdata = $this->prepare_item_for_response($item, $request);
            $response[] = $this->prepare_response_for_collection($itemdata);
        }

        return new WP_REST_Response( $response, 200 );
    }

    public function get_items_permissions_check( $request ) {
        if ( !current_user_can( 'edit_others_posts' ) ) {
            return new WP_Error( 'rest_forbidden', 'Você não pode acessar esse recurso!', array( 'status' => 401 ) );
        }
        return true;
    }

    public function get_item( $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix.'postliking';

        $item = $request->get_url_params();

        if( $item === null ) {
            return array("Não ha recursos!");
        }

        $result = $wpdb->get_row( "SELECT * FROM $table_name WHERE post_id = ".$item['post_id'], 'ARRAY_A' );
        
        $response = $this->prepare_item_for_response( $result, $request );

        return $response;
    }

    public function get_item_permissions_check( $request ) {
        if ( !current_user_can( 'edit_others_posts' ) ) {
            return new WP_Error( 'rest_forbidden', 'Você não pode acessar esse recurso!', array( 'status' => 401 ) );
        }
        return true;
    }

    public function create_item( $request ) {
        $item = $this->prepare_item_for_database( $request );
        if( is_wp_error($item) ) {
            return $item;
        }

        return new WP_REST_Response( $item, 200 );
    }

    public function create_item_permissions_check( $request ) {
        if ( !current_user_can( 'edit_others_posts' ) ) {
            return new WP_Error( 'rest_forbidden', 'Você não pode acessar esse recurso!', array( 'status' => 401 ) );
        }
        return true;
    }

    public function delete_item( $request ) {
        $item = $this->prepare_item_for_database( $request );

        return new WP_REST_Response( $item, 200 );
    }

    public function delete_item_permissions_check( $request ) {
        if ( !current_user_can( 'edit_others_posts' ) ) {
            return new WP_Error( 'rest_forbidden', 'Você não pode acessar esse recurso!', array( 'status' => 401 ) );
        }
        return true;
    }

    public function prepare_item_for_database( $request ) {
        $data = $request->get_json_params();
        global $wpdb;
        $table_name = $wpdb->prefix.'postliking';

        if( $request->get_method() === 'POST' ) {
            $item = array(
                'post_id' => $data['post_id'],
                'post_title' => $data['post_title'],
                'post_url' => $data['post_url'],
                'time_created' => current_time( 'mysql' )
            );
        
            $wpdb->insert( $table_name, $item, array('%d', '%s', '%s') );
            if( $wpdb->insert_id === false ) {
                return new WP_Error( 'cannot_insert', 'Não foi possivel inserir na tabela!', array( 'status' => 401 ) );
            }
        } else if ( $request->get_method() === 'DELETE' ) {
            $item = array(
                'post_id' => $data['post_id']
            );
            
            $wpdb->delete( $table_name, $item, array('%d') );
        }
        
        return $data;
    }

    public function prepare_item_for_response( $item, $request ) {
        if($item === null) {
            return new WP_Error( 'rest_no_route', 'Nenhuma rota foi encontrada que corresponde com o URL e o método de requisição', array( 'status' => 404 ) );
        }

        return new WP_REST_Response(
            array(
                'status' => 200,
                'response' => 'Completado!',
                'body_response' => $item
            )
        );
    }
}
?>