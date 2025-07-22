<?php

class Favorite_Posts {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'favorite-posts';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_rest_api_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-favorite-posts-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-favorite-posts-database.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-favorite-posts-rest-controller.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-favorite-posts-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-favorite-posts-admin.php';

        $this->loader = new Favorite_Posts_Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new Favorite_Posts_Admin( $this->get_plugin_name(), $this->get_version() );

        $post_types = get_post_types( array( 'public' => true ), 'names' );
        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ) ) ) {
                continue;
            }
            $this->loader->add_filter( "manage_{$post_type}_posts_columns", $plugin_admin, 'add_favorites_column' );
            $this->loader->add_action( "manage_{$post_type}_posts_custom_column", $plugin_admin, 'display_favorites_column_content', 10, 2 );
            $this->loader->add_filter( "manage_edit-{$post_type}_sortable_columns", $plugin_admin, 'register_favorites_sortable_column' );
            $this->loader->add_action( 'pre_get_posts', $plugin_admin, 'custom_favorites_column_orderby' );
        }

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_deactivation_modal_scripts' );
    }

    private function define_public_hooks() {
        $plugin_public = new Favorite_Posts_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'init', $plugin_public, 'register_favorite_button_shortcode' );
    }

    private function define_rest_api_hooks() {
        $database = new Favorite_Posts_Database();
        $rest_controller = new Favorite_Posts_REST_Controller( $database );
        $this->loader->add_action( 'rest_api_init', $rest_controller, 'register_routes' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}