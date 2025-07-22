<?php

class Favorite_Posts_Admin {

    private $plugin_name;
    private $version;
    private $database;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->database = new Favorite_Posts_Database();
    }

    public function add_favorites_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( 'title' === $key ) {
                $new_columns['favorite_count'] = 'Favoritos';
            }
        }
        return $new_columns;
    }

    public function display_favorites_column_content( $column, $post_id ) {
        if ( 'favorite_count' === $column ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'favorite_posts';
            $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d", $post_id ) );
            echo $count;
        }
    }

    public function register_favorites_sortable_column( $columns ) {
        $columns['favorite_count'] = 'favorite_count_numeric';
        return $columns;
    }

    public function custom_favorites_column_orderby( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( 'favorite_count_numeric' === $orderby ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'favorite_posts';
            $query->set( 'orderby', "(SELECT COUNT(fp.post_id) FROM {$table_name} AS fp WHERE fp.post_id = {$wpdb->posts}.ID) " . $query->get( 'order' ) );
        }
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'Posts Favoritos - Configurações',
            'Posts Favoritos',
            'manage_options',
            $this->plugin_name . '_settings',
            array( $this, 'display_plugin_setup_page' ),
            'dashicons-star-filled',
            60
        );
    }

    public function display_plugin_setup_page() {
        ?>
        <div class="wrap">
            <h1>Posts Favoritos - Configurações</h1>

            <h2>Como usar o botão de Favoritar</h2>
            <p>Para exibir o botão "Favoritar" em qualquer lugar do seu site, utilize o shortcode <code>[favorite_button]</code>.</p>

            <h3>Opções do Shortcode:</h3>
            <ul>
                <li><strong><code>[favorite_button]</code></strong>: Insira este shortcode no conteúdo de qualquer post, página ou Custom Post Type (CPT). Ele exibirá o botão de favoritar para o item que está sendo visualizado no momento.</li>
                <li><strong><code>[favorite_button post_id="ID_DO_POST"]</code></strong>: Use esta variação se você quiser que o botão favorite um post específico, independentemente de onde o shortcode está sendo usado. Substitua <code>ID_DO_POST</code> pelo número do ID do post que deseja favoritar.</li>
            </ul>

            <p>O botão só será exibido para usuários logados.</p>
        </div>
        <?php
    }

    public function enqueue_deactivation_modal_scripts( $hook_suffix ) {
        if ( 'plugins.php' === $hook_suffix ) {
            wp_enqueue_style(
                'sweetalert2',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
                array(),
                '11.0.0',
                'all'
            );

            wp_enqueue_script(
                'sweetalert2',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
                array(),
                '11.0.0',
                true
            );

            wp_enqueue_script(
                $this->plugin_name . '-deactivation-modal',
                plugin_dir_url( __FILE__ ) . '../public/js/favorite-posts-admin-deactivate-modal.js',
                array( 'jquery', 'sweetalert2' ),
                $this->version,
                true
            );

            wp_localize_script(
                $this->plugin_name . '-deactivation-modal',
                'favoritePostsDeactivationData',
                array(
                    'plugin_slug'      => $this->plugin_name,
                    'modal_title'      => 'Desativar Posts Favoritos?',
                    'modal_text'       => 'Você deseja manter os dados de posts favoritos no banco de dados?',
                    'confirm_text'     => 'Sim, manter dados',
                    'cancel_text'      => 'Não, excluir tudo',
                    'deny_text'        => 'Cancelar desativação',
                    'keep_data_value'  => 'no',
                    'delete_data_value' => 'yes',
                    'is_rtl'           => is_rtl(),
                )
            );
        }
    }
}