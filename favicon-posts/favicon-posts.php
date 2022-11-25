<?php
/*
Plugin Name: Favorite Posts
Plugin URI: https://github.com/jonathanafranio/
Description: Favoritar posts no WP
Author: Jonathan Afranio
Author URI: https://github.com/jonathanafranio/
Text Domain: favicon-posts
License: GPLv2 or later
Version: 1.0.1
Domain Path: /languages
*/

$class_plugin_fav = 'plugin_processo_seletivo';
class plugin_processo_seletivo {
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $base_name = $wpdb->base_prefix . "favicons_posts";
        
        $sql = "
        CREATE TABLE IF NOT EXISTS $base_name (
            id bigint(20) unsigned NOT NULL auto_increment,
            user bigint(20) unsigned NOT NULL default 0,
            post bigint(20) unsigned NOT NULL default 0,
            PRIMARY KEY (id),
            FOREIGN KEY (post) REFERENCES wp_posts(ID),
            FOREIGN KEY (user) REFERENCES wp_users(ID)
        ) $charset_collate;
        ";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

    }
    
    public static function register_endpoints () {
        register_rest_route( 
            'favicon/v1', 
            '/add_favicon',
            array(
                'methods' => 'POST',
                'callback' => array( __CLASS__, 'add_favicon' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            'favicon/v1',
            '/rm_favicon',
            array(
                'methods' => 'POST',
                'callback' => array( __CLASS__, 'rm_favicon' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            'favicon/v1',
            '/check_post_favicon/',
            array(
                'methods' => 'GET',
                'callback' => array( __CLASS__, 'get_check_post' ),
                'permission_callback' => '__return_true',
            ),
        );
    }

    public static function get_check_post( $request ) {
        if ( ! isset($_GET['post'])) {
            return array( 'error' => 'Parametro post não especificado' );
        }
        if ( ! isset($_GET['user'])) {
            return array( 'error' => 'Parametro user não especificado' );
        }
        
        $post = $_GET['post'];
        if( ! is_numeric( $post ) ) {
            return array( 'error' => 'Formato errado para o parametro post.' );
        }
        $user = $_GET['user'];
        if( ! is_numeric( $user ) ) {
            return array( 'error' => 'Formato errado para o parametro user.' );
        }

        global $wpdb;
        $base_name = $wpdb->base_prefix . "favicons_posts";
        $has_favicon = $wpdb->get_results( "SELECT * FROM $base_name WHERE user = $user AND post = $post" );
        return array(
            'favorite' => count( $has_favicon ) < 1 ? FALSE : TRUE,
        );
    } 

    public static function get_is_logado( $request ) {
        $logado = array();
        if ( is_user_logged_in() ) {
            $logado = array(
                'logged' => true,
                'user_id' => wp_get_current_user()->ID,
            );
        }
        else { 
            $logado = array(
                'logged' => false
            );
        }
        return $logado;
    }

    public static function add_favicon( $request ) {
        global $wpdb;
        $base_name = $wpdb->base_prefix . "favicons_posts";
        
        $post = $request->get_param('post');
        $user = $request->get_param('user');

        if( ! $post || ! is_numeric( $post ) ) {
            return new WP_Error( 'invalid_post', 'Invalid Post', array( 'status' => 400 ) );
        }
        if( ! $user || ! is_numeric( $user ) ) {
            return new WP_Error( 'invalid_user', 'Invalid User', array( 'status' => 400 ) );
        }

        $post = intval( $post );
        $user = intval( $user );

        $has_post = $wpdb->get_results( "SELECT * FROM wp_posts WHERE ID = $post" );
        $has_user = $wpdb->get_results( "SELECT * FROM wp_users WHERE ID = $user" );

        if( count( $has_post ) < 1 ) {
            return new WP_Error( 'invalid_post', 'Invalid Post, Post not find.', array( 'status' => 400 ) );            
        }
        if( count( $has_user ) < 1 ) {
            return new WP_Error( 'invalid_user', 'Invalid User, User not find.', array( 'status' => 400 ) );            
        }

        $has_favicon = $wpdb->get_results( "SELECT * FROM $base_name WHERE user = $user AND post = $post" );

        if( count($has_favicon) > 0 ) {
            return new WP_Error( 'post_duplicate', 'Post já favoritado', array( 'status' => 400 ) );
        }

        $wpdb->insert( $base_name, array(
            'user' => $user,
            'post' => $post,
        ), array(
            '%d',
            '%d'
        ));
        
        $response = new WP_REST_Response( array( "message" => "Favorito adicionado", "id" => $wpdb->insert_id ));
        $response->set_status(200);
        return $response;
    }

    public static function rm_favicon( $request ) {
        global $wpdb;
        $base_name = $wpdb->base_prefix . "favicons_posts";

        $post = $request->get_param('post');
        $user = $request->get_param('user');

        if( ! $post || ! is_numeric( $post ) ) {
            return new WP_Error( 'invalid_post', 'Invalid Post', array( 'status' => 400 ) );
        }
        if( ! $user || ! is_numeric( $user ) ) {
            return new WP_Error( 'invalid_user', 'Invalid User', array( 'status' => 400 ) );
        }

        $post = intval( $post );
        $user = intval( $user );

        $has_favicon = $wpdb->get_results( "SELECT * FROM wp_favicons_posts WHERE user = $user AND post = $post" );

        if( count( $has_favicon ) > 0 ) {
            $id_favicon = intval( $has_favicon[0]->id );
            $query_remove = "DELETE FROM $base_name WHERE id IN ($id_favicon)";
            $wpdb->query( $wpdb->prepare($query_remove) );

            $response = new WP_REST_Response( array( "message" => "Favicon removido com sucesso." ));
            $response->set_status(200);
            return $response;
        } else {
            return new WP_Error( 'not_find', 'Favorito não encontrado', array( 'status' => 400 ) );
        }
    }

    public static function template_page_favicon( $page_template ) {
        if ( get_page_template_slug() == 'page-favicons.php' ) {
            $page_template = dirname( __FILE__ ) . '/page-favicons.php';
        }
        return $page_template;
    }

    public static function theme_page_favicon( $post_templates, $wp_theme, $post, $post_type ) {
        $post_templates['page-favicons.php'] = __('Favoritos');
        return $post_templates;
    }

    public static function theme_front_end () {
        if ( is_user_logged_in() ) {
            $user_id = wp_get_current_user()->ID;
            $baseRequestURL = get_bloginfo('url');

            if( is_page_template( 'page-favicons.php' ) ) {
                echo "<script type='text/javascript'>/* <![CDATA[ */ 
                    var favicon_plugin_obj = {
                        'user_id': " . $user_id . ", 'base_url_req': '" . $baseRequestURL ."/wp-json/favicon/v1'
                    };  /* ]]> */
                </script>";
            }

            if( is_singular( 'post' ) ) {
                $post_id = get_the_ID();
                echo "<script type='text/javascript'>/* <![CDATA[ */
                var favicon_plugin_obj = { 'user_id': " . $user_id . ", 'base_url_req': '" . $baseRequestURL ."/wp-json/favicon/v1', 'post_id': " . $post_id . " };
                /* ]]> */
                </script>";

            }
    
            if( is_page_template( 'page-favicons.php' ) || is_singular( 'post' ) ) {
                wp_enqueue_script( 'favicon_script', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.min.js', array(), false, 'true'  );
            }
        }
        wp_enqueue_style( 'favicons_css', plugin_dir_url( __FILE__ ) . 'assets/css/main.css' );
    }

    public static function create_menu_admin () {
        add_menu_page( 'Favoritos de cada usuário', 'Favoritos por usuário', 'edit_theme_options', 'favicons_user', array( __CLASS__, 'users_list_favicon' ), 'dashicons-star-filled' );
    }

    public static function users_list_favicon () {
        global $wpdb;
        $get_user = '';
        $user_name = '';
        $favicons = array();
        if (isset($_GET['user'])) {
            $get_user = intval( $_GET['user'] );

            $base_name = $wpdb->base_prefix . "favicons_posts";
            $favicons = $wpdb->get_results("SELECT post FROM $base_name WHERE user = $get_user");
            $aux_favicons = array();
            foreach( $favicons as $favicon ) {
                $post = get_posts(
                    array( 'post__in' => array( $favicon->post ) )
                );
                $aux_favicons[] = array(
                    'id'      => $post[0]->ID,
                    'title'   => $post[0]->post_title,
                    'link'    => get_the_permalink( $post[0]->ID ),
                );
            }
            $favicons = $aux_favicons;
        }
        
        $users = get_users();
        if( $users ) {
            $aux_users = array();
            foreach( $users as $user ) {
                $aux_user = $user->data;
                $aux_users[] = array(
                    'ID'   => $aux_user->ID,
                    'name' => $aux_user->display_name ? $aux_user->display_name : $aux_user->user_login,
                );

                if($get_user == $aux_user->ID) {
                    $user_name = $aux_user->display_name ? $aux_user->display_name : $aux_user->user_login;
                }
            }
            $users = $aux_users;
        }

        ?>
        <div class="wrap">
            <h1>Lista de favoritos</h1>
            
            <?php if( $users ) : ?>
                <div class="tablenav top">
                    <form action="<?php echo get_bloginfo('url'); ?>/wp-admin/admin.php" method="get" class="alignleft actions bulkactions">
                        <input type="hidden" name="page" value="favicons_user">
                        <select name="user" id="user" required>
                            <option value="">Escolha um usuário. </option>
                            <?php foreach( $users as $user ) : ?>
                                <option value="<?php echo $user['ID']; ?>" <?php if($get_user == $user['ID']) { echo 'selected'; } ?>>
                                    <?php echo $user['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" id="favicons_user-submit" class="button" value="Pesquisar">
                    </form>
                </div>
            <?php endif; ?>

            <?php if( $get_user ) : ?>
                <h2>Favoritos de <?php echo $user_name; ?>:</h2>

                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <th class="manage-column column-primary">
                            Post:
                        </th>
                        <th class="manage-column">
                            Link:
                        </th>
                    </thead>
                    <tbody>
                        <?php if( $favicons ) : ?>
                            <?php foreach( $favicons as $favicon ) : ?>
                                <tr class="check-column">
                                    <td class="has-row-actions column-primary">
                                        <?php echo $favicon['title'];  ?>
                                    </td>
                                    <td class="has-row-actions">
                                        <a href="<?php echo $favicon['link'];  ?>">Ver o post</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="2">Nenhum favorito encontrado para esse usuário.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Escolha um usuário, para visualizar os favoritos.</p>
            <?php endif; ?>
            
        </div>
        <?php
    } 

    public static function init () {
        add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
        add_filter( 'page_template', array( __CLASS__, 'template_page_favicon' ) );
        add_filter( 'theme_page_templates', array( __CLASS__, 'theme_page_favicon' ), 10, 4 );
        add_action( 'wp_head', array( __CLASS__, 'theme_front_end' ), 2);
        add_action( 'admin_menu', array( __CLASS__, 'create_menu_admin' ) );
    }
    
}
register_activation_hook( __FILE__, array( $class_plugin_fav, 'create_table' ) );
add_action( 'init', array( $class_plugin_fav, 'init' ) );
