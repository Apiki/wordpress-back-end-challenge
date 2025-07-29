<?php

class FavoriteController {
    // Método para favoritar ou desfavoritar um post
    public static function toggle($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $post_id = $request->get_param('post_id');
        $table = $wpdb->prefix . 'post_favorites';

        // Verifica se o post já está favoritado pelo usuário
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ));

        if ($exists) {
            // Se já existe, remove (desfavorita)
            $wpdb->delete($table, ['user_id' => $user_id, 'post_id' => $post_id]);
            return ['status' => 'removed'];
        } else {
            // Se não existe, insere (favorita)
            $wpdb->insert($table, ['user_id' => $user_id, 'post_id' => $post_id]);
            return ['status' => 'added'];
        }
    }

    // Método para listar os posts favoritados pelo usuário logado
    public static function list($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'post_favorites';

        // Busca todos os IDs dos posts favoritados pelo usuário
        $favorites = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM $table WHERE user_id = %d",
            $user_id
        ));

        return $favorites;
    }
}
