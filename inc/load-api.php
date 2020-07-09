<?php
class Bookmark_Posts_Routes extends WP_REST_Controller {
    public function register_bookmarkposts() {
        $version = '1';
        $namespace = 'bookmark-posts/v'.$version;

        register_rest_route( $namespace, '/mark/(?P<post_id>[\d]+)', [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'bookmarkposts_mark'],
                'permission_callback' => function () {
                    return true;
                },
                'args'      => []
            ]
        ]);

        register_rest_route( $namespace, '/unmark/(?P<post_id>[\d]+)', [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'bookmarkposts_unmark'],
                'permission_callback' => function () {
                    return true;
                },
                'args'      => []
            ]
        ]);
    }

    public function bookmarkposts_mark(WP_REST_Request $request) {
        global $wpdb;
        $post_url = $request->get_url_params();
        $post_id = $post_url['post_id'];

        if( $request->get_method() === 'GET' ) {
            $item = [
                'post_id'       => $post_id,
                'user_id'       => BOOKMARKPOSTS_CURRENT_USERID,
                'created_at'    => current_time( 'mysql' )
            ];

            $wpdb->insert( BOOKMARKPOSTS_TABLE, $item, array('%d', '%s', '%s') );
            if( $wpdb->insert_id === false ) {
                return new WP_Error( 'cannot_insert', 'Não foi possivel inserir na tabela!', array( 'status' => 401 ) );
            }
        }
        return $data;
    }

    public function bookmarkposts_unmark(WP_REST_Request $request) {
        global $wpdb;
        $post_url = $request->get_url_params();
        $post_id = $post_url['post_id'];

        if( $request->get_method() === 'GET' ) {
            $item = ['post_id' => $post_id];
            $wpdb->delete( BOOKMARKPOSTS_TABLE, $item, array('%d') );
        }
        return $data;
    }
}

function register_routes_bookmarkposts() {
    $controller = new Bookmark_Posts_Routes();
    $controller->register_bookmarkposts();
}
add_action('rest_api_init', 'register_routes_bookmarkposts');
