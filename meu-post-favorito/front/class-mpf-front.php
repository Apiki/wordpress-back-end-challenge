<?php
/**
 * 'Front facing Elements
 *
 * @package    MPF
 * @subpackage MPF/front
 * @link       #
 * @since      0.1.0
 * @author     Bruno Lima
 *
 */

/**
 * The front-facing functionality.
 *
 * Add the Options Page to select sections and enqueues
 * the stylesheets and JavaScripts.
 *
 * @package    MPF
 * @subpackage MPF/front
 * @since      0.1.0
 * @author     Bruno Lima
 *
 */
class mpf_Front {

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
	 * The options name prefix for API 
	 *
	 * @since  	0.1.2
	 * @access 	private
	 * @var  		string 		$option_name 		Option name prefix for API 
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
	 * 
	 * Trás o botão de like no front
	 * @since  0.1
	 */
	public function html_like_post($content){
		global $post;
		if(is_single()){
			$id_user = get_option( 'id_user' );
			$plugin_front = new MPF_LikePost();
			
			$class = $plugin_front->check_like_post_on_list($id_user, $post->ID);

			return '<div class="button-like"><button class="like-cta-button'.$class.'" data-postid="'.$post->ID.'"data-siteurl="'.get_site_url().'">Favoritar</button></div><br>'. $content;

		}
		return $content;
	}

	public function getCurrentUserID(){
		global $user_ID;
		$user_ID = get_current_user_id();
		update_option( 'id_user', $user_ID);
	}

	/**
	 * Register the stylesheets for the front area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {		
		if ( is_single() ) { 
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'dist/js/api-mpf-front.js', array( 'jquery' ) );

			wp_localize_script( $this->plugin_name, 'like_posts', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			));	

			wp_enqueue_script( $this->plugin_name);

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'dist/css/api-mpf-front.css', array(), $this->version, 'all' );
		}
	}
}
