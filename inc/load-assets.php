<?php
function apki_bookmark_posts_assets_init() {
	if(!is_admin()) {
		wp_enqueue_style( 'apki-bookmark-posts-styles', BOOKMARKPOSTS_PLUGIN_URL . '/inc/assets/css/styles.css?'.time() );

		wp_register_script('apki-bookmark-posts-admin-js', BOOKMARKPOSTS_PLUGIN_URL.'/inc/assets/js/plugin.js?'.time(), array('jquery'), false, false);
		wp_enqueue_script( 'apki-bookmark-posts-admin-js' );
	}
}
add_action('init', 'apki_bookmark_posts_assets_init');
