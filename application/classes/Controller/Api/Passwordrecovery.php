<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Passwordrecovery extends Controller_Api_Core
{

    public function action_index()
    {
        $email=Security::xss_clean(Arr::get($this->post, 'email', ''));

        $user = ORM::factory('User')->where('email', '=', $email)->find();
        if ($user->loaded() && $user->network_reg==0 && empty($user->link_activate))
        {
            $date = date("Y-m-d H:i:s");
            $code = md5($date . $user->password);
            Email::connect();
            Email::View('reminderapi');
            Email::set(array('username' => $user->username, 'id' => $code, 'url' => URL::media($this->language.'/auth/recovery/',true)));
            Email::send($user->email, array('no-reply@e-history.kz', 'e-history.kz'), "E-history.kz, ссылка для смены пароля.", '', true);
            $save_code = ORM::factory('User', $user->id);
            $save_code->link_recovery = $code;
            $save_code->save();
            $this->data=true;
        }
        else
        {
            $this->data['error'] = 'Email is not registered';
        }

        $this->response->body(json_encode($this->data));
    }

}