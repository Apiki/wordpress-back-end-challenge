$j = jQuery.noConflict();
$j(document).ready(function () {

  var namespace = "/apikirestfavorite/v1/posts/";
  var getUrl = window.location;
  var baseUrl =
    getUrl.protocol +
    "//" +
    getUrl.host +
    "/" +
    getUrl.pathname.split("/")[1] +
    "/" +
    getUrl.pathname.split("/")[2] +
    "/wp-json";

  $j(".favoriteCheckbox").change(function () {

    var postId = $j(this).val();

    if ($j(this).is(":checked")) {
      $j.ajax({
        url: baseUrl + namespace + postId,
        method: "POST",
        data: JSON.stringify({
          user : apikiScriptVars.user
        }),
        credentials: 'include',
        contentType: "application/json",
        beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', apikiScriptVars.nonce );
        },         
        success: function (result) {
        },
        error: function (request, msg, error) {
        },
      });
    } else {

      $j.ajax({
        url: baseUrl + namespace + postId,
        method: "DELETE",
        data: JSON.stringify({
          user : apikiScriptVars.user
        }),
        credentials: 'include',
        contentType: "application/json",
        beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', apikiScriptVars.nonce );
        },         
        success: function (result) {
        },
        error: function (request, msg, error) {
        },
      });
    }
  });

  $j(".favoriteCheckbox").each(function() {

    addEventListener("load",  () => {

      var postId = $j(this).val();
      var element = this;

      $j.ajax({
        url: baseUrl + namespace + postId + '/' +apikiScriptVars.user,
        method: "GET",
        credentials: 'include',
        beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', apikiScriptVars.nonce );
        },         
        success: function (result) {
          if (result.response == 'TRUE') {
            document.getElementById(element.id).setAttribute("checked", '');
          }
        },
        error: function (request, msg, error) {
        },
      });
    });
  });
})