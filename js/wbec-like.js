jQuery(document).ready(function($) {
    jQuery('#like-button').click(function(e){
        e.preventDefault();
        var nonce = jQuery(this).attr("data-nonce");
        var post_id = jQuery(this).attr("data-post-id");
        jQuery.ajax({
            url : wbeclike.ajax_url,
            type : "post",
            data : {
                action : 'wbec_like_func',
                post_id: post_id,
                nonce: nonce
            },
            success : function( response ) {
                var responseJSON = JSON.parse(response);
                if(responseJSON.type == "success") {
                    jQuery("#like-button").html(responseJSON.text);
                }else{
                    alert("Your could not favourite this post. Refresh the page and try again!");
                }
            }
        });
        
        return false;
    });
});