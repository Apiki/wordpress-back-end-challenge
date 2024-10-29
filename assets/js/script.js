document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.like-button');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-post-id');
            const isLiked = this.classList.contains('liked');

            const method = isLiked ? 'DELETE' : 'POST';
            const endpoint = isLiked ? 'delete' : 'add';
            const url = `/wp-json/like/v1/${endpoint}`;

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify({
                    post_id: postId
                })
            })
            .then(response => {
                console.log(response);
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                return response.json();
            })
            .then(data => {
                console.log(data.message);
                this.classList.toggle('liked', !isLiked);
                this.textContent = isLiked ? 'Favoritar' : 'Desfavoritar';
            })
            .catch(error => {
                alert('Erro: ' + error.message);
            });
        });
    });
});
