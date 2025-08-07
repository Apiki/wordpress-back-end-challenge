<?php

/**
 * Página de administração dos favoritos
 * PHP version 8.1 
 * 
 * @category Wpbackendchallenge
 * @package  WordPress_Back_End_Challenge
 * @author   Gustavo Dias <gustavodiasdsc@gmail.com>
 * @license  GPL v2 or later
 * @link     https://github.com/gustavodscruz/wordpress-back-end-challenge
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Favoritos dos Usuários', 'wp_backend_challenge'); ?></h1>

    <div class="wpb-admin-stats">
        <div class="wpb-stat-card">
            <h3><?php _e('Total de Favoritos', 'wp_backend_challenge'); ?></h3>
            <p class="wpb-stat-number"><?php echo count($favorites); ?></p>
        </div>

        <div class="wpb-stat-card">
            <h3><?php _e('Posts Mais Favoritados', 'wp_backend_challenge'); ?></h3>
            <?php
            // Contar posts mais favoritados
            $post_counts = array();
            foreach ($favorites as $favorite) {
                if (!isset($post_counts[$favorite->post_id])) {
                    $post_counts[$favorite->post_id] = array(
                        'title' => $favorite->post_title,
                        'count' => 0
                    );
                }
                $post_counts[$favorite->post_id]['count']++;
            }

            // Ordenar por contagem
            uasort(
                $post_counts,
                function ($a, $b) {
                    return $b['count'] - $a['count'];
                }
            );

            $top_posts = array_slice($post_counts, 0, 3, true);
            ?>

            <?php if (!empty($top_posts)) : ?>
                <ul class="wpb-top-posts">
                    <?php foreach ($top_posts as $post_id => $data): ?>
                        <li>
                            <a href="<?php echo get_edit_post_link($post_id); ?>">
                                <?php echo esc_html($data['title']); ?>
                            </a>
                            <span class="count">
                                (<?php echo $data['count']; ?>)
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>
                    <?php _e(
                        'Nenhum post favoritado ainda.', 
                        'wp_backend_challenge'
                    ); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($favorites)) : ?>
        <div class="notice notice-info">
            <p><?php _e('Nenhum favorito encontrado.', 'wp_backend_challenge'); ?></p>
        </div>
    <?php else: ?>
        <h2><?php _e('Lista de Favoritos', 'wp_backend_challenge'); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Usuário', 'wp_backend_challenge'); ?></th>
                    <th scope="col"><?php _e('Post', 'wp_backend_challenge'); ?></th>
                    <th scope="col"><?php _e('Data', 'wp_backend_challenge'); ?></th>
                    <th scope="col"><?php _e('Ações', 'wp_backend_challenge'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favorites as $favorite): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo get_edit_user_link($favorite->user_id); ?>">
                                    <?php echo esc_html($favorite->display_name); ?>
                                </a>
                            </strong>
                            <br>
                            <small>ID: <?php echo $favorite->user_id; ?></small>
                        </td>
                        <td>
                            <strong>
                                <a href="<?php echo get_edit_post_link($favorite->post_id); ?>">
                                    <?php echo esc_html($favorite->post_title); ?>
                                </a>
                            </strong>
                            <br>
                            <small>
                                <a href="<?php echo get_permalink($favorite->post_id); ?>" target="_blank">
                                    <?php _e('Ver post', 'wp_backend_challenge'); ?>
                                </a>
                            </small>
                        </td>
                        <td>
                            <?php echo date_i18n(
                                get_option('date_format') 
                                . ' ' . get_option('time_format'),
                                strtotime($favorite->date_favorited)
                            ); ?>
                        </td>
                        <td>
                            <a 
                            href="<?php echo wp_nonce_url(
                                admin_url(
                                    'admin.php?page=wpb-favorites&action=delete&id=' 
                                    . $favorite->id
                                ),
                                'delete_favorite_' 
                                . $favorite->id 
                            ); ?>"
                                class="button button-small button-link-delete"
                                onclick="return confirm('<?php _e('Tem certeza que deseja remover este favorito?', 'wp_backend_challenge'); ?>');">
                                <?php _e('Remover', 'wp_backend_challenge'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .wpb-admin-stats {
        display: flex;
        gap: 20px;
        margin: 20px 0;
    }

    .wpb-stat-card {
        background: white;
        border: 1px solid #ccd0d4;
        border-left: 4px solid #0073aa;
        padding: 20px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        flex: 1;
    }

    .wpb-stat-card h3 {
        margin-top: 0;
        color: #23282d;
    }

    .wpb-stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #0073aa;
        margin: 10px 0;
    }

    .wpb-top-posts {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .wpb-top-posts li {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }

    .wpb-top-posts li:last-child {
        border-bottom: none;
    }

    .wpb-top-posts .count {
        color: #666;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .wpb-admin-stats {
            flex-direction: column;
        }
    }
</style>