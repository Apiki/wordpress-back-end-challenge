<?php if (is_user_logged_in()): ?>
    <button class="like-button <?php echo $has_liked ? 'liked' : ''; ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
        <?php echo $has_liked ? 'Desfavoritar' : 'Favoritar' ;?>
    </button>
<?php else: ?>
    <p>Você precisa estar logado para curtir este post. 
        <a href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>">Clique aqui para fazer login</a>.
    </p>
<?php endif; ?>
