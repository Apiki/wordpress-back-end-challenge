<?php

function af_enqueue_scripts()
{
	wp_register_style('af_style', plugins_url('/style/main.css', AF_PLUGIN_URL));
	wp_enqueue_style('af_style');

	wp_register_script('af_script', plugins_url('/js/main.js', AF_PLUGIN_URL), array('jquery'), '1.0', true);
	wp_enqueue_script('af_script');

	wp_localize_script('af_script', 'afwp_data', array(
		'home_url' => home_url('/'),
		'logged_user' => get_current_user_id(),
		'nonce' => wp_create_nonce('wp_rest'),
	));
}