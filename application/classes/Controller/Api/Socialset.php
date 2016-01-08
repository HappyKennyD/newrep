<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Socialset extends Controller_Api_Private
{

    public function action_index()
    {
        $social_type = '';
        $link = '';
        $i = 0; //считаем, сколько социалок записалось
        //Получаем список соц сетей
        $user_socials = ORM::factory('User_Social')->where('user_id', '=', $this->user_id)->find_all();
        foreach ($user_socials as $soc) {
            $soc->delete();
        }
        foreach ($this->post as $var) {
            $social_type = $var['socialType'];
            $link = $var['link'];
            $user_new_soc = ORM::factory('User_Social')->where('link', '=', $link)->find();
            if (!$user_new_soc->loaded()) {
                $social = ORM::factory('User_Social')
                    ->set('user_id', $this->user_id)
                    ->set('social', $social_type)
                    ->set('link', $link)
                    ->save();
                $i++;
            }
        }

        $this->response->body(json_encode(true));


    }
}