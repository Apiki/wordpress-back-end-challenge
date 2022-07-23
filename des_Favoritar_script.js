// Script para enviar e receber retorno do registro e valor atual do Favoritar/Desfavoritar via AJAX
jQuery(document).ready( function() {
   jQuery("#des_Favoritar").click( function(e) {
      e.preventDefault(); 
      post_id = jQuery(this).attr("data-post_id");
      nonce = jQuery(this).attr("data-nonce");
      des_Favoritar = jQuery(this).attr("data-desfavoritar");
      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : myAjax.ajaxurl,
         data : {action: "des_Favoritar", post_id : post_id, nonce: nonce},
         success: function(response) {
            if(response.status == "success") {
               jQuery('a#des_Favoritar').text(response.des_Favoritar);
               setTimeout(function() {
                  if (response.des_Favoritar == 'Favoritou') {
                     jQuery('a#des_Favoritar').text('Desfavoritar');
                  } else {
                     jQuery('a#des_Favoritar').text('Favoritar');
                  }
               }, 5000);
            }
            else {
               jQuery("#errorFavoritar").html(response.des_Favoritar + ' Por favor, tente novamente.');
               setTimeout(function() {
                  jQuery("#errorFavoritar").html('');
               }, 5000);
            }
         }
      });
   });
});