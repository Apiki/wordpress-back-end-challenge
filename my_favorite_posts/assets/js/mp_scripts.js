function mp_loading(type) {
    if (type == "open")
        jQuery(".mp_loading_body").css("display", "flex")
    else
        jQuery(".mp_loading_body").css("display", "none")
}

function mp_favorite(url_base, id_user, id_post) {

    // console.log(url_base);
    mp_loading("open")

    jQuery.ajax({
        url: url_base + '/wp-json/api/add_favorite_post/' + id_user + '/' + id_post,
        type: 'GET',
        success: ((callback) => {
            mp_loading()

            alert(callback.message)
            location.reload()

            console.log(callback);
        })
    })
}

function mp_unfavorite(url_base, id) {

    // console.log(url_base);
    mp_loading("open")

    jQuery.ajax({
        url: url_base + '/wp-json/api/remove_favorite_post/' + id,
        type: 'GET',
        success: ((callback) => {
            mp_loading()

            alert(callback.message)
            location.reload()
            console.log(callback);
        })
    })
}