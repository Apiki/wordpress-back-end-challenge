<?php
/**
* Plugin Name: Post Favorito
* Description: Permite gravar um post como favorito
* Version: 1.0
* Author: Ricardo Saraiva
*/


register_activation_hook(__FILE__ , function () {
    global $wpdb;

    //Cria tabela para gravar os posts favoritos
    $tabela = 'posts_favorito';

    $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$tabela} (
            ID BIGINT PRIMARY KEY AUTO_INCREMENT,
            post_id BIGINT NOT NULL,
            usuario_id BIGINT NOT NULL,
            UNIQUE(post_id, usuario_id)
        ) ENGINE = INNODB;
    ";

    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    dbDelta($sql);    
});

//carrega os endpoints da api e o widget
add_action('plugins_loaded', function() {

    global $wpdb;


    $usuarioId = get_current_user_id();
    $tabela = $wpdb->prefix . 'posts_favorito';

    //valida se o usuario esta logado
    if(!is_user_logged_in()) {
        return;
    }


    add_action( 'rest_api_init', function () use ($usuarioId, $logado, $tabela, $wpdb) {
        
        register_rest_route( 'post_favorito/v1', 'add/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => function ($param) use ($usuarioId, $logado, $tabela, $wpdb) {

                $postTipo = get_post_type($param->get_param('id'));

                //valida se o post existe
                if($postTipo != 'post') {
                    $res = new WP_REST_Response();
                    $res->set_status(400);
                    $res->set_data('Post inválido');
                    return $res;
                }

                $dados = [
                    'usuario_id' => $usuarioId,
                    'post_id' => $param->get_param('id')
                ];

                $wpdb->insert($tabela, $dados, ['%d', '%d']);
                return 'Post adicionado ao favorito!';
            },
        ));


        register_rest_route( 'post_favorito/v1', 'del/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => function ($param) use ($usuarioId, $logado, $tabela, $wpdb) {
                $dados = [
                    'usuario_id' => $usuarioId,
                    'post_id' => $param->get_param('id')
                ];

                $wpdb->delete($tabela, $dados);
                return 'Post removido com sucesso!';
            },
        ));

    });    

});

//adiciona botão para adicionar / remover dos favoritos
add_filter('the_content', function ($postConteudo) {

    if (is_single() && is_user_logged_in()) {

        global $wpdb;
        $post = get_post();
    

        $postFavorito = $wpdb->get_results( "
            SELECT COUNT(*) AS total FROM {$wpdb->prefix}posts_favorito WHERE post_id = " 
            . $post->ID . " AND usuario_id = " . get_current_user_id(), OBJECT );


        //valida se o post ja esta nos favoritos e adiciona botão para remover poste dos favoritos
        $label = ($postFavorito[0]->total > 0) ? 'Remover post dos favoritos' : 'Adicionar post aos favoritos';

        return $postConteudo . '<p>
        
            <button onclick="post_favorito(' . $post->ID . ', this)">' . $label . '</button>

            <script> 
                function post_favorito(id, el) {
                    
                    var metodo = \'POST\';
                    var url  = \'add\';

                    if(el.innerHTML === \'Remover post dos favoritos\') {
                        var metodo = \'DELETE\';
                        var url  = \'del\'; 
                    }

                    el.innerHTML = \'Carregando...\';

                    fetch(\'' . get_site_url() . '/wp-json/post_favorito/v1/\' + url + \'/\'  + id, {method: metodo})
                    .then(function(res) {

                        if (!res.ok) {
                            throw Error(res.statusText);
                        }

                        el.innerHTML = (url === \'add\') ? 
                            \'Remover post dos favoritos\' : \'Adicionar post aos favoritos\';
                    })
                    .catch(function() {
                        el.innerHTML = (url === \'del\') ? 
                            \'Remover post dos favoritos\' : \'Adicionar post aos favoritos\';

                        alert(\'Erro ao adicionar post aos favoritos tente novamente mais tarde!\');
                    });
                }
            </script>
        </p>';
    }

    return $postConteudo;

});