jQuery(document).ready(function ($) {
    $('.favorite-button').on('click', function () {
        var postId = $(this).data('post-id');

        $.ajax({
            url: FavoriteButton.root + 'favorites/v1/posts/' + postId,
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', FavoriteButton.nonce);
            },
            success: function (response) {
                alert('Post favoritado com sucesso!');
            },
            error: function (response) {
                alert('Erro ao favoritar o post.');
            }
        });
    });

    $('.unfavorite-button').on('click', function () {
        var postId = $(this).data('post-id');

        $.ajax({
            url: FavoriteButton.root + 'favorites/v1/posts/' + postId,
            method: 'DELETE',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', FavoriteButton.nonce);
            },
            success: function (response) {
                alert('Post desfavoritado com sucesso!');
            },
            error: function (response) {
                alert('Erro ao desfavoritar o post.');
            }
        });
    });
});

