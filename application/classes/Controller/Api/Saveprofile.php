<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Saveprofile extends Controller_Api_Private{

    public function action_index()
    {
        $name = '';
        $surname = '';
        $specialization = '';
        $about = '';

        if (!empty($this->post))
        {
            $name = $this->post['name'];
            $surname = $this->post['surname'];
            $specialization = $this->post['position'];
            $about = $this->post['about'];
            $photo = Arr::get($this->post, 'photo', '');

            $user = ORM::factory('User_Profile')->where('user_id', '=', $this->user_id)->find();
            if (!$user->loaded())
            {
                $mes['error'] = 'No user loaded';
                $this->response->body(json_encode($mes));
            }else
            {
                //сохраняем или изменяем пользователя
                if ($user->first_name != $name){
                    $user->set('first_name', $name);
                }
                if ($user->last_name != $surname){
                    $user->set('last_name',$surname);
                }
                if ($user->specialization != $specialization){
                $user->set('specialization', $specialization);
                }
                if ($user->about != $about){
                    $user->set('about', $about);
                }
                $user->save();

                if( !empty($photo) )
                {
                    $new_name = md5(date('Y:m:d H:i:s')).'.png';
                    $path = $this->api->get_path($this->user_id) . '/' . $new_name;

                    $photo = base64_decode($photo);

                    $file = fopen($path, "w");
                    $result = fwrite($file, $photo);

                    $info = getimagesize($path);

                    if($result)
                    {
                        $storage = ORM::factory('Storage');
                        $storage->name      = 'безымянный файл';
                        $storage->file_path = $path;
                        $storage->user_id   = $this->user_id;
                        $storage->date      = date('Y-m-d H:i:s');
                        $storage->mime      = $info['mime'];
                        $storage->type      = 'png';

                        $storage->save();

                        $profile = ORM::factory('User_Profile')->where('user_id', '=', $this->user_id)->find();
                        $profile->photo = $storage->id;
                        $profile->update();

                    }

                    fclose($file);

                    $this->response->body(json_encode($info));
                }

                if ($user->saved() or ($user->first_name == $name and $user->last_name == $surname and
                        $user->specialization == $specialization and $user->about == $about)) {
                    $this->response->body(json_encode('true'));
                } else {
                    $this->response->body(json_encode('false'));
                }
            }
        }
    }

}