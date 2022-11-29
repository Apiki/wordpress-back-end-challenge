<?php

/**
 * Core Class
 *
 * endpoint.
 *
 * @package    MPF
 * @subpackage MPF/includes
 * @since      0.1.0
 * @author     Bruno Lima
 *
 */

class MPF {

	/**
	 * O loader responsável por manter e registrar todos os hooks
	 * 
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      mpf_Loader    $loader 
	 */
	protected $loader;

	/**
	 * O identificador exclusivo deste plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name  A string usada para identificar exclusivamente este plug-in.
	 */
	protected $plugin_name;

	/**
	 * A versão atual da API
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The Option Prefix for API Meu post favorito.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $option_name    The option prefix for API Meu post favorito.
	 */
	protected $option_name;

	/**
	 * Define the core functionality of API Meu post favorito.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {

		$this->plugin_name        = 'api-mpf';
		$this->version            = '0.1.0';
		$this->option_name        = 'Meupostfavorito_';

		$this->load_dependencies();
		$this->set_locale();
		$this->create_endpoint();
		
		if(is_admin()):
			$this->define_admin_hooks();
		elseif ('post' != get_post_type()):
			$this->define_front_hooks();
		endif;

		$this->create_database();
	}

	/**
	 * Load the required dependencies for API Meu post favorito.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - mpf_Loader. Orchestrates the hooks of the plugin.
	 * - mpf_i18n. Defines internationalization functionality.
	 * - mpf_Front. Defines all hooks for the front area.
	 * - mpf_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mpf-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mpf-i18n.php';

		/**
		 * The class responsible for creating the custom endpoint for API Meu post favorito.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/interfaces/interface-mpf-endpoint.php';
		

		/**
		 * 
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/service/class-likepost-service.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/controllers/class-mpf-likepost.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/endpoints/class-mpf-endpoint-likepost.php';
		

		/**
		 * A classe responsável por definir todas as ações que ocorrem na área de administração.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mpf-admin.php';

		/**
		 * A classe responsável por definir todas as ações que ocorrem no front-end
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'front/class-mpf-front.php';

		$this->loader = new mpf_Loader();

	}

	/**
	 * Define a localidade do plugin para criar a internacionalização.
	 *
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new mpf_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Definindo o endpoint
	 *
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function create_endpoint() {
		$statistics_service    = new MPF_LikePost_Service( new MPF_LikePost() );
		$endpoints = new MPF_Endpoint_LikePost( $statistics_service);

		$this->loader->add_action( 'rest_api_init', $endpoints, 'register' );

	}

	/**
	 * Criando as tabelas
	 *
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function create_database() {
			global $wpdb;
			global $wp_version;

			$charset_collate = $wpdb->get_charset_collate();
			$table_name = esc_sql( $wpdb->prefix . MPF_LIKEPOSTS );
			
			//
			if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
				$sql = "CREATE TABLE " . $table_name . " (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				post_id bigint unsigned NOT NULL DEFAULT 0,
				id_user bigint unsigned NOT NULL DEFAULT '0',
                created_on timestamp default current_timestamp                            
            ) $charset_collate;";
				$wpdb->query( $sql );
			}
			$error = $wpdb->last_error;
			$query = $wpdb->last_query;

			if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
				echo $error . '<br><code>' . $query . '</code>';
			}			
	}

	/**
	 * Registra todos hooks no administrador
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new mpf_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_option_name() );

		// Adicionando ao menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );

		// Registrando
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		
		// Styles e Scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );		
	}


	
	/**
	 * Registra todos hooks no front-end
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_front_hooks() {

		$plugin_admin = new mpf_Front( $this->get_plugin_name(), $this->get_version(), $this->get_option_name() );
		
		// Styles e Scripts
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		
		//HTML like hook into post single
		$this->loader->add_action( 'the_content', $plugin_admin, 'html_like_post' );

		//get Current User ID
		$this->loader->add_action( 'init', $plugin_admin, 'getCurrentUserID' );
		
		
	}

	
	/**
	 * Executa todos os hooks
	 *
	 * @since    0.1.0
	 */
	public function run() {

		$this->loader->run();

	}

	/**
	 * A identificação do plug-in
	 *
	 * @since     0.1
	 * @return    string    O nome do plugin.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;

	}

	/**
	 * A referência à classe que controla os hooks com o plug-in.
	 *
	 * @since     0.1
	 * @return    mpf_Loader    Controla os hooks do plug-in.
	 */
	public function get_loader() {

		return $this->loader;

	}

	/**
	 * Obter o número da versão da API
	 *
	 * @since     0.1
	 * @return    string    O número da versão do plug-in.
	 */
	public function get_version() {

		return $this->version;

	}

	/**
	 * Obter o prefixo
	 *
	 * @since     0.1
	 * @return    string  
	 */
	public function get_option_name() {

		return $this->option_name;

	}
}
