jQuery(document).ready(function($) {
    const favoritePostsData = window.favoritePostsData;

    if (!favoritePostsData || !favoritePostsData.root || !favoritePostsData.nonce || !favoritePostsData.namespace) {
        console.error('Dados do plugin Favorite Posts não inicializados corretamente.');
        return;
    }

    const restRoot = favoritePostsData.root;
    const restNonce = favoritePostsData.nonce;
    const namespace = favoritePostsData.namespace;
    const isUserLoggedIn = favoritePostsData.is_user_logged_in;
    const i18n = favoritePostsData.i18n;

    function updateButtonState($button, isFavorited) {
        const $outlineIcon = $button.find('.favorite-icon-outline');
        const $filledIcon = $button.find('.favorite-icon-filled');

        if (isFavorited) {
            $button.addClass('is-favorited');
            $outlineIcon.hide();
            $filledIcon.show();
        } else {
            $button.removeClass('is-favorited');
            $outlineIcon.show();
            $filledIcon.hide();
        }
    }

    function checkFavoriteStatus(postId, $button) {
        if (!isUserLoggedIn) { 
            updateButtonState($button, false);
            return;
        }

        $.ajax({
            url: `${restRoot}${namespace}/is-favorited/${postId}`,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', restNonce);
            },
            success: function(response) {
                updateButtonState($button, response.is_favorited);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erro ao verificar status de favorito:', textStatus, errorThrown, jqXHR.responseJSON);
                Swal.fire({
                    icon: 'error',
                    title: i18n.error_title,
                    text: 'Não foi possível verificar o status de favorito.',
                    confirmButtonText: i18n.confirm_button
                });
            }
        });
    }

    $('.favorite-post-button').each(function() {
        const $this = $(this);
        const postId = $this.data('post-id');
        if (postId) {
            checkFavoriteStatus(postId, $this);
        }
    });

    $(document).on('click', '.favorite-post-button', function(e) {
        e.preventDefault();
        const $button = $(this);
        const postId = $button.data('post-id');

        if (!isUserLoggedIn) { 
            Swal.fire({
                icon: 'info',
                title: i18n.login_required_title,
                text: i18n.login_required_text,
                confirmButtonText: i18n.confirm_button
            });
            return; 
        }

        if (!postId) {
            console.error('ID do post não disponível.');
            Swal.fire({
                icon: 'error',
                title: i18n.error_title,
                text: 'ID do post não encontrado.',
                confirmButtonText: i18n.confirm_button
            });
            return;
        }

        let method;
        let endpoint;
        let successMessage;

        if ($button.hasClass('is-favorited')) {
            method = 'DELETE';
            endpoint = `${restRoot}${namespace}/unfavorite/${postId}`;
            successMessage = 'Post desfavoritado com sucesso!';
        } else {
            method = 'POST';
            endpoint = `${restRoot}${namespace}/favorite/${postId}`;
            successMessage = 'Post favoritado com sucesso!';
        }

        $.ajax({
            url: endpoint,
            method: method,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', restNonce);
                $button.prop('disabled', true);
            },
            success: function(response) {
                if (response.favorited !== undefined) {
                    updateButtonState($button, response.favorited);
                }
                Swal.fire({
                    icon: 'success',
                    title: i18n.success_title,
                    text: response.message || successMessage,
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('API Error:', textStatus, errorThrown, jqXHR.responseJSON);
                let errorMessage = i18n.error_title;
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (textStatus) {
                    errorMessage = `Erro: ${textStatus}`;
                }

                Swal.fire({
                    icon: 'error',
                    title: i18n.error_title,
                    text: errorMessage,
                    confirmButtonText: i18n.confirm_button
                });
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});