<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Profile extends Controller_Api_Core
{

    public function action_index()
    {
        $id = $this->request->param('id',null);
        $user = ORM::factory('User')->where('id','=',$id)->find();
        if (!$user->loaded())
        {
            $data = array(
                'error' => 'Profile is not exists',
            );
        }
        else{
        $data['login']      =  $user->username;
        $data['name']       =  $user->profile->first_name;
        $data['surname']    =  $user->profile->last_name;
        $data['position']   =  $user->profile->specialization;
        $data['about']      =  $user->profile->about;
        if ($user->profile->img->file_path != ''){
            $data['photoUrl']   =  URL::site('/',true).$user->profile->img->file_path;
        }else{
            $data['photoUrl']   = '';
        }
        }

        $this->response->body(json_encode($data));
    }
}
