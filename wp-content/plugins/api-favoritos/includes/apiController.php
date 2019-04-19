<?php

/*
*  Essa classe foi baseada no exemplo do REST API Handbook
*  que pode ser visualizado no link: https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/
*  Alexandre S. Anjos
*  https://github.com/AlexandreSA/wordpress-back-end-challenge/tree/alexandre-anjos
*/
class ApiController {
 
    // Here initialize our namespace and resource name.
    public function __construct() {
        $this->namespace     = '/api/v2';
        $this->resource_name = 'posts';
    }
 
    // Register our routes.
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name, array(
            // Here we register the readable endpoint for collections.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name, array(
            // Here we register the readable endpoint for collections.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'api_get_posts_favoritos' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/add' . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'api_add_posts_favoritos' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/del' . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods'   => 'DELETE',
                'callback'  => array( $this, 'api_del_posts_favoritos' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
            ),
        ) );
    }
 
    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check( $request ) {
        if ( ! current_user_can( 'read' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }
    
    /**
    *   Busca os posts publicados e retorna os valores definidos no array
    *   metodo = '$_GET'
    * @param WP_REST_Request $request Current request.
    *   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/
    */
    public function get_items( $request ) {
 		$args = array(
            'post_per_page' => 5,
        );
        $posts = get_posts( $args );
 
        $data = array();
 
        if ( empty( $posts ) ) {
            return rest_ensure_response( $data );
        }
 
        foreach ( $posts as $post ) {
            $data[] =  array(
		        'id' => $post->ID,
		        'title' => $post->post_title,
		        'author' => get_the_author_meta( 'display_name', $post->post_author ),
		        'content' => apply_filters( 'the_content', $post->post_content ),
		        'teaser' => $post->post_excerpt,
		        'guid' => $post->guid
		      );
        }
 
        // Return all of our comment response data.
        return rest_ensure_response( $data );
    }
 
    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_permissions_check( $request ) {
        if ( ! current_user_can( 'read' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }
  
    /**
    *   Busca o post pelo ID definido na requisição
    *   metodo = '$_GET'
    * @param WP_REST_Request $request Current request.
    *   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/
    */
    public function get_item( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );
 
        if ( empty( $post ) ) {
            return rest_ensure_response( array() );
        }
 		
 		$data[] = array(
		    'id' => $post->ID,
		    'title' => $post->post_title,
		    'author' => get_the_author_meta( 'display_name', $post->post_author ),
		    'content' => apply_filters( 'the_content', $post->post_content ),
		    'teaser' => $post->post_excerpt,
		    'guid' => $post->guid
	  	);
 
        // Return all of our post response data.
        return $data;
    }

    /**
    *   Busca os posts pelo ID definido no endpoint
    *   metodo = '$_GET'
    *   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/favoritos
    */
    function api_get_posts_favoritos($data) {
      global $wpdb;
      $query = "SELECT * FROM `wp_favoritos`";
      $list = $wpdb->get_results($query);
      return $list;
    }

    /**
	*   Insere na tabela wp_favoritos os dados do post definido pelo ID na requisição
	*   metodo = '$_POST'
	*   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/add/{id}
	*/
	function api_add_posts_favoritos($request) {
	  // You can access parameters via direct array access on the object:
	  //$param = $request['id'];

	  // Or via the helper method:
	  $id = $request->get_param( 'id' );

	    // retorna os dados do usuário logado
	  $current_user = wp_get_current_user();
	    //passamos o ID do usuário e geramos o array
	  $user_info = get_userdata($current_user->ID);
	    //Criamos uma variavel com os dados que desejamos
	  $user_login = $user_info->user_login;
	  $user_id = $user_info->ID;

	  $items = array();
	  
	  $post = get_post($id);

	  $items[] = array(
	    'id' => $post->ID,
	    'title' => $post->post_title,
	    'author' => get_the_author_meta( 'display_name', $post->post_author ),
	    'content' => apply_filters( 'the_content', $post->post_content ),
	    'teaser' => $post->post_excerpt,
	    'guid' => $post->guid
	  );

	  global $wpdb;
	  $wpdb->insert( 
	    'wp_favoritos', 
	    array( 
	      'post_id'     => $post->ID,
	      'post_title'  => $post->post_title,
	      'guid'        => $post->guid,
	      'user_id'     => (int) $user_id,
	      'user_login'  => $user_login
	    )
	  );
	    //$record_id = $wpdb->insert_id;
	  return $items;
	}

	/**
	*   Deleta na tabela wp_favoritos os dados do post definido pelo ID na requisição
	*   metodo = '$_DELETE'
	*   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/del/{id}
	*/
	function api_del_posts_favoritos(WP_REST_Request $request) {
	  // You can access parameters via direct array access on the object:
	  //$param = $request['id'];

	  // Or via the helper method:
	  $id = $request->get_param('id');

	  // retorna os dados do usuário logado
	  $current_user = wp_get_current_user();
	  //passamos o ID do usuário e geramos o array
	  $user_info = get_userdata($current_user->ID);
	  
	  $user_login = $user_info->user_login;
	  $user_id = $user_info->ID;

	  $items = array();
	  
	  $post = get_post($id);

	  $items[] = array(
	    'id' 		=> $post->ID,
	    'title' 	=> $post->post_title,
	    'author' 	=> get_the_author_meta( 'display_name', $post->post_author ),
	    'content' 	=> apply_filters( 'the_content', $post->post_content ),
	    'teaser' 	=> $post->post_excerpt,
	    'guid' 		=> $post->guid
	  );

	  global $wpdb;
	  $wpdb->delete( 
	    'wp_favoritos', 
	    array( 
	      'post_id'     => $post->ID,
	      'user_id'     => (int) $user_id
	    )
	  );
	    //$record_id = $wpdb->insert_id;
	  return $items;
	}
 
    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code() {
 
        $status = 401;
 
        if ( is_user_logged_in() ) {
            $status = 403;
        }
 
        return $status;
    }
}
 
// Function to register our new routes from the controller.
function prefix_register_my_rest_routes() {
    $controller = new ApiController();
    $controller->register_routes();
}
 
add_action( 'rest_api_init', 'prefix_register_my_rest_routes' );