<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Private extends Controller_Api_Core{

    protected $user_id;

    public function before()
    {
        parent::before();

        $this->check_token ();
    }


    /*
     * Проверка токена
     */
    public function check_token ()
    {
        if( empty($this->auth_token) OR !$this->api->token_expires($this->auth_token) )
        {
            $this->data['error'] = 'Invalid token';
            echo json_encode($this->data);
            die();
        }

        $user = ORM::factory('Api_Token')->where('token', '=', $this->auth_token)->find();
        if(!$user->loaded())
        {
            $this->data['error'] = 'Invalid token';
            echo json_encode($this->data);
            die();
        }

        $this->user_id = $user->user_id;
    }


}