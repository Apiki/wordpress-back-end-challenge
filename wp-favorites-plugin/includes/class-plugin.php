<?php
/**
 * Bootstrap do plugin: carrega dependências e registra hooks.
 *
 * @package WPFavoritesPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPFAV_Plugin {

    /**
     * Inicializa hooks do plugin.
     *
     * @return void
     */
    public function run(): void {
        $this->load_dependencies();

        
        add_action( 'plugins_loaded', [ 'WPFAV_Database', 'maybe_update' ] );
        add_action( 'rest_api_init', [ 'WPFAV_API', 'register_routes' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Carrega os arquivos de classes necessários.
     *
     * @return void
     */
    private function load_dependencies(): void {
        require_once WPFAV_PLUGIN_PATH . 'includes/class-api.php';
        require_once WPFAV_PLUGIN_PATH . 'includes/class-favorites.php';
    }

    /**
     * Registra e localiza o script JS do plugin.
     *
     * @return void
     */
    public function enqueue_scripts(): void {
        if ( ! is_user_logged_in() ) {
            return;
        }

        wp_enqueue_script(
            'wpfav-favorites',
            WPFAV_PLUGIN_URL . 'assets/js/favorites.js',
            [],
            WPFAV_VERSION,
            true
        );

        /*
         * wp_localize_script: passa dados do PHP para o JS de forma segura.
         */
        wp_localize_script(
            'wpfav-favorites',
            'wpFavSettings',
            [
                'apiUrl' => esc_url_raw( rest_url( 'wpfav/v1' ) ),
                'nonce'  => wp_create_nonce( 'wp_rest' ),
            ]
        );
    }
}
