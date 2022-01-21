<?php

function af_insert_button($content) {
	if(!is_single() || !is_user_logged_in()) {
		return $content;
	}
	$user_id = get_current_user_id();

	global $post;
	global $wpdb;

	$table = $wpdb->prefix . "apiki_favorites";
	$query = $wpdb->prepare("SELECT * FROM `$table` WHERE post_id = %d AND user_id = %d", array(
		$post->ID,
		$user_id
	));
	$aFavoritePost = $wpdb->query($query);

	$favoriteButton = '<button id="favorite-button"';
	$favoriteButton .=  ($aFavoritePost) ? ' class="hide"' : '';
	$favoriteButton .= ' data-post-id="' . $post->ID . '">favoritar post</button>';

	$cancelFavoriteButton = '<button id="cancel-favorite-button"';
	$cancelFavoriteButton .=  (!$aFavoritePost) ? ' class="hide"' : '';
	$cancelFavoriteButton .= ' data-post-id="' . $post->ID . '">desfavoritar post</button>';

	$newContent = $favoriteButton;
	$newContent .= $cancelFavoriteButton;
	$newContent .= $content;

	return $newContent;
}