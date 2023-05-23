  <?php
  /*
  Plugin Name: Favoritador de Posts
  Description: Permite aos usuários logados favoritar posts.
  Version: 1.0
  Author: Leonardo Marzeuski
  Author URI: github/leomarzeuski
  */

  if ( ! defined( 'ABSPATH' ) ) {
      exit; 
  }
  register_activation_hook(__FILE__, 'create_favorite_posts_table');

  function create_favorite_posts_table() {
      global $wpdb;

      $charset_collate = $wpdb->get_charset_collate();
      $table_name = $wpdb->prefix . 'favorite_posts';

      $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          user_id mediumint(9) NOT NULL,
          post_id mediumint(9) NOT NULL,
          PRIMARY KEY  (id)
      ) $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
  }
  add_action( 'rest_api_init', function () {
    register_rest_route( 'favorites/v1', '/posts/(?P<id>\d+)', array(
      'methods' => 'POST',
      'callback' => 'favorite_post',
    ) );

    register_rest_route( 'favorites/v1', '/posts/(?P<id>\d+)', array(
      'methods' => 'DELETE',
      'callback' => 'unfavorite_post',
    ) );
  } );

  function favorite_post( $data ) {
    global $wpdb;

    if ( !is_user_logged_in() ) {
      return new WP_Error( 'not_logged_in', 'Você precisa estar logado para favoritar um post', array( 'status' => 401 ) );
    }

   
    $table_name = $wpdb->prefix . 'favoritos';

    $result = $wpdb->insert( $table_name, array( 'user_id' => get_current_user_id(), 'post_id' => $data['id'] ) );


    if ( $result ) {
      return array( 'success' => true );
    } else {
      return new WP_Error( 'database_error', 'Não foi possível favoritar o post', array( 'status' => 500 ) );
    }
  }

  function unfavorite_post( $data ) {
    global $wpdb;

   
    if ( !is_user_logged_in() ) {
      return new WP_Error( 'not_logged_in', 'Você precisa estar logado para desfavoritar um post', array( 'status' => 401 ) );
    }

  
    $table_name = $wpdb->prefix . 'favoritos';
    $result = $wpdb->delete( $table_name, array( 'user_id' => get_current_user_id(), 'post_id' => $data['id'] ) );

   
    if ( $result ) {
      return array( 'success' => true );
    } else {
      return new WP_Error( 'database_error', 'Não foi possível desfavoritar o post', array( 'status' => 500 ) );
    }
  }

  add_filter( 'the_content', 'add_favorite_button_to_content' );

  function add_favorite_button_to_content( $content ) {
      if ( is_singular( 'post' ) ) {
          $post_id = get_the_ID();

         
          $content .= '<button class="favorite-button" data-post-id="' . $post_id . '">Favoritar</button>';
          $content .= '<button class="unfavorite-button" data-post-id="' . $post_id . '">Desfavoritar</button>';
      }
      
      return $content;
  }

  add_action( 'wp_enqueue_scripts', 'add_favorite_button_script' );

  function add_favorite_button_script() {
      wp_enqueue_script( 'favorite-button-script', plugins_url( 'meu-script.js', __FILE__ ), array( 'jquery' ), '1.0', true );

    
      wp_localize_script( 'favorite-button-script', 'FavoriteButton', array(
          'root' => esc_url_raw( rest_url() ),
          'nonce' => wp_create_nonce( 'wp_rest' ),
      ) );
  }
