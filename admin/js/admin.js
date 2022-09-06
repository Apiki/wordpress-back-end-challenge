(function( $ ) {
  $('.wbec-fav-btn').each(function(index, el) {
    $(el).click(function(event) {
      var post_id = $(el).data('id');
      $(el).find('span').toggleClass('dashicons-star-filled');
      $(el).find('span').toggleClass('dashicons-star-empty');
      toggleFav(post_id);
    });
  });
  function toggleFav(idPost){
    var ajaxurl = wbec_var['ajaxurl'];
    $.ajax({
      type: "post",
      url: ajaxurl,
      data: {action: "toggle_fav",id: idPost},
      success: function(msg){
          alert('Alterado com sucesso!');
      }
  });
  }
})( jQuery );