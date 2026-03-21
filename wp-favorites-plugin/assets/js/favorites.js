async function toggleFavorite(postId) {
    const response = await fetch('/wp-json/wpfav/v1/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce
        },
        body: JSON.stringify({ post_id: postId })
    });

    const data = await response.json();
    console.log(data);
}