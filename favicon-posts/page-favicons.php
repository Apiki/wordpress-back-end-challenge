<?php
/*
Template Name: Pagina - Favoritos
*/

$logado = is_user_logged_in();
if ( $logado ) { 
    $title = "logado";

    $user_name = wp_get_current_user()->display_name ? wp_get_current_user()->display_name: wp_get_current_user()->user_login;
    $user_id = wp_get_current_user()->ID;
    $base_name = $wpdb->base_prefix . "favicons_posts";

    $favicons = $wpdb->get_results("SELECT post FROM $base_name WHERE user = 1");
    if( $favicons ) {
        $aux_favicon = array();
        foreach( $favicons as $favicon ) {
            $post = get_posts(
                array( 'post__in' => array( $favicon->post ) )
            );
            
            $aux_favicon[] = array(
                'id'      => $post[0]->ID,
                'title'   => $post[0]->post_title,
                'thumb'   => get_the_post_thumbnail_url( $post[0]->ID ),
                'excerpt' => $post[0]->post_excerpt,
                'link'    => get_the_permalink( $post[0]->ID ),
            );            
        }
        $favicons = $aux_favicon;
    }
}

get_header(); ?>
    <main id="main" class="site-main" role="main">
        <?php if ( $logado ) : ?>
		<div class="container">
            <?php while ( have_posts() ) : the_post(); ?>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            <?php endwhile; ?>
            <hr>
            <?php if( $favicons ) : ?>
                <div class="posts-list">
                    <div class="posts-list__header">
                        <h2>Posts favoritos:</h2>
                    </div>
                    <div class="posts-list__page">
                        <?php foreach( $favicons as $favicon ) : ?>
                            <article class="card-post mx-4 md-6 sm-6" id="favorito-<?php echo $favicon['id']; ?>">
                                <div class="card-post__container">
                                    <figure class="card-post__figure">
                                        <?php if( $favicon['thumb'] ): ?>
                                            <img src="<?php echo $favicon['thumb']; ?>" alt="<?php echo $favicon['title']; ?>">
                                        <?php else : ?>
                                            <img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/no-image.jpg" alt="<?php echo $favicon['title']; ?>">
                                        <?php endif; ?>
                                    </figure>

                                    <div class="card-post__text">
                                        <h3 class="card-post__title"><?php echo $favicon['title']; ?></h3>

                                        <div class="card-post__excerpt">
                                            <?php echo $favicon['excerpt']; ?>
                                        </div>
                                        
                                        <a href="<?php echo $favicon['link']; ?>" class="card-post__link">Leia Mais</a>

                                        <button class="card-post__remove" data-id="<?php echo $favicon['id']; ?>">
                                            <span class="-is-text">Remover dos favoritos</span>
                                            <span class="-is-load hide-span">Removendo, aguarde...</span>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="posts-list">
                    <div class="posts-list__header">
                        <h2>Posts favoritos:</h2>
                        <p>Não possui posts favoritados.</p>
                    </div>
                </div>
            <?php endif; ?>            
		</div>
        <?php else : ?>
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <p>Faz o login para visualizar os favoritos:</p>
            
            <form action="<?php echo get_option('home'); ?>/wp-login.php" method="post" class="form-login">
                <label for="log" class="form-login__label">Login:</label>
                <input type="text" name="log" id="log" class="form-login__input" value="" size="20" required>
              
                <label for="pwd" class="form-login__label">Senha:</label>
                <input type="password" name="pwd" id="pwd" class="form-login__input" size="20" required>
                    
                <button class="form-login__button">Login</button>
                
                <input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" />
            </form>
        </div>
        <?php endif; ?>
	</main>

<?php get_footer(); ?>