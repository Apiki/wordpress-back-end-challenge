jQuery(document).ready(function($) {
    $('.favorite-button').on('click', function() {
        var button = $(this);
        var postId = button.data('post-id');

        $.ajax({
            method: 'POST',
            url: favoritePostsData.apiUrl,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', favoritePostsData.nonce);
            },
            data: {
                post_id: postId
            },
            success: function(response) {
                if (response.status === 'favorited') {
                    button.text('Desfavoritar');
                } else if (response.status === 'unfavorited') {
                    button.text('Favoritar');
                }
            },
            error: function() {
                alert('Ocorreu um erro. Tente novamente.');
            }
        });
    });
});
