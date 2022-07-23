<?php
// Coloque este pedaço de código em algum lugar do script single.php do tema WordPress para ter o link de Favoritar/Desfavoritar
$user_id = 0;
if ( is_user_logged_in() ) {
	$user_id = get_current_user_id();
}

global $wpdb;
$table_des_Favoritar = $wpdb->prefix . "des_Favoritar";

$resultsdes_Favoritar = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT *
        FROM $table_des_Favoritar
        WHERE post_id =  $post->ID
        AND user_id = $user_id"
    )
);
if (count($resultsdes_Favoritar) == 0) {
	$strLabel = 'Favoritar';
} else {
	$strLabel = 'Desfavoritar';
}

$nonce = wp_create_nonce("user_des_Favoritar_nonce");
$linkDesFavoritar = admin_url('admin-ajax.php?action=des_Favoritar&post_id='.$post->ID.'&nonce='.$nonce);
echo '<a id="des_Favoritar" data-nonce="' . $nonce . '" data-post_id="' . $post->ID . '" href="' . $linkDesFavoritar . '">'.$strLabel.'</a> <span id="errorFavoritar"></span>';
?>