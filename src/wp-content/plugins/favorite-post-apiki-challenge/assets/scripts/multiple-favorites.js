jQuery(document).ready(function () {
    var usuario
    jQuery.ajax({
        type: "post",
        url: 'wp-admin/admin-ajax.php',
        dataType: "json",
        data: {
            'action':'is_user_logged_in'
        }, success: function(response) {
        usuario = response.data.user
        let posts = jQuery('li.post');
        jQuery(posts).each(function (a, b) {
            var atual_post = jQuery(b).attr('class').split(/\s+/);
            jQuery.ajax({
                type: "post",
                url: '/wp-json/v2/check-favorite',
                dataType: "json",
                data: {
                    user: usuario,
                    post: atual_post[1]
                }, success: function(response) {
                    if(response.data.status === '00') {
                        append_favorite(b, 'red', atual_post[1], usuario)
                    } else {
                        append_favorite(b, '#ccc', atual_post[1], usuario)
                    }
                }
            });
        })
    }})

    function append_favorite(element, color, index, user)
    {
        jQuery(element).prepend(
            '    <label for="checkbox"  style="display: flex; float: right">\n' +
            '      <svg id="heart-svg' + index.replace('post-', '') +'" viewBox="467 392 58 57" xmlns="http://www.w3.org/2000/svg">\n' +
            '        <g id="Group" fill="none" fill-rule="evenodd" transform="translate(467 392)">\n' +
            '          <path d="M29.144 20.773c-.063-.13-4.227-8.67-11.44-2.59C7.63 28.795 28.94 43.256 29.143 43.394c.204-.138 21.513-14.6 11.44-25.213-7.214-6.08-11.377 2.46-11.44 2.59z" id="heart" fill="'+ color +'"/>\n' +
            '          <circle id="main-circ" fill="#E2264D" opacity="0" cx="29.5" cy="29.5" r="1.5"/>\n' +
            '\n' +
            '          <g id="grp7" opacity="0" transform="translate(7 6)">\n' +
            '            <circle id="oval1" fill="#9CD8C3" cx="2" cy="6" r="2"/>\n' +
            '            <circle id="oval2" fill="#8CE8C3" cx="5" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '\n' +
            '          <g id="grp6" opacity="0" transform="translate(0 28)">\n' +
            '            <circle id="oval1" fill="#CC8EF5" cx="2" cy="7" r="2"/>\n' +
            '            <circle id="oval2" fill="#91D2FA" cx="3" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '\n' +
            '          <g id="grp3" opacity="0" transform="translate(52 28)">\n' +
            '            <circle id="oval2" fill="#9CD8C3" cx="2" cy="7" r="2"/>\n' +
            '            <circle id="oval1" fill="#8CE8C3" cx="4" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '\n' +
            '          <g id="grp2" opacity="0" transform="translate(44 6)">\n' +
            '            <circle id="oval2" fill="#CC8EF5" cx="5" cy="6" r="2"/>\n' +
            '            <circle id="oval1" fill="#CC8EF5" cx="2" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '\n' +
            '          <g id="grp5" opacity="0" transform="translate(14 50)">\n' +
            '            <circle id="oval1" fill="#91D2FA" cx="6" cy="5" r="2"/>\n' +
            '            <circle id="oval2" fill="#91D2FA" cx="2" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '\n' +
            '          <g id="grp4" opacity="0" transform="translate(35 50)">\n' +
            '            <circle id="oval1" fill="#F48EA7" cx="6" cy="5" r="2"/>\n' +
            '            <circle id="oval2" fill="#F48EA7" cx="2" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '\n' +
            '          <g id="grp1" opacity="0" transform="translate(24)">\n' +
            '            <circle id="oval1" fill="#9FC7FA" cx="2.5" cy="3" r="2"/>\n' +
            '            <circle id="oval2" fill="#9FC7FA" cx="7.5" cy="2" r="2"/>\n' +
            '          </g>\n' +
            '        </g>\n' +
            '      </svg>\n' +
            '    </label>'
        );

        jQuery("#heart-svg" + index.replace('post-', '')).on('click', function () {
            ajax_requests(jQuery(this), user);
        })
    }
})


function ajax_requests(element, usuario)
{
    let classes = element.parent().parent().attr('class').split(/\s+/);
    let coracao = element;
    jQuery.ajax({
        type: "post",
        url: '/wp-json/v2/favorite',
        dataType: "json",
        data: {
            user: usuario,
            post: classes[1]
        }
    }).success(function(response) {
        if(response.data.data == 'ati') {
            coracao.find('#Group').find('#heart').css('fill', 'red')
        } else {
            coracao.find('#Group').find('#heart').css('fill', '#ccc')
        }
    });
}