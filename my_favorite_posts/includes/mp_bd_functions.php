<?php

// CRIAR UMA TABELA PARA POSTS FAVORITOS
function table_install()
{
    global $wpdb;

    $table_name = $wpdb->prefix . "favorite_posts";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        id_user int(11) NULL,
        id_post int(11) NULL,
        created_at DATETIME NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// INSERIR POST NA TABELA
function insert_data($data)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'favorite_posts';

    $search = $wpdb->get_results("SELECT * FROM $table_name WHERE id_user = " . @$data['id_user'] . " AND id_post = " . @$data['id_post']);

    if (empty($search)) {

        $insert = $wpdb->insert(
            $table_name,
            array(
                'id_user' => $data['id_user'],
                'id_post' => $data['id_post'],
                'created_at' => current_time('mysql'),
            )
        );

        if ($insert) {
            return [
                'status' => 'success',
                'message' => 'Post adicionado aos favoritos',
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Erro ao favoritar post',
            ];
        }
    } else {
        return [
            'status' => 'error',
            'message' => 'Este post já está favoritado',
        ];
    }
}

// REMOVER FAVORITO
function mp_remove_favorite_post($data)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'favorite_posts';

    $args = array(
        'id' => $data['id']
    );

    if ($wpdb->delete($table_name, $args)) {
        return [
            'status' => 'success',
            'message' => 'Post removido dos favoritos com sucesso',
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Erro ao remover post dos favoritos',
        ];
    }
}

// VERIFICAR SE POST JÁ ESTÁ FAVORITADO
function mp_is_favorited($id_user, $id_post)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'favorite_posts';

    $search = $wpdb->get_row("SELECT * FROM $table_name WHERE id_user = " . @$id_user . " AND id_post = " . @$id_post);

    if (empty($search)) {
        return false;
    } else {
        return $search->id;
    }
}

// LISTAR POSTS FAVORITOS
function read_favorites()
{
    global $wpdb;

    $table_name = $wpdb->prefix . "favorite_posts";

    $result = $wpdb->get_results("SELECT * FROM $table_name");

    if (!empty($result)) {
        echo "<h1>Meus Posts Favoritos</h1>";

        $mp_url_base = get_site_url();
        foreach ($result as $row) {
            echo "<div class='mp-row'>";
            $post = mp_post_data($row->id_post);
            echo "<div class='mp-col-md-6'><a href='" . @$post['guid'] . "'>" . @$post['post_title'] . "</a></div>";
            echo '<div class="mp-col-md-6"><button type="button" class="mp_permalink mp_permalink_danger" value="' . $mp_url_base . '" onclick="mp_unfavorite(this.value,' . $row->id . ')">Desfavoritar Post</button></div>';
            echo "</div>";
        }
    }
}

// COUNT FAVORITES
function mp_count_favorites()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "favorite_posts";

    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    return $count;
}

function mp_post_data($id)
{
    global $wpdb;

    $tableName = $wpdb->prefix . "posts";

    $result = $wpdb->get_row("SELECT * FROM $tableName WHERE id = $id", ARRAY_A);

    return $result;
}
