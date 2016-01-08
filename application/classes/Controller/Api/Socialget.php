<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Socialget extends Controller_Api_Private{

    public function action_index()
    {
        /* Все соц сети*/
        $social = ORM::factory('User_Social')->where('user_id', '=', $this->user_id)->find_all();

        if( count($social->as_array(null,'id'))!=0 )
        {
            foreach ($social as $item){
                $this->data[]=array(
                    'socialType'=>strtoupper($item->social),
                    'link'=>$item->link
                );
            }
        }
        $this->response->body(json_encode($this->data));
    }

}