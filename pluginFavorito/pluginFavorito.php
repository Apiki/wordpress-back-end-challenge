<?php

/*
Plugin Name: Meu Plugin de Favoritos
Description: Implementa a funcionalidade de favoritar posts para usuários logados usando a WP REST API.
Version: 1.0
Author: Clayton Santos Cordeiro
Author URI: https://testepluginfavoritos.com
*/

// Função para criar a tabela de favoritos

function criar_tabela_favoritos() {
    global $wpdb;
    $post_favoritos = $wpdb->prefix . 'favoritos';

    // Verifica se a tabela já existe a tabela no banco de dados

    if ($wpdb->get_var("SHOW TABLES LIKE '$post_favoritos'") != $post_favoritos) {

        // Cria a tabela no banco de dados

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $post_favoritos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            post_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            deleted_at DATETIME
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'criar_tabela_favoritos');

// Adicionando as rotas da API REST

function adicionar_rotas_api() {
    register_rest_route('meu-plugin-favoritos/v1', '/favoritos/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'adicionar_favorito',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route('meu-plugin-favoritos/v1', '/favoritos/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'remover_favorito',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));
}

add_action('rest_api_init', 'adicionar_rotas_api');

// Função para adicionar um favorito

function adicionar_favorito($request) {
    $user_id = get_current_user_id();
    $post_id = $request['id'];
    $created_at = current_time('mysql');

    global $wpdb;
    $post_favoritos = $wpdb->prefix . 'favoritos';

    // Verifica se o favorito já existe
    $favorito_existe = $wpdb->get_var($wpdb->prepare("SELECT id FROM $post_favoritos WHERE post_id = %d AND user_id = %d", $post_id, $user_id));

    if ($favorito_existe) {
        return new WP_Error('favorito_ja_existente', 'Este post já foi favoritado.', array('status' => 400));
    }

    // Insere o favorito na tabela com a data de criação
    $wpdb->insert(
        $post_favoritos,
        array(
            'post_id' => $post_id,
            'user_id' => $user_id,
            'created_at' => $created_at
        ),
        array(
            '%d',
            '%d',
            '%s'
        )
    );

    return array('message' => 'Post favoritado com sucesso!');
}

// Função para remover dos favoritos

function remover_favorito($request) {
    $user_id = get_current_user_id();
    $post_id = $request['id'];
    $deleted_at = current_time('mysql');

    global $wpdb;
    $post_favoritos = $wpdb->prefix . 'favoritos';

    // Verifica se o favorito existe
    $favorito_existe = $wpdb->get_var($wpdb->prepare("SELECT id FROM $post_favoritos WHERE post_id = %d AND user_id = %d", $post_id, $user_id));

    if (!$favorito_existe) {
        return new WP_Error('favorito_nao_encontrado', 'O post não está favoritado.', array('status' => 400));
    }

    // Atualiza a coluna de data de exclusão para registrar a data atual
    $wpdb->update(
        $post_favoritos,
        array(
            'deleted_at' => $deleted_at
        ),
        array(
            'post_id' => $post_id,
            'user_id' => $user_id
        ),
        array(
            '%s'
        ),
        array(
            '%d',
            '%d'
        )
    );

    return array('message' => 'Post removido dos favoritos com sucesso!');
}