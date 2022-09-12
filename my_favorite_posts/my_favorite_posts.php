<?php

/**
 * Plugin name: My Favorite Posts
 * Description: Adicione e remova seus posts favoritos
 * Version: 1.0
 * Author: Pedro Henrique da Silva
 * Author email: pedrohenriquedasilva100@yahoo.com.br
 * Author uri: https://henrik-phs.github.io
 * License: GPLv2 or later
 */

// IMPORTA ARQUIVOS DE FUNÇÕES
require_once plugin_dir_path(__FILE__) . "includes/mp_functions.php";
require_once plugin_dir_path(__FILE__) . "includes/mp_bd_functions.php";
require_once plugin_dir_path(__FILE__) . "includes/mp_api_functions.php";

// INSTALA TABELA NO BANCO DE DADOS
register_activation_hook(__FILE__, 'table_install');

// ADICIONAR FOLHA DE ESTILOS DO PLUGIN
// Admin Dashboard 
add_action('admin_head', 'insert_css_in_head');

// Front End 
add_action('wp_head', 'insert_css_in_head');

// ADICIONAR FOLHA DE ESTILOS DO PLUGIN
// Admin Dashboard 
add_action('admin_footer', 'insert_script_in_footer');

// Front End 
add_action('wp_footer', 'insert_script_in_footer');

// ADICIONA UM LINK PARA PÁGINA DO PLUGIN AO MENU DO PAINEL DE ADMINISTRAÇÃO
add_action('admin_menu', 'mp_add_my_admin_link');

// ADICIONA UM BOTÃO PARA FAVORITAR POST NO FINAL DE CADA POST
add_action('the_content', 'mp_add_btn_favorite');

// ADICIONA UM LINK POST ROW ACTIONS PARA FAVORITAR UM POST NO PAINEL DE EDIÇÃO DOS POSTS
add_filter('post_row_actions', 'mp_favorite_post_link', 10, 2);

// ADICIONA UM LINK VIEWS EDIT POST PARA VISUALIZAR OS POSTS FAVORITOS NO PAINEL DE EDIÇÃO DOS POSTS
add_filter('views_edit-post', 'mp_view_favorites_link');
