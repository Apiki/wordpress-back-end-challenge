<?php
/**
 * Created by PhpStorm.
 * User: Guilherme
 * Date: 03/07/2018
 * Time: 23:00
 */

include_once(ABSPATH . 'wp-includes/pluggable.php');

class ApiController
{
    public function __construct() {
        $this->namespace     = '/api_fav';
    }
    public function register_routes(){
        register_rest_route( $this->namespace, '/Insert', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'add_fav' )
            ),
        ) );

        register_rest_route( $this->namespace,  '/HelloWorld', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'hello' )
            ),
        ) );
        register_rest_route( $this->namespace, '/Delete', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'delete_fav' )
            ),
        ) );
    }
    public function hello(){
        $message = array('message'=>'Olá Mundo.','sucess'=>true);
        return rest_ensure_response( $message );
    }
    private function validateRequest($request){
        $obj = new stdClass();
        $obj->sucess = true;
        $obj->message = 'ok';
        if(!isset($request['post_id']) || !isset($request['user_id'])){
            $obj->sucess = false;
            $obj->message = 'Dados incorretos.';
        }
        $post_id = (int) $request['post_id'];
        $user_id = (int) $request['user_id'];
        $post = get_post($post_id );
        if(empty($post)){
            $obj->sucess = false;
            $obj->message = 'Post Incorreto.';
        }
        $user = get_user_by('id',$user_id);
        if(empty($user)){
            $obj->sucess = false;
            $obj->message = 'Usuário Incorreto';
        }
        return $obj;
    }

    public function add_fav($request){
        $validate = $this->validateRequest($request);
        if(!$validate->sucess){
            $message = array('message'=>$validate->message,'sucess'=>$validate->sucess);
            return rest_ensure_response( $message );
        }
        $post_id = (int) $request['post_id'];
        $user_id = (int) $request['user_id'];

        $db = new DBUtil();
        $db->insertDB($post_id,$user_id);
        $message = array('message'=>'Add com sucesso.','sucess'=>true);
        return rest_ensure_response( $message );

    }
    public function delete_fav($request){
        $validate = $this->validateRequest($request);
        if(!$validate->sucess){
            $message = array('message'=>$validate->message,'sucess'=>$validate->sucess);
            return rest_ensure_response( $message );
        }
        $post_id = (int) $request['post_id'];
        $user_id = (int) $request['user_id'];
        $db = new DBUtil();
        $db->deleteDB($post_id,$user_id);
        $message = array('message'=>'Add com sucesso.','sucess'=>true);
        return rest_ensure_response( $message );
    }


}