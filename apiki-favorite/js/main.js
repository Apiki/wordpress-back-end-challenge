jQuery(document).ready(function() {
	jQuery('#favorite-button').on('click', function() {
		var postID = jQuery(this).data('post-id');
		var url = afwp_data.home_url + 'wp-json/apiki/favorites-post/' + postID + '/' + afwp_data.logged_user;
		jQuery.ajax({
			headers: {"X-WP-Nonce": afwp_data.nonce},
			url,
			dataType: "text",
			success:function() {
				jQuery('#favorite-button').addClass('hide');
				jQuery('#cancel-favorite-button').removeClass('hide');
			}
		})
	})


	jQuery('#cancel-favorite-button').on('click', function() {
		var postID = jQuery(this).data('post-id');
		var url = afwp_data.home_url + 'wp-json/apiki/cancel-favorite-post/' + postID + '/' + afwp_data.logged_user;
		jQuery.ajax({
			headers: {"X-WP-Nonce": afwp_data.nonce},
			url,
			dataType: "text",
			success:function() {
				jQuery('#favorite-button').removeClass('hide');
				jQuery('#cancel-favorite-button').addClass('hide');
			}
		})
	})

});