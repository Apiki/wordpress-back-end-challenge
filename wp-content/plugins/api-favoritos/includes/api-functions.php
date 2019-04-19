<?php

/**
*  API sem orientação a objetos
*  Alexandre S. Anjos
*  https://github.com/AlexandreSA/wordpress-back-end-challenge/tree/alexandre-anjos
*/


/*
*  Cria as rotas para API [endpoints]
*/
add_action( 'rest_api_init', 'api_register_api_endpoints' );


/*
*  Endpoints da API
*/
function api_register_api_endpoints() {
  register_rest_route( 'api/v2', '/posts', array(
    'methods' => 'GET',
    'callback' => 'api_get_post_items',
  ) );
  register_rest_route( 'api/v2', '/posts/favoritos', array(
    'methods' => 'GET',
    'callback' => 'api_get_posts_favoritos',
    /*'permission_callback' => function () {
      return current_user_can( 'edit_others_posts' );
    }*/
  ) );
  register_rest_route( 'api/v2', '/posts/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'api_get_post_items_byid'
  ) );
  register_rest_route( 'api/v2', '/posts/add/(?P<id>\d+)', array(
    'methods' => 'POST',
    'callback' => 'api_add_posts_favoritos'
  ) );
  register_rest_route( 'api/v2', '/posts/del/(?P<id>\d+)', array(
    'methods' => 'DELETE',
    'callback' => 'api_del_posts_favoritos'
  ) );
}


/**
*   Busca os posts publicados e retorna os valores definidos no array
*   metodo = '$_GET'
*   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/
*/
function api_get_post_items() {
  $args = array (
    'post_status' => 'publish'
  );

  $items = array();

  if ( $posts = get_posts( $args ) ) {
    foreach ( $posts as $post ) {
      $items[] = array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'author' => get_the_author_meta( 'display_name', $post->post_author ),
        'content' => apply_filters( 'the_content', $post->post_content ),
        'teaser' => $post->post_excerpt,
        'guid' => $post->guid
      );
    }
  }
  return $items;
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
*   Busca o post pelo ID definido na requisição
*   metodo = '$_GET'
*   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/
*/
function api_get_post_items_byid(WP_REST_Request $request) {
  // You can access parameters via direct array access on the object:
  //$param = $request['id'];

  // Or via the helper method:
  $id = $request->get_param('id');

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

  return $items;
}

/**
*   Insere na tabela wp_favoritos os dados do post definido pelo ID na requisição
*   metodo = '$_POST'
*   url = http://localhost/wordpress-back-end-challenge/wp-json/api/v2/posts/add/{id}
*/
function api_add_posts_favoritos(WP_REST_Request $request) {
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

