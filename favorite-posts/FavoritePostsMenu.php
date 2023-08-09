<?php

require_once(plugin_dir_path(__FILE__) . '/Database.php');

class FavoritePostsMenu
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'addFavoritedPostsSubmenu'));
    }

    public function addFavoritedPostsSubmenu()
    {
        add_submenu_page(
            'edit.php',
            'Favoritados',
            'Favoritados',
            'read',
            'favorited-posts',
            array($this, 'displayFavoritedPosts')
        );
    }

    public function displayFavoritedPosts()
    {
        $userId = get_current_user_id();
        $favoritedPosts = Database::getFavoritedPostsByUserId($userId);
        if (empty($favoritedPosts)) {
            echo '<p>Nenhuma postagem favoritada encontrada.</p>';
            return;
        }
        self::generateSimpleTable($favoritedPosts);
    }

    private static function generateSimpleTable($posts)
    {
        echo '<div class="wrap">';
        echo '<table class="wp-list-table widefat fixed striped table-view-list posts" style="padding: 32px">';
        echo '<tbody class="">';
        foreach ($posts as $post) {
            echo '<tr class="iedit author-self level-0 type-post status-publish format-standard hentry">';
            echo '<td class="title column-title has-row-actions column-primary page-title">';
            echo '<h3>' . get_the_title($post->post_id) . '</h3>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }
}
