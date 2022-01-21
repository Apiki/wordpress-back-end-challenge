<?php
function set_endpoints(): void
{
	register_rest_route('apiki/favorites-post', '/(?P<post_id>\d+)/(?P<logged_user>\d+)', array(
    'callback' => 'favoritesPost',
    'permission_callback' => function () {
      return current_user_can( 'edit_others_posts' );
    }
  ));

  register_rest_route('apiki/cancel-favorite-post', '/(?P<post_id>\d+)/(?P<logged_user>\d+)', array(
    'callback' => 'cancelFavoritePost',
    'permission_callback' => function () {
      return current_user_can( 'edit_others_posts' );
    }
  ));
}