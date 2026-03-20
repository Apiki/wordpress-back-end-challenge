(function () {
    'use strict';

    function updateButtonState(button, favorited, text) {
        button.setAttribute('data-favorited', favorited ? 'true' : 'false');
        button.setAttribute('aria-pressed', favorited ? 'true' : 'false');
        button.disabled = false;
        button.textContent = text;
    }

    function handleClick(event) {
        var button = event.target;

        if (!button.classList.contains('apiki-favorite-button')) {
            return;
        }

        event.preventDefault();

        if (!window.apikiFavorites || !window.apikiFavorites.isLoggedIn) {
            window.alert(window.apikiFavorites.loginRequiredText);
            return;
        }

        var postId = parseInt(button.getAttribute('data-post-id'), 10);
        var favorited = button.getAttribute('data-favorited') === 'true';
        var method = favorited ? 'DELETE' : 'POST';
        var nextText = favorited ? window.apikiFavorites.favoriteText : window.apikiFavorites.unfavoriteText;

        if (!postId) {
            return;
        }

        button.disabled = true;
        button.textContent = window.apikiFavorites.loadingText;

        window.fetch(window.apikiFavorites.restUrl + postId, {
            method: method, credentials: 'same-origin', headers: {
                'X-WP-Nonce': window.apikiFavorites.nonce, 'Content-Type': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }

                return response.json();
            })
            .then(function () {
                updateButtonState(button, !favorited, nextText);
            })
            .catch(function () {
                button.disabled = false;
                button.textContent = favorited ? window.apikiFavorites.unfavoriteText : window.apikiFavorites.favoriteText;
                window.alert(window.apikiFavorites.errorText);
            });
    }

    document.addEventListener('click', handleClick);
}());