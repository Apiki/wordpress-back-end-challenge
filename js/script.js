jQuery(document).ready(function ($) {
  $(".likeImg").click(function () {
      let postID = $(this).data("post-id");
      let userID = $(this).data("user-id");
      let liked = $(this).data("liked");

      let data = {
          post_id: postID,
          user_id: userID,
      };

      $.ajax({
          url: '/wp-json/favorites/v1/toggle/',  // Usando o endpoint da REST API.
          method: 'POST',
          data: data,
          beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', myAjax.restNonce); // Incluindo o nonce.
          },
          success: function (response) {
              if (response.success) {
                  if (response.message === "disliked") {
                      $(".likeImg").attr("src", myAjax.imgUrl + "star.svg");
                      $(".likeImg").data("liked", "false");
                  } else if (response.message === "liked") {
                      $(".likeImg").attr("src", myAjax.imgUrl + "star-active.svg");
                      $(".likeImg").data("liked", "true");
                  }
              } else {
                  alert(response.message);
              }
          },
          error: function (response) {
              alert('Ocorreu um erro ao processar sua solicitação.');
          }
      });
  });
});
