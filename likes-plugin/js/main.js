class Like {
    constructor() {
        this.events()
    }

    events() {
        jQuery(".like-box").on("click", this.ourClickDispatcher.bind(this))
    }

    ourClickDispatcher(e) {
        let currentLikeBox = jQuery(e.target).closest(".like-box");

        if (currentLikeBox.attr("data-exists") == "yes") {
            this.deleteLike(currentLikeBox)
        } else {
            this.createLike(currentLikeBox)
        }
    }

    createLike(currentLikeBox) {
        jQuery.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', themeData.nonce);
            },
            url: themeData.rootUrl + '/wp-json/likeuri/v1/manageLike',
            type: 'POST',
            data: {
                'user': currentLikeBox.data('user'),
                'post': currentLikeBox.data('post'),
            },
            success: (response) => {
                currentLikeBox.attr('data-exists', 'yes');
                let likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                likeCount++;
                currentLikeBox.find(".like-count").html(likeCount);
                currentLikeBox.attr("data-like", response);
                console.log(response);
            },
            error: (response) => {
                console.log(response);
            }
        })
    }

    deleteLike(currentLikeBox) {
        jQuery.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', themeData.nonce);
            },
            url: themeData.rootUrl + '/wp-json/likeuri/v1/manageLike',
            data: {
                'like': parseInt(currentLikeBox.attr('data-like'), 10),
                'post': currentLikeBox.data('post'),
            },
            type: 'DELETE',
            success: (response) => {
                console.log(response);
                currentLikeBox.attr('data-exists', 'no');
                let likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                likeCount--;
                currentLikeBox.find(".like-count").html(likeCount);
                currentLikeBox.attr("data-like", '');
            },
            error: (response) => {
                console.log(response);
            }
        })
    }
}

const like = new Like();