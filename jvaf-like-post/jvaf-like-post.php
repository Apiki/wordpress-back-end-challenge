<?php
/**
 * @package JvafLikePost
 */
/*
    Plugin Name: Posts para Curtir
    Description: Um plugin que permite usuários logados curtir posts do Wordpress
    Version: 1.0
    Author: Johan Alves
    Author URI: https://johanalves.com/
    License: GPLv2 or later
    Text Domain: jvaf-lp-plugin
*/

if (!defined('ABSPATH')) exit;

if( !class_exists('JvafLikePost')){

    //Define CONSTANTS
    if ( !defined('JVAFLP_PLUGIN_DIR')) {
        define('JVAFLP_PLUGIN_DIR', plugin_dir_url( __FILE__ ));
    }
    if ( !defined('JVAFLP_PLUGIN_DIR_PATH')) {
        define('JVAFLP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ));
    }

    //enqueue admin scripts
    if (is_admin()){
        require_once JVAFLP_PLUGIN_DIR_PATH . 'inc/admin.php';
    }

    class JvafLikePost {
        function __construct(){
            global $wpdb;
            $this->charset = $wpdb->get_charset_collate();
            $this->table_name = $wpdb->prefix . "like_post"; 
        }

        function register() {
            //Plugin actions
            add_action('wp_enqueue_scripts', array($this, 'loadAssets'));


            //Ajax actions
            add_action( 'wp_ajax_jvaflp_like_button_ajax', array( $this, 'jvaflp_like_button_ajax' ) ); 
            add_action( 'wp_ajax_nopriv_jvaflp_like_button_ajax', array( $this, 'jvaflp_like_button_ajax' ) );

            //Edit post content
            add_filter('the_content', array($this, 'addLikeButton'));
        }

        function isPostLiked($post_id, $user_id){
            global $wpdb;
            $query = 'SELECT * from ' . $this->table_name . ' WHERE like_post_id = "'.$post_id .'" AND like_user_id = "'.$user_id.'"';
            $isPostLiked = $wpdb->get_results($query);
            if ($isPostLiked == null) return false;
            return true;
        }

        function getTotalPostLikes($post_id){
            global $wpdb;
            $query = 'SELECT * from ' . $this->table_name . ' WHERE like_post_id = "'.$post_id .'"';
            $totalPostLikes = sizeof($wpdb->get_results($query));
            return $totalPostLikes;
        }

        function likePost($post_id, $user_id){
            global $wpdb;
            $wpdb->insert($this->table_name, array(
                'like_post_id' => $post_id,
                'like_user_id' => $user_id
            ));
        }

        function unlikePost($post_id, $user_id){
            global $wpdb;
            $likeId = $this->getLikeId($post_id, $user_id);
            $wpdb->delete($this->table_name, array(
                'like_id' => $likeId,
            ));
            return ;
        }

        function getLikeId($post_id, $user_id){
            global $wpdb;
            $query = 'SELECT * from ' . $this->table_name . ' WHERE like_post_id = "'.$post_id .'" AND like_user_id = "'.$user_id.'"';
            $isPostLiked = $wpdb->get_results($query);
            if ($isPostLiked == null) return -1;
            return $isPostLiked[0]->like_id;
        }

        function jvaflp_like_button_ajax(){
            if (is_user_logged_in()){
                $post_id = $_POST['post_id'];
                $user_id = $_POST['user_id']; 
                $is_liked = $this->isPostLiked($post_id, $user_id);
                $total_likes = $this->getTotalPostLikes($post_id);
                $msg = "";
        
                if(!$is_liked){
        
                    $this->likePost($post_id, $user_id);

                    $is_liked = $this->isPostLiked($post_id, $user_id);
                    $total_likes = $this->getTotalPostLikes($post_id);

                    $msg = $total_likes;

                }else{
                    $this->unlikePost($post_id, $user_id);

                    $is_liked = $this->isPostLiked($post_id, $user_id);
                    
                    $msg = "Curtir";
                }

                wp_send_json_success( array(
                    'msg' => $msg,
                    'is_liked' => $is_liked,
                ));

            }else{
                wp_send_json_error( "Usuário não está logado" );
            }

        }

        function loadAssets(){    
            wp_enqueue_style('jvaflp_css', JVAFLP_PLUGIN_DIR . 'assets/css/like-post.css');
            wp_enqueue_style('font-awesome_css', JVAFLP_PLUGIN_DIR . 'assets/font-awesome/css/all.min.css');
            wp_enqueue_script('jvaflp-ajax', JVAFLP_PLUGIN_DIR. 'assets/js/like-post-ajax.js', array('jquery'), '1.0', true);
            wp_localize_script( 'jvaflp-ajax', 'jvaflp_ajax_url', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'post_id' => get_the_ID(),
                'user_id' => get_current_user_id(),
                'login_url' => wp_login_url()
            ));
        }

        function addLikeButton($content){
            if (is_single() && is_main_query()){
                $post_id = get_the_ID();
                $user_id = get_current_user_id();
                $is_liked = $this->isPostLiked($post_id, $user_id);
                $total_likes = $this->getTotalPostLikes($post_id); 
                $heartImage = JVAFLP_PLUGIN_DIR_PATH . 'assets/img/like-icon.svg';        
                
                if (!$is_liked) $likeButton = '<p><span id="likeButton" onclick="jvaflp_handleAjax()" class="likeButton"><i id="likeButtonIcon" class="fa-solid fa-heart"></i><span id="likeButtonMsg">Curtir</span></span></p>';
                else $likeButton = '<p><span id="likeButton" onclick="jvaflp_handleAjax()" class="likeButton liked"><i id="likeButtonIcon" class="fa-solid fa-heart"></i><span id="likeButtonMsg">' . $total_likes . '</span></span></p>';

                switch(get_option('jvaflp_location','0')){
                    case '0':
                        return $likeButton . $content;
                        break;
                    case '1':
                        return $content . $likeButton;
                        break;
                }
                
            }
            return $content;        
        }

        
    }

    $jvafLikePost = new JvafLikePost();
    $jvafLikePost->register();
}

//activation
require_once JVAFLP_PLUGIN_DIR_PATH . 'inc/jvaf-like-post-activate.php';
register_activation_hook( __FILE__, array( 'JvafLikePostActivate', 'onActivate' ));

//deactivation
require_once JVAFLP_PLUGIN_DIR_PATH . 'inc/jvaf-like-post-deactivate.php';
register_deactivation_hook( __FILE__, array( 'JvafLikePostDeactivate', 'onDeactivate' ));