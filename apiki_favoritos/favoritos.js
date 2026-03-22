jQuery(document).ready(function($){
    $('.favorito-btn').click(function(){
        const btn = $(this);
        const post_id = btn.data('post');
        const action = btn.text().includes('Desfavoritar') ? 'desfavoritar' : 'favoritar';

        $.ajax({
            url: wpFavoritos.url + '/' + action,
            method: 'POST',
            data: { post_id },
            beforeSend: function(xhr){
                xhr.setRequestHeader('X-WP-Nonce', wpFavoritos.nonce);
            },
            success: function(res){
                if(res.favorited){
                    btn.text(' Desfavoritar');
                } else {
                    btn.text(' Favoritar');
                }
            }
        });
    });
});