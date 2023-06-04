jQuery(document).ready(function ($) {
  $(".likeImg").click(function () {
    let postID = $(this).data("post-id");
    let userID = $(this).data("user-id");
    let liked = $(this).data("liked");

    let data = {
      action: "register_like",
      post_id: postID,
      user_id: userID,
      is_liked: liked,
    };
    // console.log(liked);
    // console.log(myAjax);

    $.post(myAjax.ajaxurl, data, function (response) {
      // console.log(response);
      if (response.success == true) {
        if (response.message == "disliked") {
          // console.log("tirar active");
          $(".likeImg").attr("src", myAjax.imgUrl + "heart.svg");
          $(".likeImg").data("liked", "false");
          return false;
        }

        if (response.message == "liked") {
          // console.log("mudar para active");
          $(".likeImg").attr("src", myAjax.imgUrl + "heart-active.svg");
          $(".likeImg").data("liked", "true");
          return false;
        }
      }

      if (response.success == false) {
        alert(response.message);
      }
    });
  });
});
