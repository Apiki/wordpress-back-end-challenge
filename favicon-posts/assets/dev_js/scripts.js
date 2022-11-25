const bodyClass = document.querySelector( 'body' ).classList;
const is_single = bodyClass.contains( 'single-post' );
const is_page_favicon = bodyClass.contains( 'page-template-page-favicons' );

document.addEventListener('DOMContentLoaded', () => {
    if( favicon_plugin_obj ) {
        const { base_url_req } = favicon_plugin_obj;
        if( is_single ) {
            const { user_id, post_id } = favicon_plugin_obj;
            checkPostFavorite( user_id, post_id, base_url_req );

            document.addEventListener('click', (e) => {
                const btn_add_single = e.target.classList.contains( 'favicon-action__add' );
                const btn_remove_single = e.target.classList.contains( 'favicon-action__rmm' );

                if( ! btn_add_single && ! btn_remove_single ) return;

                e.target.classList.add('hide');
                toggleLoadSingle();
                if( btn_remove_single ) {
                    remove( user_id, post_id, base_url_req );
                }
                if( btn_add_single ) {
                    add( user_id, post_id, base_url_req );
                }
            });
        }

        if( is_page_favicon ) {
            const btns_remove = document.querySelectorAll( '.card-post__remove' );
            if( btns_remove ) {
                btns_remove.forEach( btn => {
                    btn.addEventListener( 'click', (e) => {
                        const { currentTarget } = e;
                        const post_id = +currentTarget.dataset.id;
                        const { user_id } = favicon_plugin_obj;
                        
                        currentTarget.disabled = true;
                        currentTarget.querySelector( '.-is-text' ).classList.toggle( 'hide-span' );
                        currentTarget.querySelector( '.-is-load' ).classList.toggle( 'hide-span' );
                        
                        remove( user_id, post_id, base_url_req );
                    } )
                });
            }
        }
    }
});

function add( user_id, post_id, baseRequestUrl ) {
    if( ! baseRequestUrl ) return;
    if( ! post_id || (typeof post_id !== 'number') ) return;
    if( ! user_id || (typeof user_id  !== 'number') ) return;

    let formdata = new FormData();
        formdata.append( "user", user_id );
        formdata.append( "post", post_id );

    const requestOptions = {
        method: 'POST',
        body: formdata,
    };

    fetch(`${baseRequestUrl}/add_favicon`, requestOptions)
        .then(r => r.json())
        .then(result => {
            console.log({ result });
            toggleLoadSingle();
            document.querySelector('.favicon-action__rmm').classList.remove('hide');
        })
        .catch(error => console.log('error', error));
}

function remove( user_id, post_id, baseRequestUrl ) {
    if( ! baseRequestUrl ) return;
    if( ! post_id || (typeof post_id !== 'number') ) return;
    if( ! user_id || (typeof user_id  !== 'number') ) return;

    let formdata = new FormData();
        formdata.append( "user", user_id );
        formdata.append( "post", post_id );

    const requestOptions = {
        method: 'POST',
        body: formdata,
    };

    fetch(`${baseRequestUrl}/rm_favicon`, requestOptions)
        .then(r => r.json())
        .then(result => {
            console.log(result);
            if( is_page_favicon ) {
                document.getElementById(`favorito-${post_id}`).remove();
            }
            if( is_single ) {
                toggleLoadSingle();
                document.querySelector('.favicon-action__add').classList.remove('hide');
            }
        })
        .catch(error => console.log('error', error));

}

function checkPostFavorite( user_id, post_id, baseRequestUrl ) {
    if( ! baseRequestUrl ) return;
    if( ! post_id || (typeof post_id !== 'number') ) return;
    if( ! user_id || (typeof user_id  !== 'number') ) return;

    fetch(`${baseRequestUrl}/check_post_favicon/?post=${post_id}&user=${user_id}`)
        .then(r => r.json())
        .then(result => {
            //console.log('favorite: ', result.favorite);
            createButtonFavorite( result.favorite );
        })
        .catch(error => console.log('error', error));
}

function createButtonFavorite( bool_favorite ) {
    let wrap_fav = document.createElement('div');
    wrap_fav.classList.add('favicon-action');

    let btnAddFav = document.createElement('button');
    btnAddFav.innerHTML = 'Favoritar Post';
    btnAddFav.classList.add('favicon-action__add');
    if( bool_favorite ) {
        btnAddFav.classList.add('hide');
    }

    let btnRemoveFav = document.createElement('button');
    btnRemoveFav.innerHTML = "Remover dos Favoritos";
    btnRemoveFav.classList.add('favicon-action__rmm');
    if( ! bool_favorite ) {
        btnRemoveFav.classList.add('hide');
    }

    let preloadFav = document.createElement('span');
        preloadFav.innerHTML = "Aguarde...";
        preloadFav.classList.add('favicon-action__loading');
        preloadFav.classList.add('hide');


    wrap_fav.append(btnAddFav);
    wrap_fav.append(btnRemoveFav);
    wrap_fav.append(preloadFav);
    
    document.querySelector('body').append(wrap_fav);

}

function toggleLoadSingle() {
    document.querySelector( '.favicon-action__loading' ).classList.toggle('hide');
}