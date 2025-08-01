document.addEventListener('DOMContentLoaded', function() {
    // Favorite buttons
    document.querySelectorAll('.apiki-favorite-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.dataset.postId;
            toggleFavorite(postId, this);
        });
    });

    function toggleFavorite(postId, button) {
        if (!postId) return;

        fetch('/wp-json/apiki-favorites/v1/favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': apikiFavorites.nonce
            },
            body: JSON.stringify({ post_id: postId }),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button appearance
                if (button) {
                    button.classList.toggle('active', data.is_favorited);
                    button.setAttribute('aria-pressed', data.is_favorited);
                    button.title = data.is_favorited ? 'Remove from favorites' : 'Add to favorites';
                }
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Initialize favorite buttons
    function initFavoriteButtons() {
        document.querySelectorAll('.apiki-favorite-btn').forEach(button => {
            const postId = button.dataset.postId;
            if (postId) {
                fetch('/wp-json/apiki-favorites/v1/favorites')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.favorites.includes(parseInt(postId))) {
                            button.classList.add('active');
                            button.setAttribute('aria-pressed', 'true');
                            button.title = 'Remove from favorites';
                        }
                    });
            }
        });
    }

    // Only initialize if user is logged in
    if (apikiFavorites.isLoggedIn) {
        initFavoriteButtons();
    }
});