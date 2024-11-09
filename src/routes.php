<?php

if (!defined("ABSPATH")) exit;


require_once(PLUGIN_DIR . "src/db_utils.php");



function wpfav_add_bookmark($request) {
    if (!$request->has_param("id")) {
        wp_send_json_error([ "error" => "Missing post id" ], 400);
        return;
    }

    $id = $request->get_param("id");
    $user_id = get_current_user_id();

    if (!get_post($id)) {
        wp_send_json_error([ "error" => "Post not found" ], 404);
        return;
    }

    Database::addBookmark($user_id, $id);

    wp_send_json_success();
}



function wpfav_remove_bookmark($request) {
    if (!$request->has_param("id")) {
        wp_send_json_error([ "error" => "Missing post id" ], 400);
        return;
    }

    $id = $request->get_param("id");
    $user_id = get_current_user_id();

    if (!get_post($id)) {
        wp_send_json_error([ "error" => "Post not found" ], 404);
        return;
    }

    Database::removeBookmark($user_id, $id);

    wp_send_json_success();
}



function wpfav_is_bookmarked($request) {
    if (!$request->has_param("id")) {
        wp_send_json_error([ "error" => "Missing post id" ], 400);
        return;
    }

    $id = $request->get_param("id");
    $user_id = get_current_user_id();

    if (!get_post($id)) {
        wp_send_json_error([ "error" => "Post not found" ], 404);
        return;
    }

    $is_bookmarked = Database::isBookmarked($user_id, $id);

    wp_send_json_success([ "is_bookmarked" => $is_bookmarked ]);
}



function wpfav_get_bookmarks($request) {
    $user_id = get_current_user_id();

    $bookmarks = Database::getBookmarks($user_id);

    wp_send_json_success([ "bookmarks" => $bookmarks ]);
}