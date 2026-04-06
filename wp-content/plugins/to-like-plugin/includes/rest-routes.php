<?php

defined('ABSPATH') || exit;

/**
 * Check if the current user is authenticated.
 *
 * @return bool
 */
function authentication_for_favorite(): bool
{
	return is_user_logged_in();
}

/**
 * Register plugin REST routes.
 *
 * @return void
 */
function register_rest_routes(): void
{
	register_rest_route('to-like-plugin/v1', '/favorites', [
		[
			'methods'             => 'POST',
			'callback'            => 'create_favorite',
			'permission_callback' => 'authentication_for_favorite',
		],
		[
			'methods'             => 'GET',
			'callback'            => 'list_favorites',
			'permission_callback' => 'authentication_for_favorite',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'delete_favorite',
			'permission_callback' => 'authentication_for_favorite',
			'args'                => [
				'post_id' => [
					'required'          => true,
					'validate_callback' => function ($value) {
						return is_numeric($value) && (int) $value > 0;
					},
				],
			],
		],
	]);
}

add_action('rest_api_init', 'register_rest_routes');

/**
 * Create a favorite for the authenticated user.
 *
 * @param WP_REST_Request $request Request instance.
 *
 * @return array|WP_Error
 */
function create_favorite(WP_REST_Request $request)
{
	$user_id = get_current_user_id();
	$post_id = (int) $request->get_param('post_id');

	if (!$post_id) {
		return new WP_Error(
			'tlp_missing_post_id',
			'post_id is required.',
			['status' => 400]
		);
	}

	$post = get_post($post_id);

	if (!$post) {
		return new WP_Error(
			'tlp_post_not_found',
			'Post not found.',
			['status' => 404]
		);
	}

	$added = To_Like_Plugin::add_favorite($user_id, $post_id);

	if (!$added) {
		return new WP_Error(
			'tlp_favorite_not_created',
			'Favorite already exists or could not be added.',
			['status' => 409]
		);
	}

	return [
		'success' => true,
		'message' => 'Favorite added successfully.',
		'data'    => [
			'user_id' => $user_id,
			'post_id' => $post_id,
		],
	];
}

/**
 * List favorites for the authenticated user.
 *
 * @return array
 */
function list_favorites(): array
{
	$user_id = get_current_user_id();
	$favorites = To_Like_Plugin::get_user_favorites($user_id);

	return [
		'success' => true,
		'data'    => [
			'user_id'   => $user_id,
			'favorites' => $favorites,
		],
	];
}

/**
 * Remove a favorite for the authenticated user.
 *
 * @param WP_REST_Request $request Request instance.
 *
 * @return array|WP_Error
 */
function delete_favorite(WP_REST_Request $request)
{
	$user_id = get_current_user_id();
	$post_id = (int) $request->get_param('post_id');

	if (!$post_id) {
		return new WP_Error(
			'tlp_missing_post_id',
			'post_id is required.',
			['status' => 400]
		);
	}

	$post = get_post($post_id);

	if (!$post) {
		return new WP_Error(
			'tlp_post_not_found',
			'Post not found.',
			['status' => 404]
		);
	}

	$removed = To_Like_Plugin::remove_favorite($user_id, $post_id);

	if (!$removed) {
		return new WP_Error(
			'tlp_favorite_not_removed',
			'Favorite not found or could not be removed.',
			['status' => 404]
		);
	}

	return [
		'success' => true,
		'message' => 'Favorite removed successfully.',
		'data'    => [
			'user_id' => $user_id,
			'post_id' => $post_id,
		],
	];
}
