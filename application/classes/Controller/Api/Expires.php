<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Expires extends Controller_Api_Core{

    public function action_index()
    {
        $expires_time=ORM::factory('Api_Token')->where('token','=', Security::xss_clean(Arr::get($this->post, 'tokenAuth', 0)))->find();
        if ($expires_time->loaded()){
            if ($this->api->token_expires($this->post['tokenAuth'], $interval=172800)){
                $this->data[]=true;
            } else {
                $this->data[]=false;
            }
        } else{
            $this->data[]=false;
        }
        $this->response->body(json_encode($this->data));
    }

}