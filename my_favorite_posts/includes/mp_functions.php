<?php

// ADICIONAR FOLHA DE ESTILOS DO PLUGIN
function insert_css_in_head()
{
    echo "<link rel='stylesheet' href='" . plugins_url("/my_favorite_posts/assets/css/mp_style.css") . "'>";
}

function insert_script_in_footer()
{
    echo "<div class='mp_loading_body'><div class='mp_loading'></div></div>";
    echo "<script src='" . plugins_url("/my_favorite_posts/assets/js/mp_scripts.js") . "'></script>";
}

// ADICIONA UM LINK PARA PÁGINA DO PLUGIN AO MENU DO PAINEL DE ADMINISTRAÇÃO
function mp_add_my_admin_link()
{
    add_menu_page(
        'Meus Posts Favoritos', // Title of the page
        'My Favorite Posts', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'my_favorite_posts/includes/mp-page.php' // The 'slug' - file to display when clicking the link
    );
}

// ADICIONA UM BOTÃO PARA FAVORITAR POST NO FINAL DE CADA POST
function mp_add_btn_favorite($content)
{

    if (is_singular() && in_the_loop() && is_main_query()) {

        global $current_user, $post;

        if (is_user_logged_in()) {

            // VERIFICA SE ESSE POST FOI FAVORITADO PELO USUÁRIO
            $is_favorited = mp_is_favorited($current_user->ID, $post->ID);

            $mp_url_base = get_site_url();

            if ($is_favorited) {

                // SE TIVER SIDO FAVORITADO, APARECE BOTÃO COM OPÇÃO DE DESFAVORITAR
                $msg = "
                <button class='mp_btn_fav' value='" . $mp_url_base . "' onclick='mp_unfavorite(this.value," . $is_favorited . ")'>
                    <img src='" . plugins_url("/my_favorite_posts/assets/imgs/star.svg") . "' width='17px'> Desfavoritar
                </button>
            ";
            } else {

                // SE NÃO TIVER FAVORITADO, APARECE BOTÃO PARA FAVORITAR
                $msg = "
                <button class='mp_btn_fav' value='" . $mp_url_base . "' onclick='mp_favorite(this.value," . $current_user->ID . "," . $post->ID . ")'>
                    <img src='" . plugins_url("/my_favorite_posts/assets/imgs/star-outline.svg") . "' width='17px'> Favoritar
                </button>
            ";
            }
        }

        return $content . "<b>$msg</b>";
    }

    return $content;
}

// ADICIONA UM LINK POST ROW ACTIONS PARA FAVORITAR UM POST NO PAINEL DE EDIÇÃO DOS POSTS
function mp_favorite_post_link($actions, $post)
{
    global $current_user;
    if ($post->post_type == 'post') {
        // $actions['view'] = "<a href='" . get_site_url() . "/wp-json/api/add_favorite_post/$current_user->ID/$post->ID' title='' rel='permalink'>Favoritar Post</a>";
        $mp_url_base = get_site_url();
        $id_user = $current_user->ID;
        $id_post = $post->ID;

        $is_favorited = mp_is_favorited($id_user, $id_post);
        if ($is_favorited)
            $actions['view'] = '<button type="button" class="mp_permalink mp_permalink_danger" value="' . $mp_url_base . '" onclick="mp_unfavorite(this.value,' . $is_favorited . ')">Desfavoritar Post</button>';
        else
            $actions['view'] = '<button type="button" class="mp_permalink mp_permalink_primary" value="' . $mp_url_base . '" onclick="mp_favorite(this.value,' . $id_user . ',' . $id_post . ')">Favoritar Post</button>';
    }
    return $actions;
}

// ADICIONA UM LINK VIEWS EDIT POST PARA VISUALIZAR OS POSTS FAVORITOS NO PAINEL DE EDIÇÃO DOS POSTS
function mp_view_favorites_link($views)
{
    $views['publish'] = '<a href="admin.php?page=my_favorite_posts%2Fincludes%2Fmp-page.php" title="" rel="permalink">Favoritos (' . mp_count_favorites() . ')</a>';

    return $views;
}
