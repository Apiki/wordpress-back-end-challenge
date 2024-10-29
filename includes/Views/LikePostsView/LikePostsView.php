<?php

namespace LikePosts\Views\LikePostsView;

use LikePosts\Controllers\LikePostsController;

class LikePostsView
{
    public function render($post_id)
    {
        $like = new LikePostsController();
        $has_liked = $like->has_liked_post(get_current_user_id(), $post_id);
        include plugin_dir_path(__FILE__) . 'template.php';
    }
}
