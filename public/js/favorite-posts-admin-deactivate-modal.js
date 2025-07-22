jQuery(document).ready(function($) {
    const data = window.favoritePostsDeactivationData;

    if (!data || !data.plugin_slug) {
        return;
    }

    const deactivateLink = $(`tr[data-slug="${data.plugin_slug}"] .deactivate a`);

    if (deactivateLink.length === 0) {
        return; 
    }

    const originalDeactivateUrl = deactivateLink.attr('href');

    deactivateLink.on('click', function(e) {
        e.preventDefault(); 

        Swal.fire({
            title: data.modal_title,
            text: data.modal_text,
            icon: 'question',
            showDenyButton: true, 
            showCancelButton: true, 
            confirmButtonText: data.confirm_text, 
            cancelButtonText: data.cancel_text, 
            denyButtonText: data.deny_text,
            reverseButtons: data.is_rtl 
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = originalDeactivateUrl + '&_favorite_posts_delete_data=' + data.keep_data_value;
            } else if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = originalDeactivateUrl + '&_favorite_posts_delete_data=' + data.delete_data_value;
            } else if (result.isDenied) {
            }
        });
    });
});