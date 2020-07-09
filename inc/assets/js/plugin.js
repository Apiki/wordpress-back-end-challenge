var $ = jQuery.noConflict();
$(document).ready(function () {
    $('.button_bookmarkposts').on('click', function(event) {
        event.preventDefault();
        var postID = $(this).attr('post-id');
        if($(this).hasClass('marked')) {
            button_bookmarkposts_call('unmark', postID)
        } else if($(this).hasClass('unmarked')) {
            button_bookmarkposts_call('mark', postID)
        }
    });
});

function button_bookmarkposts_call(action, postID) {
    $.ajax({
        url: homeURL+'wp-json/bookmark-posts/v1/'+action+'/'+postID,
        type: 'GET',
        success:function(data) {
            if(action == 'mark') {
                $('.button_bookmarkposts').removeClass('unmarked').addClass('marked').text('DESMARCAR FAVORITO');
            } else if(action == 'unmark') {
                $('.button_bookmarkposts').removeClass('marked').addClass('unmarked').text('MARCAR COMO FAVORITO');
            }
        },
        error:function() {
            console.log('ERROR');
        }
    });
}
