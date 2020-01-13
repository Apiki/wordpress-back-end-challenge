function pl_favoritar_post() {
    let url = 'http://localhost/wordpress/wp-json/post-liking/v1/favorites';
    post_data(url, {
        post_id : parseInt(post_object.post_id),
        post_title : post_object.post_title,
        post_url : post_object.post_url
    })
    .then((data) => {
            console.log(data);
    });
    location.reload(true);
}

function pl_desfavoritar_post() {
    let url = 'http://localhost/wordpress/wp-json/post-liking/v1/favorites/'+post_object.post_id;
    delete_data(url, {
        post_id : parseInt(post_object.post_id)
    })
    .then((data) => {
        console.log(data);
    });
    location.reload(true);
}

async function post_data(url = '', data = {}) {
    const response = await fetch(url, {
        method: 'POST',
        mode: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();
}

async function delete_data(url = '', data = {}) {
    const response = await fetch(url, {
        method: 'DELETE',
        mode: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();
}