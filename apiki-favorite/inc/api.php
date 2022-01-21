<?php
function favoritesPost($data)
{
	$post_id = $data['post_id'];
	$user_id = $data['logged_user'];

	if (!empty($post_id) && !empty($user_id)) {
		global $wpdb;

		$table = $wpdb->prefix . "apiki_favorites";
		$query = $wpdb->prepare("SELECT * FROM `$table` WHERE post_id = %d AND user_id = %d", array(
			$post_id,
			$user_id
		));
		$aFavoritePost = $wpdb->query($query);

		if (!$aFavoritePost) {
			$table = $wpdb->prefix . "apiki_favorites";
			$wpdb->insert($table, array(
				'post_id' => $post_id,
				'user_id' => $user_id
			));
		}
	}
}

function cancelFavoritePost($data)
{
	$post_id = $data['post_id'];
	$user_id = $data['logged_user'];

	if (!empty($post_id) && !empty($user_id)) {
		global $wpdb;
		$table = $wpdb->prefix . "apiki_favorites";
		$wpdb->delete($table, array(
			'post_id' => $post_id,
			'user_id' => $user_id
		));
	}
}