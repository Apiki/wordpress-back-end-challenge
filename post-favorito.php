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

    add_action('admin_menu', function () {
        add_menu_page( 'Post Favorito', 'Post Favorito', 'manage_options', 'posts-favorito', function () {
            echo '
                <h1 class="wp-heading-inline">Post Favorito</h1> <br />
                Use shortcode: <b>[postfavorito itenspagina="5"]</b> <br />
                O padrão de itens por pagina é 5 caso não informado
            ';
        }, 'dashicons-star-filled');
    });

    add_shortcode('postfavorito', function ($param) {
        $itenspagina = isset($param['itenspagina']) ? $param['itenspagina'] : 5;

        return '
            <ul data-id="post-favorito-itens"></ul>

            <button onclick="carregarPost(this)" data-pagina="1">Carregar posts</button>

            <script>
            function carregarPost(el) {
                    
                el.innerHTML = \'Carregando...\';
                var pagina = parseInt(el.getAttribute(\'data-pagina\'));

                fetch(\'' . get_site_url() . '/wp-json/post-favorito/v1/posts/\' + pagina + \'/'. $itenspagina .'\')
                .then(function(res) {

                    if (!res.ok) {
                        throw Error(res.statusText);
                    }


                    if( pagina === 1 && ' . $itenspagina . ' ) {
                    
                    }

                    el.innerHTML = \'Carregar posts\';
                    return res.json();

                }).then(function(json) {
                    var posts =  document.querySelector(\'ul[data-id=post-favorito-itens]\');
                    
                    if(json.length === 0) {
                        el.remove();
                    }

                    if(pagina === 1 && json.length === 0) {
                        var li = document.createElement(\'li\');
                        li.innerHTML = \'Nenhum post favorito\';

                        posts.appendChild(li);
                        return;
                    }

                    el.setAttribute(\'data-pagina\', pagina + 1);

                    json.forEach(function (item) {
                    
                        var a = document.createElement(\'a\');
                        a.innerHTML = item.post_title;
                        a.setAttribute(\'href\', item.url);

                        var li = document.createElement(\'li\');
                        li.appendChild(a);

                        posts.appendChild(li);
                    });
                })
                .catch(function() {
                    el.innerHTML = \'Carregar posts\' ;

                    alert(\'Erro ao carregar posts, tente novamente mais tarde!\');
                });
            }
            </script>
        ';
    });

    add_action( 'rest_api_init', function () use ($usuarioId, $tabela, $wpdb) {


        register_rest_route( 'post-favorito/v1', 'posts/(?P<pagina>\d+)/(?P<limite>\d+)', array(
            'methods' => 'GET',
            'callback' => function ($param) use ($usuarioId, $tabela, $wpdb) {

                $tabelaPost = $wpdb->prefix . 'posts';
                $pagina = ($param->get_param('pagina') - 1)  * $param->get_param('limite');
                $limite = $param->get_param('limite') + $pagina;
                
                $posts = $wpdb->get_results("
                    SELECT 
                        p.ID,
                        p.post_title
                    FROM {$tabelaPost} as p
                    WHERE p.post_type = 'post' 
                    AND p.post_status = 'publish'
                    AND p.ID IN(SELECT u.post_id FROM {$tabela} u WHERE u.usuario_id = {$usuarioId})
                    LIMIT $pagina, $limite
                ");

                return array_map(function($post) {
                    $post->url = get_permalink($post->ID);
                    return $post;
                }, $posts);
            },
        ));

        
        register_rest_route( 'post-favorito/v1', 'add/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => function ($param) use ($usuarioId, $tabela, $wpdb) {

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


        register_rest_route( 'post-favorito/v1', 'del/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => function ($param) use ($usuarioId, $tabela, $wpdb) {
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
        
            <button onclick="postFavorito(' . $post->ID . ', this)">' . $label . '</button>

            <script> 
                function postFavorito(id, el) {
                    
                    var metodo = \'POST\';
                    var url  = \'add\';

                    if(el.innerHTML === \'Remover post dos favoritos\') {
                        var metodo = \'DELETE\';
                        var url  = \'del\'; 
                    }

                    el.innerHTML = \'Carregando...\';

                    fetch(\'' . get_site_url() . '/wp-json/post-favorito/v1/\' + url + \'/\'  + id, {method: metodo})
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

                        alert(\'Erro ao adicionar post aos favoritos, tente novamente mais tarde!\');
                    });
                }
            </script>
        </p>';
    }


    return $postConteudo;

});