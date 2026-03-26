import Toastify from 'toastify-js';

const favoritePosts = {
    init() {
        if (!this.component) return;

        this.addEventListeners();
    },

    addEventListeners() {
        this.component.addEventListener('click', this.handleClick.bind(this));
    },

    async favoritePost() {
        // @ts-ignore
        fetch(wpApiSettings.root + wpApiSettings.rest_namespace + '/favorite-posts', {
            method: 'POST',
            body: JSON.stringify({
                post_id: this.component.dataset.postId
            }),
            headers: {
                'Content-Type': 'application/json',
                // @ts-ignore
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            Toastify({ text: data.message }).showToast();

            this.component.classList.add('is-favorite');
        })
        .catch(error => {
            Toastify({
                text: 'Failed to add favorite post',
                className: 'error',
                style: {
                    background: 'linear-gradient(to right, #ff0000, #ff4747)',
                }
            }).showToast();
        });
    },

    async handleClick() {
        !!this.dialogLoader && this.dialogLoader.showModal();

        if (this.component.classList.contains('is-favorite')) {
            await this.removeFavoritePost();
        } else {
            await this.favoritePost();
        }

        !!this.dialogLoader && this.dialogLoader.close();
    },

    async removeFavoritePost() {
        // @ts-ignore
        fetch(wpApiSettings.root + wpApiSettings.rest_namespace + '/favorite-posts', {
            method: 'DELETE',
            body: JSON.stringify({
                post_id: this.component.dataset.postId
            }),
            headers: {
                'Content-Type': 'application/json',
                // @ts-ignore
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            Toastify({ text: data.message }).showToast();

            this.component.classList.remove('is-favorite');
        })
        .catch(error => {
            Toastify({
                text: 'Failed to remove favorite post',
                className: 'error',
                style: {
                    background: 'linear-gradient(to right, #ff0000, #ff4747)',
                }
            }).showToast();
        });
    },

    component: document.querySelector('.favorite-post-button'),
    dialogLoader: document.querySelector('#dialog-loader'),
    favoritePostsPopover: document.querySelector('#favorite-posts-popover'),
}

favoritePosts.init();
