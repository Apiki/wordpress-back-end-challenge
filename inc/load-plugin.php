<?php
function check_bookmarkposts($content) {
    if ( is_single() ) {
        global $wpdb;
        $post_id = get_the_ID();
        if( $post_id !== false ) {
            $data = $wpdb->get_row( "SELECT post_id FROM ".BOOKMARKPOSTS_TABLE." WHERE post_id = ".$post_id." AND user_id = ".BOOKMARKPOSTS_CURRENT_USERID );
            if($data) {
                $custom_content = '<button class="button_bookmarkposts marked" post-id="'.$post_id.'"></span>DESMARCAR FAVORITO</button>';
            } else {
                $custom_content = '<button class="button_bookmarkposts unmarked" post-id="'.$post_id.'"></span>MARCAR COMO FAVORITO</button>';
            }
            $custom_content .= $content;
        }
        return $custom_content;
    } else {
          return $content;
    }
}
add_action( 'the_content', 'check_bookmarkposts' );


function bookmarkposts_insertFooterHomeURL() {
	if(BOOKMARKPOSTS_CURRENT_USERID !== false) {
		echo  '<script>';
		echo  'homeURL = "'.esc_url(home_url( '/' )).'"';
		echo  '</script>';
	}
}
add_action('wp_footer', 'bookmarkposts_insertFooterHomeURL');
