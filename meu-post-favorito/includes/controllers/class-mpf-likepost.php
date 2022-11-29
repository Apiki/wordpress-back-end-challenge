<?php
/**
 * MPF plugin file.
 *
 * @package MPF\Internals
 */

/**
 * Classe
 */
class MPF_LikePost {


	/**
	 *    Like
	 *
	 * @return Array
	 */

	public function like_post( $data ) {		
		global $wpdb;
		
		//checa se existe um post favoritado
		$results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mpf_likepost WHERE id_user = {$data['id_user']} and  post_id = {$data['post_id']} ");

		if($results){	
			$_delete =   $wpdb->delete( $wpdb->prefix.'mpf_likepost', array( 'post_id' => $data['post_id'], 'id_user' => $data['id_user'] ) );
			if($_delete){
				return "Removido dos seus favoritos!";
			}
		} else {
			//checa se o post existe antes de favoritar
			$results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts  WHERE id = {$data['post_id']} and  post_type = 'post' ");
			
			if($results){
				$_insert =  $wpdb->insert(
					$wpdb->prefix.'mpf_likepost',
					array(
						'post_id'=>$data['post_id'],
						'id_user'=>$data['id_user'],
					),
					array( '%s','%s' )
				 );
	
				if($_insert){
					return "Adicionado aos seus favoritos!";
				}else{
					return "Erro ao favoritar.";
				}
			} else {
				return "Post não econtrado.";			
			}
		}
	}

	/**
	 *  Check post like 
	 *
	 * @return Array
	 */
	public function check_like_post_on_list( $id_user, $id_post ) {		
		global $wpdb;

		$results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mpf_likepost WHERE id_user = {$id_user} and  post_id = {$id_post} ");
		
		if($results){
			return " liked";
		} else {
			return "";
		}
	}
}