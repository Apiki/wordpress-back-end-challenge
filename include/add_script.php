<?php

function include_js(){
    wp_register_script(
        'fav_script',
        plugins_url('/js/script.js', __FILE__),
        array('jquery'),
        '1.0',
        true
    );
        
    wp_enqueue_script('fav_script');

    wp_localize_script('fav_script', 'fav_obj', array(
    	'ajax_url'=> admin_url('admin-ajax.php')
    ));
}
