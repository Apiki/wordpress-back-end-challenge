function jvaflp_handleAjax() {
  jQuery(document).ready(function ($) {
    let likeButton = document.getElementById("likeButton");
    let likeButtonMsg = document.getElementById("likeButtonMsg");
    let likeButtonIcon = document.getElementById("likeButtonIcon");

    if (jvaflp_ajax_url.user_id === "0")
      window.location.href = jvaflp_ajax_url.login_url;

    likeButtonIcon.classList.remove("fa-heart");
    likeButtonIcon.classList.add("fa-spinner", "spinning");
    likeButton.classList.add("loading");

    $.ajax({
      url: jvaflp_ajax_url.ajax_url,
      type: "POST",
      dataType: "JSON",
      data: {
        action: "jvaflp_like_button_ajax",
        post_id: jvaflp_ajax_url.post_id,
        user_id: jvaflp_ajax_url.user_id,
      },
      success: function (res) {
        if (res.success) {
          res.data.is_liked
            ? likeButton.classList.add("liked")
            : likeButton.classList.remove("liked");

          likeButtonMsg.textContent = res.data.msg;

          likeButtonIcon.classList.add("fa-heart");
          likeButtonIcon.classList.remove("fa-spinner", "spinning");
          likeButton.classList.remove("loading");
        }
      },
      error: function (res) {
        console.log("Houve um erro");
        likeButtonIcon.classList.add("fa-heart");
        likeButtonIcon.classList.remove("fa-spinner", "spinning");
        likeButton.classList.remove("loading");
      },
    });
  });
}
