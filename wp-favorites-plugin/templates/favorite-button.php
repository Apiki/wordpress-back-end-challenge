<?php
/**
 * Template for favorite button
 *
 * @package WP_Favorites_Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get post ID
$post_id = isset($args['post_id']) ? $args['post_id'] : get_the_ID();
$show_count = isset($args['show_count']) ? $args['show_count'] : false;
$button_text = isset($args['button_text']) ? $args['button_text'] : __('Favorite', 'wp-favorites-plugin');

// Check if user is logged in
if (!is_user_logged_in()) {
    $login_url = wp_login_url(get_permalink($post_id));
    ?>
    <a href="<?php echo esc_url($login_url); ?>" class="wp-favorites-button">
        <span class="wp-favorites-icon">♡</span>
        <span class="wp-favorites-text"><?php echo esc_html($button_text); ?></span>
    </a>
    <?php
    return;
}

// Get favorite count if requested
$favorite_count = 0;
if ($show_count) {
    $favorite_count = WP_Favorites_Helpers::get_favorite_count($post_id);
}

// Check if post is already favorited
$is_favorited = WP_Favorites_Helpers::is_post_favorited($post_id);
$button_class = 'wp-favorites-button';
$icon = '♡';
$text = $button_text;

if ($is_favorited) {
    $button_class .= ' favorited';
    $icon = '♥';
    $text = __('Favorited', 'wp-favorites-plugin');
}
?>

<button class="<?php echo esc_attr($button_class); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    <span class="wp-favorites-icon"><?php echo $icon; ?></span>
    <span class="wp-favorites-text"><?php echo esc_html($text); ?></span>
    <?php if ($show_count && $favorite_count > 0): ?>
        <span class="wp-favorites-count"><?php echo esc_html($favorite_count); ?></span>
    <?php endif; ?>
</button> 