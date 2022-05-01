<?php 

function posts_post($request){

  $post_id = sanitize_text_field($request['post_id']);
  $user_id = sanitize_text_field($request['user_id']);
  $status  = sanitize_text_field($request['status']);
  
  $request = array(
    'id' => $post_id.$user_id,
    'post_id' => $post_id,
    'user_id' => $user_id,
    'status'  => $status
  );


  $like = new LikeData;
  $like->like($request['id'], $request['post_id'], $request['user_id'], $request['status']); 
	
  $message = $like->get_message(); 
      
  return rest_ensure_response($message);
}

function posts_post_page(){
  register_rest_route('api/v1', '/post', array(
    array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => 'posts_post',
    ),    
  ));
}

add_action('rest_api_init', 'posts_post_page');


?>