<?php
/**
 * Admin facing Elements of API Meu post favorito
 *
 * @package    MPF
 * @subpackage MPF/admin
 * @link       #
 * @since      0.1.0
 * @author     Bruno Lima
 *
 */

/**
 * The admin-facing functionality of API Meu post favorito.
 *
 * Add the Options Page to select sections and enqueues
 * the stylesheets and JavaScripts.
 *
 * @package    MPF
 * @subpackage MPF/admin
 * @since      0.1.0
 * @author     Bruno Lima
 *
 */
class mpf_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options name prefix for API Meu post favorito
	 *
	 * @since  	0.1.2
	 * @access 	private
	 * @var  		string 		$option_name 		Option name prefix for API Meu post favorito
	 */
	private $option_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param 	 string 	$plugin_name 				The name of this plugin.
	 * @param    string    	$version    				The version of this plugin.
	 * @param    string    	$option_name   				The option prefix for this plugin.
	 *
	 */
	public function __construct( $plugin_name, $version, $option_name ) {

		$this->plugin_name        = $plugin_name;
		$this->version            = $version;
		$this->option_name        = $option_name;

	}

	/**
	 * Add an options page under the Settings submenu
	 *
	 * @since  0.1
	 */
	public function add_options_page() {

		$this->plugin_options_page = add_options_page(
			__( 'Meu post favorito', 'api-mpf' ),
			__( 'Meu post favorito', 'api-mpf' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);

	}

	/**
	 * Render the options page for API Meu post favorito
	 *
	 * @since  0.1
	 */
	public function display_options_page() {

		include_once 'partials/api-mpf-admin-display.php';

	}

	/**
	 * Register the admin page settings
	 *
	 * @since  0.1.2
	 */
	public function register_settings() {

		add_settings_section(
			$this->option_name . 'example',
			__( 'Example Section', 'api-mpf' ),
			'',
			$this->plugin_name
		);

		add_settings_field(
			$this->option_name . 'key',
			__( 'Example Field', 'api-mpf' ),
			array( $this, $this->option_name . 'example_output' ),
			$this->plugin_name,
			$this->option_name . 'example',
			array( 'label_for' => $this->option_name . 'key' )
		);

		register_setting( $this->plugin_name, $this->option_name . 'key', $this->option_name . 'validate_sections' );
	}



	/**
	 * Register the stylesheets for the front area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {	
		$screen = get_current_screen();
		
		// Only enqueue our files to the options page
		if ( 'settings_page_api-mpf' === $screen->base ) {

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'dist/css/api-mlc-admin.min.css', array(), $this->version, 'all' );

		}
	}
}
