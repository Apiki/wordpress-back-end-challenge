<?php
/**
 * Plugin Name: WordPress Back-end Challenge
 * Description: Desafio para os futuros programadores back-end em WordPress da Apiki.
 * Version: 1.0
 * Author: Lucas Dzin Pedroso
 */

// Make sure we don't expose any info if called directly
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

register_activation_hook( __FILE__, array('WBEC', 'plugin_activation'));
register_deactivation_hook( __FILE__, array('WBEC', 'plugin_deactivation'));
add_action( 'plugins_loaded', array( 'WBEC', 'init' ) );

class WBEC {

    public static function init()
    {
        self::init_hooks();
    }

    public static function init_hooks() {
        add_action( 'wp_enqueue_scripts', array('WBEC', 'wbec_like_scripts'));
        add_action( 'wp_ajax_nopriv_wbec_like_func', array('WBEC', 'wbec_login'));
        add_action( 'wp_ajax_wbec_like_func', array('WBEC', 'wbec_like_func'));
        add_filter( 'the_content', array('WBEC', 'wbec_like_button_html'));
    }

	public static function plugin_activation() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wbec';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY (user_id, post_id)
        ) $charset_collate;";

        $wpdb->query($sql);
	}

	public static function plugin_deactivation() {
		
    }

    public static function wbec_like_scripts() {
        if( is_single() && is_user_logged_in()) {             
            if (!wp_script_is( 'jquery', 'enqueued' )) {
                wp_enqueue_script( 'jquery' );
            }
            
            wp_enqueue_script( 'wbec-like', trailingslashit( plugin_dir_url( __FILE__ ) ).'js/wbec-like.js', array('jquery'), '1.0', true );
     
            wp_localize_script( 'wbec-like', 'wbeclike', array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            ));
        }
    }

    public static function wbec_like_func() {
        if ( !wp_verify_nonce( $_POST['nonce'], "user_like_nonce")) {
            exit("Invalid nonce!");
        }
        
        global $wpdb;
    
        $user_id = get_current_user_id();
        $post_id = $_POST['post_id'];
        $table_name = $wpdb->prefix . 'wbec';
        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id = $post_id AND user_id = $user_id");
        
        if(empty($results)){
            $insert = $wpdb->insert($table_name, array(
                'post_id' => $post_id,
                'user_id' => $user_id
                )
            );
            if($insert){
                $result['type'] = "success";
                $result['text'] = "Desfavoritar";
            }else{
                $result['type'] = "error";
            }
        }else{
            $insert = $wpdb->delete($table_name, array(
                'post_id' => $post_id,
                'user_id' => $user_id
                )
            );
            if($insert){
                $result['type'] = "success";
                $result['text'] = "Favoritar";
            }else{
                $result['type'] = "error";
            }
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }
    
        die();
    }

    public static function wbec_like_button_html( $content ) {
        if(is_single() && is_user_logged_in()) {
            $nonce = wp_create_nonce("user_like_nonce");

            global $wpdb, $post;
            
            $user_id = get_current_user_id();
            $table_name = $wpdb->prefix . 'wbec';
            $results = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id = $post->ID AND user_id = $user_id");
            $text_like_button = empty($results) ? "Favoritar" : "Desfavoritar";
            $like_button = '<div class="wbec_like_button">
                                <button id="like-button" type="button" data-nonce="' . $nonce . '" data-post-id="' . $post->ID . '">' . $text_like_button .'</button>
                            </div>';
            
            return $content . $like_button;
        }else{
            return $content;
        }
    }

    public static function wbec_login() {
        echo "You must log in!";
        die();
     }
  
    public function __construct()
    {
    
    }
}
