    <?php
/**
* Plugin Name: Post Favorito
* Description: Permite gravar um post como favorito
* Version: 1.0
* Author: Ricardo Saraiva
*/


//Cria a tabela para salvar os posts como favoritos
register_activation_hook(__FILE__ , function () {
    global $wpdb;

    $tabela = 'posts_favorito';

    $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$tabela} (
            ID BIGINT PRIMARY KEY AUTO_INCREMENT,
            post_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL
        ) ENGINE = INNODB;
    ";

    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    dbDelta($sql);
});

//adiciona botão para adicionar / remover dos favoritos
add_filter('the_content', function ($post) {

    if (is_single() && is_user_logged_in()) {

        global $wpdb;

        $postFavorito = $wpdb->get_results( "
            SELECT COUNT(*) AS total FROM {$wpdb->prefix}posts_favorito WHERE post_id = " 
            . get_post()->ID . " AND user_id = " . get_current_user_id(), OBJECT );

        //valida se o post ja esta nos favoritos e adiciona botão para remover poste dos favoritos
        if($postFavorito[0]->total > 0) {
            return $post . '<p>
                <button onclick="del_post_favorito(' . get_post()->ID . ')">Remover post dos favoritos</button>
            </p>';    
        }

        return $post . '<p>
            <button onclick="add_post_favorito(' . get_post()->ID . ')">Adicionar post aos favoritos</button>
        </p>';
    }

    return $post;

});