/**
* Template Name: Personal - v4.8.1
* Template URL: https://bootstrapmade.com/personal-free-resume-bootstrap-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/
(function ($) {

    "use strict";
/**
 * Easy selector helper function
 */
const select = (el, all = false) => {
    el = el.trim() 
    if (all) {
        return [...document.querySelectorAll(el)]
    } else {
        return document.querySelector(el)
    }
}

/**
 * Easy event listener function
 */
 const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)

    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }


/**
 * 
 */
 window.addEventListener('load', () => {
    let likeCtabutton = select('.like-cta-button');
    if (likeCtabutton) {
        on('click', '.like-cta-button', function(e) {
            e.preventDefault();
            $(this).prop("disabled",true);
            
            const post_id = $(this).data('postid');
            const site_url = $(this).data('siteurl');
            if(this.classList.contains('liked')){                
                this.classList.remove('liked');
                console.log('deslike');
                liked(post_id, site_url);
            } else {               
                console.log('like');
                liked(post_id, site_url);
                this.classList.add('liked');
            }
          }, true);  
    }

});

function liked(post_id, site_url) { 
    var formData = {
        post_id: post_id,
        site_url: site_url
    };
    console.log(formData);
    $.ajax({
        type: "POST",
        data: formData,
        dataType: "json",
        encode: true,
        url: site_url+'/wp-json/mpf/v1/likepost',
        beforeSend: function () {
            //load
        },
        success: function (resp) {           
            alert(resp);
            $('.like-cta-button').prop("disabled",false);
        },
    });
};
  

})(jQuery);