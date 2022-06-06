<?php
class Favorite_Apiki {
    public static function init()
    {
        global $posts;

        wp_enqueue_style('ssfcss', '/wp-content/plugins/favorite-post-apiki-challenge/assets/style/style.css');

        if(count($posts) > 1) {
            wp_enqueue_script('smfjs', '/wp-content/plugins/favorite-post-apiki-challenge/assets/scripts/multiple-favorites.js', array( 'jquery' ));
        }
    }
    public static function favorite_unfavorite_post($data)
    {
        try {
            global $wpdb;
            $post = str_replace('post-', '', $data['post']);
            if(!get_post($post)) {
               wp_send_json_error(['error' => 'Post não encontrado'], 400);
               exit;
            }

            if(!get_user_by('ID', $data['user'])) {
                wp_send_json_error(['error' => 'Usuário não encontrado'], 400);
                exit;
            }

            $actual_state = $wpdb->get_results("SELECT * FROM wp_favorite_post WHERE id_user = {$data['user']} AND id_post = {$post}",  ARRAY_A);

            if(empty($actual_state)) {
                $wpdb->insert( 'wp_favorite_post', [
                    'id_user' => $data['user'],
                    'id_post' => $post,
                    'st_favorite' => 'ati',
                ]);
            } else {
                $actual_state = array_shift($actual_state);
                $wpdb->update( 'wp_favorite_post', ['st_favorite' => $actual_state['st_favorite'] == 'ati' ? 'ina' : 'ati'], ['id_favorite' => $actual_state['id_favorite']]);
            }
            wp_send_json_error(['status' => '00', 'data' => $actual_state['st_favorite'] == 'ati' ? 'ina' : 'ati']);
        } catch (Exception $e) {
            wp_send_json_error(['status' => '01'], 500);
        }
    }

    public function ajax_check_user_logged_in()
    {
        if(is_user_logged_in()) {
            wp_send_json_success(['user' => get_current_user_id()]);
        } else {
            wp_send_json_error();
        }
    }


    public static function check_favorite_post($data)
    {
        try {
            global $wpdb;
            $post = str_replace('post-', '', $data['post']);
            if(!get_post($post)) {
                wp_send_json_error(['error' => 'Post não encontrado'], 400);
                exit;
            }

            if(!get_user_by('ID', $data['user'])) {
                wp_send_json_error(['error' => 'Usuário não encontrado'], 400);
                exit;
            }

            $actual_state = $wpdb->get_results("SELECT * FROM wp_favorite_post WHERE id_user = {$data['user']} AND id_post = {$post} AND st_favorite = 'ati'",  ARRAY_A);

            if(empty($actual_state)) {
                wp_send_json_success(['status' => '01']);
            } else {
                wp_send_json_success(['status' => '00']);
            }
        } catch (Exception $e) {
            wp_send_json_error(['status' => '02'], 500);
        }
    }
}
