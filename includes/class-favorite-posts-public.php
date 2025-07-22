<?php

class Favorite_Posts_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_scripts() {
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

        wp_enqueue_style(
            'font-awesome-free',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
            array(),
            '6.5.2',
            'all'
        );

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . '../public/css/favorite-posts-public.css',
            array(),
            $this->version,
            'all'
        );

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . '../public/js/favorite-posts-public.js',
            array( 'jquery', 'sweetalert2' ),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'favoritePostsData',
            array(
                'root'             => esc_url_raw( rest_url() ),
                'nonce'            => wp_create_nonce( 'wp_rest' ),
                'namespace'        => 'favorite-posts/v1',
                'is_user_logged_in' => is_user_logged_in(),
                'i18n'             => array(
                    'favorite'          => 'Favoritar',
                    'unfavorite'        => 'Desfavoritar',
                    'success_title'     => 'Sucesso!',
                    'error_title'       => 'Erro!',
                    'confirm_button'    => 'Ok',
                    'login_required_title' => 'Atenção!',
                    'login_required_text' => 'Para favoritar posts, você precisa estar logado. Que tal criar uma conta ou fazer login agora?', // Nova string
                ),
            )
        );
    }

    public function register_favorite_button_shortcode() {
        add_shortcode( 'favorite_button', array( $this, 'favorite_button_shortcode' ) );
    }

    public function favorite_button_shortcode( $atts ) {
        global $post;

        $atts = shortcode_atts( array(
            'post_id' => 0,
        ), $atts, 'favorite_button' );

        $post_id = (int) $atts['post_id'];

        if ( $post_id === 0 && is_singular() && isset( $post->ID ) ) {
            $post_id = $post->ID;
        }

        if ( $post_id === 0 ) {
            return '';
        }

        $is_logged_in = is_user_logged_in();
        $button_classes = 'favorite-post-button';
        $wrapper_classes = 'favorite-post-wrapper';
        $data_attributes = 'data-post-id="' . esc_attr( $post_id ) . '"';

        if ( ! $is_logged_in ) {
            $button_classes .= ' is-logged-out';
            $wrapper_classes .= ' is-logged-out-wrapper';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $wrapper_classes ); ?>">
            <button class="<?php echo esc_attr( $button_classes ); ?>" <?php echo $data_attributes; ?>>
                <i class="fa-regular fa-heart favorite-icon-outline"></i>
                <i class="fa-solid fa-heart favorite-icon-filled" style="display:none;"></i>
            </button>
            <?php if ( ! $is_logged_in ) : ?>
                <span class="favorite-login-message">Faça login para favoritar!</span>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}