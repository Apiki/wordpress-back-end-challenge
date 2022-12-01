<?php

//admin actions

class JvafLikePost_Admin{
    function createAdmin(){
        add_action('admin_menu', array($this,'adminPage'));
        add_action('admin_init', array($this,'settings'));
    }

    /* Settings functions */

    function adminPage(){
        add_menu_page('Configurações de Posts para Curtir ','Posts para Curtir', 'manage_options', 'jvaf_like_post', array($this,'adminHTML'),'dashicons-heart');
    }

    function settings(){
        add_settings_section('jlp_first_section', null, null, 'jvaf_like_post_settings_page');

        add_settings_field('jvaflp_location', 'Localização', array($this, 'locationHTML'), 'jvaf_like_post_settings_page', 'jlp_first_section');
        register_setting('jvaflikepost','jvaflp_location',array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));
    }

    function locationHTML(){ 
        require_once JVAFLP_PLUGIN_DIR_PATH . 'templates/admin-field-location.php';
        
    }

    function adminHTML(){ 
        require_once JVAFLP_PLUGIN_DIR_PATH . 'templates/admin-page.php';
    }
}

$jvafLikePost_Admin = new JvafLikePost_Admin();
$jvafLikePost_Admin->createAdmin();

