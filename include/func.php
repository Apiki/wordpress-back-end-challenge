<?php

function botao_fav($content)
{
      if (!is_user_logged_in() || is_page()){
        return $content;
      }

	global $wpdb;
	global $post;
	global $current_user;

	$curtido = $wpdb->get_var("SELECT post_id FROM ".$wpdb->prefix."fav_post WHERE post_id = ".$post->ID." AND user_id = ".$current_user->ID);
	if ($curtido)
	{
		$stat = "Curtir Post";
	}

	else
	{
		$stat = "Descurtir";
	}


	$btn = file_get_contents('lays/botao.html', true);
	$btn = str_replace('{text}',$stat, $btn);
	$btn = str_replace('{id}',$post->ID,$btn);
	$content .= $btn;

	return $content;


}

function fav_click()
{
   	global $wpdb;
    global $current_user;

    $post_id = intval($_POST['id']);
    $user_id = $current_user->ID;
    $favorito = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."fav_post WHERE post_id = ".$post_id." AND user_id = ".$user_id);
    $sts = 0;

    if (!$favorito)
    {
      $wpdb->insert(
        $wpdb->prefix.'fav_post',
        array(
          'post_id' => $post_id,
          'user_id' => $user_id
      )
    );

    $sts = 1;
    wp_send_json_success($sts);
  }

  else
  {
    $wpdb->delete(
      $wpdb->prefix.'fav_post', 
      array (
        'post_id'=>$post_id,
        'user_id'=>$user_id
		)
  	);

    $sts = 0;
    wp_send_json_success($sts);
  }


}
