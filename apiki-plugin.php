<?php
/**
 * Plugin Name
 *
 * @package           WP Plugin Back End Apiki
 * @author            Francisco Matelli Matulovic
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP Plugin Back End Apiki
 * Plugin URI:        https://www.franciscomatelli.com.br
 * Description:       Code challenge, wp rest favorite post.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Francisco Matelli Matulovic
 * Author URI:        https://www.franciscomatelli.com.br
 * Text Domain:       apiki-chalenge
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

add_action('init', 'my_init');

function my_init() {
    add_action( 'rest_api_init', 'register_posts_meta_field' ); 
    add_action( 'wp_enqueue_scripts', 'api_test' );
    add_filter( 'the_content', 'add_content_after');
}

function register_posts_meta_field() {   
    $meta_args = array( 
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => "padra",
    );
    register_meta( 'post', 'favorited_by', $meta_args );
}

function add_content_after($content) {

    $user_id = get_current_user_id();
    if($user_id and !is_admin() and is_single()) {
        
        $post_id = get_the_ID();
        $meta = get_post_meta($post_id);
        $favorited_by_st = $meta["favorited_by"][0];
        $favorited_by_ar = explode(",", $favorited_by_st);
        
        //checa se usuário logado favoritou o post, se está na lista
        $favoritado = in_array($user_id, $favorited_by_ar);
        
        //se não estiver, adiciona pois essa lista é usada para salvar com ele
        if(!$favoritado) {
            $favorited_by_ar[] = $user_id;
        }
        $arr2 = $favorited_by_ar;
        foreach($arr2 as $k => $v) {
            if ($v == $user_id) {
                unset($arr2[$k]);
            }
        }
        
        //lista de ids que favoritaram, com o usuário
        $favcom = implode(",", $favorited_by_ar);
        //lista de ids que favoritaram, sem o usuário
        $favsem = implode(",", $arr2);
        
        //pode ser melhorado com ícones
        $txt_btn = $favoritado ?  "FAVORITADO" : "ADICIONAR AOS FAVORITOS";
        $after_content = '<p><button id="botao-favorito" onclick="fav()">'.$txt_btn.'</button>';
        ?>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>

        <script>
        //pega a variável do PHP e joga no JS
        favoritado = "<?php echo $favoritado; ?>";
        //
        async function fav() {
            if(!favoritado) {
                favoritado = true;
                jQuery("#botao-favorito").text("FAVORITADO"); 
                data = {
                    "meta": {
                        "favorited_by": "<?php echo $favcom; ?>",
                    }
                }
            } else {
                favoritado = false;
                jQuery("#botao-favorito").text("ADICIONAR AOS FAVORITOS"); 
                data = {
                    "meta": {
                        "favorited_by": "<?php echo $favsem; ?>",
                    }
                }
            }
            
            config = {
                headers:{
                    'X-WP-Nonce': wpApiSettings.nonce
                }
                
            }
            
            await axios.post("/wp-json/wp/v2/posts/<?php echo $post_id; ?>", data, config )
            .then( function ( response ) {
                console.log( response );
            } )
            .catch( error => console.log( error ) );
        }

        </script>

        <?php
        $fullcontent =  $content . $after_content;

        return $fullcontent;
    }

}
function api_test() {
    //wp_enqueue_script( 'axios', 'https://unpkg.com/axios/dist/axios.min.js' );

    wp_localize_script( 'wp-api', 'wpApiSettings', array(
        'root' => esc_url_raw( rest_url() ),
        'nonce' => wp_create_nonce( 'wp_rest' ),
    ) );
    wp_enqueue_script('wp-api');
}

