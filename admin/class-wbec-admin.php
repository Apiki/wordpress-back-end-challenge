<?php
class WBEC_Admin {

	public static function init() {
		add_action( 'admin_menu', 'WBEC_Admin::wbec_add_menu'  );
		add_action('admin_enqueue_scripts', 'WBEC_Admin::wbec_load_scripts');		
	}

	public static function wbec_add_menu() {
		add_menu_page( 'Favoritar Posts', 'Favoritar Posts', 'manage_options', 'wordpress-back-end-challenge', 'WBEC_Admin::wbec_startview', 'dashicons-star-filled' );
	}

	public static function wbec_load_scripts() {
		wp_enqueue_script( 'wbec_js', plugins_url( 'js/admin.js', __FILE__ ), array('jquery'), rand(0,500), true );
		wp_localize_script( 'wbec_js', 'wbec_var',
			array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
		);
	}

	public static function wbec_startview() {

		/*Buscar favoritos*/
		$request = new WP_REST_Request( 'GET', '/wbec/v1/favs' );
		$response = rest_do_request( $request );
		$data = rest_get_server()->response_to_data( $response, true );
		$favs = json_decode($data);

		/*Buscar Posts*/
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = rest_do_request( $request );
		$data = rest_get_server()->response_to_data( $response, true );

		include_once(WBEC_WP_PATH.'admin/partials/wbec-start-view.php');
	}

}
WBEC_Admin::init();