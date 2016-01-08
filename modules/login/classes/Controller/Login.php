<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Controller
{

    public function before()
    {
        parent::before();
    }

    public function action_register()
    {
        $username = Security::xss_clean(Arr::get($_POST, 'username', ''));
        $email = Security::xss_clean(Arr::get($_POST, 'email', ''));
        $password = Security::xss_clean(Arr::get($_POST, 'password', ''));
        $password_confirm = Security::xss_clean(Arr::get($_POST, 'password_confirm', ''));
        //$error = false;
        //$res = Session::instance()->get('return');
        //if (!empty($res))
        //    $return = $res;
        //else
        //    $return="/";
        $template  = View::factory('register');
        $config = Kohana::$config->load('login');
        if (!Auth::instance()->logged_in()) {
            if (Request::$initial->method() == "POST") {
                //регистрация через соц сети
                if (!empty($_POST['token']) && $config['type']=='social') {
                    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
                    $ulogin = json_decode($s, true);
                    $ulogin['token'] = md5($ulogin['identity'] . str_shuffle('hdhHgth'));
                    $identity = $ulogin['network'] . '_' . $ulogin['uid'];
                    $user = ORM::factory('user')->where('username', '=', $identity)->or_where('email', 'LIKE', '%'.Arr::get($ulogin, 'email', str_shuffle('1NrJH60ksWlrn')))->find();
                    //проверка зарегистрирован ли пользователь с таким логином или такоц же почтой
                    if (!$user->loaded()) {
                        $pass = strtotime(date("Y-m-d H:i:s")) . $ulogin['token'];
                        $role = ORM::factory('role', 1);
                        $user = ORM::factory('user')->values(array('username' => $identity,
                            'password' => $pass,
                            'password_confirm' => $pass,
                            'email' => $ulogin['network'] . '_' . $ulogin['uid'] . '_' . Arr::get($ulogin, 'email', $identity . '@test.kz'),
                            'network_reg' => 1,
                        ))->save();
                        $user->add('roles', $role);
                        $photo = Storage::instance()->save_social_photo(Arr::get($ulogin, 'photo', ''),$user->pk());
                        ORM::factory('User_Profile')
                            ->values(array('user_id' => $user->pk(),
                            'first_name' => Arr::get($ulogin, 'first_name', ''),
                            'last_name' => Arr::get($ulogin, 'last_name', ''),
                            'photo' => $photo,
                            'email' => Arr::get($ulogin, 'email', '')
                        ))
                            ->save();
                        Auth::instance()->force_login($identity);
                    }else
                    {
                        Auth::instance()->force_login($user->username);
                    }
                    $this->redirect('/');
                } else {
                    if (Captcha::valid($_POST['captcha'])) {
                        try {
                            if ($config['confirmation']==1){
                                $date = date("Y-m-d H:i:s");
                                $code = md5($date . $password);

                                $user = ORM::factory('User')->values(array('username' => $username,
                                    'email' => $email,
                                    'password' => $password,
                                    'password_confirm' => $password_confirm,
                                    'network_reg' => 0,
                                    'link_activate' => $code,
                                ));
                                $extra_rules = Validation::factory($_POST)
                                    ->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
                                $user->save($extra_rules);
                                /*Email::connect();
                                Email::View('activate');
                                Email::set(array('username' => $username, 'id' => $code));
                                Email::send($email, array('xxx@xxx.ru', 'Site'), "Подтверждение регистрации на сайте Site", '', true);
                                */
                            }else
                            {
                                $user = ORM::factory('User')->values(array('username' => $username,
                                    'email' => $email,
                                    'password' => $password,
                                    'password_confirm' => $password_confirm,
                                    'network_reg' => 0,
                                ));
                                $extra_rules = Validation::factory($_POST)
                                    ->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
                                $user->save($extra_rules);
                                $role = ORM::factory('Role', 1);
                                $user->add('roles', $role);
                                Db::insert('user_profiles', array('user_id', 'email'))->values(array($user->pk(), $user->email))->execute();
                                Auth::instance()->force_login($user->username);
                                $this->redirect('/');
                            }
                        } catch (ORM_Validation_Exception $e) {
                            $mas_err = array();
                            $errors = $e->errors('models', true);
                            foreach ($errors as $error):
                                if (is_scalar($error)):
                                    $mas_err[] = $error;
                                else:
                                    foreach ($error as $err):
                                        $mas_err[] = $err;
                                    endforeach;
                                endif;
                            endforeach;
                            $template->set('errors', $mas_err);
                        }
                    } else {
                        $mas_err = array();
                        $mas_err[] = I18n::get("Неправильно ввели код подтверждения.");
                        $template->set('errors', $mas_err);
                    }
                }
            }
        }
        $template->set('username', $username)
            ->set('email', $email)
            ->set('url', 'http://' . $_SERVER['SERVER_NAME'] . '/' . Request::$initial->uri())
            ->set('return','/')->render();
        $this->response->body($template->render());
    }

    public function action_enter()
    {
        $username = Security::xss_clean(Arr::get($_POST, 'username', ''));
        $password = Security::xss_clean(Arr::get($_POST, 'password', ''));
        $remember = (bool)Arr::get($_POST, 'remember', false);
        $error = false;
        $template  = View::factory('enter');
        $config = Kohana::$config->load('login');
        $captcha = false;
        if (!Auth::instance()->logged_in()) {
            if ($this->request->method() == Request::POST) {
                if (!empty($_POST['token']) && $config['type']=='social') {
                    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
                    $ulogin = json_decode($s, true);
                    $ulogin['token'] = md5($ulogin['identity'] . 'hdhHgth');
                    $identity = $ulogin['network'] . '_' . $ulogin['uid'];
                    $user = ORM::factory('user')->where('username', '=', $identity)->or_where('email', 'LIKE', '%'.Arr::get($ulogin, 'email', '1NrJH60ksWlrn'))->find();
                    if (!$user->loaded()) {
                        $pass = strtotime(date("Y-m-d H:i:s")) . $ulogin['token'];
                        $role = ORM::factory('Role', 1);
                        $user = ORM::factory('User')->values(array('username' => $identity,
                            'password' => $pass,
                            'password_confirm' => $pass,
                            'email' => $ulogin['network'] . '_' . $ulogin['uid'] . '_' . Arr::get($ulogin, 'email', $identity . '@test.kz'),
                            'network_reg' => 1,
                        ))->save();
                        $user->add('roles', $role);
                        $photo = Storage::instance()->save_social_photo(Arr::get($ulogin, 'photo', ''),$user->pk());
                        ORM::factory('User_Profile')
                            ->values(array('user_id' => $user->pk(),
                                'first_name' => Arr::get($ulogin, 'first_name', ''),
                                'last_name' => Arr::get($ulogin, 'last_name', ''),
                                'photo' => $photo,
                                'email' => Arr::get($ulogin, 'email', '')
                            ))
                            ->save();
                        Auth::instance()->force_login($identity);
                    }else
                    {
                        Auth::instance()->force_login($user->username);
                    }
                    $this->redirect('/');
                } else {
                    $flag = true;
                    if (isset($_POST['captcha'])) {
                        if (!Captcha::valid($_POST['captcha'])) {
                            $flag = false;
                            $captcha = true;
                            $error = true;
                        }
                    }
                    if ($flag) {
                        //$token = Arr::get($_POST, 'token_auth', false);
                        //if (Security::token() === $token && Auth::instance()->login($username, $password, $remember)) {
                        if (Auth::instance()->login($username, $password, $remember)) {
                            $brute = ORM::factory('User_Bruteforce')->where('user_id', '=', Auth::instance()->get_user()->id)->find();
                            if ($brute->loaded())
                                ORM::factory('User_Bruteforce', $brute->id)->delete();
                            $this->redirect('/');
                        } else {
                            $user = ORM::factory('user')->where('username', '=', $username)->or_where('email','=',$username)->find();
                            if ($user->loaded()) {
                                $bruteforce = ORM::factory('User_Bruteforce')->where('user_id', '=', $user->id)->find();
                                if ($bruteforce->loaded()) {
                                    if ($bruteforce->attempt >= 1)
                                        $captcha = true;
                                    else {
                                        $brute = ORM::factory('User_Bruteforce', $bruteforce->id);
                                        $brute->attempt = $bruteforce->attempt + 1;
                                        $brute->save();
                                    }
                                } else {
                                    ORM::factory('User_Bruteforce')->values(array('user_id' => $user->id))->save();
                                }
                            }
                            $error = true;
                        }
                    }
                }
            }
        }
        else
        {
            $this->redirect('/');
        }
        $template->set('username', $username)->set('remember', $remember ? 'checked' : '')->set('url', 'http://' . $_SERVER['SERVER_NAME'] . '/' . Request::$initial->uri())
            ->set('error', $error)->set('captcha', $captcha)->set('return','/')->render();
        $this->response->body($template->render());
    }




    public function action_logout()
    {
        $token = Arr::get($_POST, 'token', false);
        if (Auth::instance()->logged_in() && Security::token()===$token) {
            Auth::instance()->logout();
        }
        $this->redirect('/');
    }

    public function action_reminder()
    {
        $reminder = false;
        $template  = View::factory('reminder');
        if (!Auth::instance()->logged_in()) {
            if ($this->request->method() == Request::POST) {
                if (Captcha::valid($_POST['captcha'])) {
                    $user = ORM::factory('User')->where('username', '=', Arr::get($_POST, 'username', ''))->find();

                    if ($user->loaded() && $user->network_reg==0 && empty($user->link_activate)) {
                        $date = date("Y-m-d H:i:s");
                        $code = md5($date . $user->password);

                        $save_code = ORM::factory('User', $user->id);
                        $save_code->link_recovery = $code;
                        $save_code->save();
                        $reminder = true;
                    } else {
                        $mas_err = array();
                        $mas_err[] = I18n::get("Пользователь с таким логином не зарегистрирован.");
                        $error = true;
                        $template->set('errors', $mas_err)->set('error', $error);
                    }
                } else {
                    $mas_err = array();
                    $mas_err[] = I18n::get("Неправильно ввели код подтверждения.");
                    $error = true;
                    $template->set('errors', $mas_err)->set('error', $error);
                }
            }
        }
        $template->set('reminder', $reminder)->set('return','/')->render();
        $this->response->body($template->render());
    }

    public function action_recovery()
    {
        $code = $this->request->param('id', '0');
        $user = ORM::factory('User')->where('link_recovery', '=', $code)->find();
        $template  = View::factory('recovery');
        $id = Arr::get($_POST, 'id', '');
        //$token = Arr::get($_POST, 'token_auth', false);
        //if (($this->request->method() == Request::POST) && Security::token() === $token){
        if (($this->request->method() == Request::POST)){
            try{
                $password = Security::xss_clean(Arr::get($_POST, 'password', ''));
                $password_confirm = Security::xss_clean(Arr::get($_POST, 'password_confirm', ''));

                if ($password==$password_confirm && !empty($password))
                {
                    $new_user_pass = ORM::factory('User',$id)
                        ->values(array(
                            'password' => $password,
                            'password_confirm' => $password_confirm,
                            'link_recovery' =>'',
                        ));
                    $extra_rules = Validation::factory($_POST)
                        ->rule('password', 'not_empty')
                        ->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
                    $new_user_pass->save($extra_rules);
                    $template->set('ok', true);
                    //$this->redirect('/');
                }
                else
                {
                    if ($password!=$password_confirm)
                        $mas_err[] = I18n::get('Подтверждение пароля должно совпадать с паролем');
                    else
                        $mas_err[] = I18n::get('Пароль не должно быть пустым');
                    $template->set('errors', $mas_err)->set('id',$id);
                }
            }catch (ORM_Validation_Exception $e) {
                $mas_err = array();
                $errors = $e->errors('validations', true);
                foreach ($errors as $error):
                    if (is_scalar($error)):
                        $mas_err[] = $error; else:
                        foreach ($error as $err):
                            $mas_err[] = $err;
                        endforeach;
                    endif;
                endforeach;
                $template->set('errors', $mas_err)->set('id',$id);
            }
        }
        else
        {
            if (!empty($code)) {
                if (!$user->loaded())
                {
                    throw new HTTP_Exception_404('Page not found');
                }
            } else {
                throw new HTTP_Exception_404('Page not found');
            }
            $template->set('id',$user->id);
        }
        $this->response->body($template->render());
    }

    public function action_activate()
    {
        $code = $this->request->param('id', '0');
        $user = ORM::factory('User')->where('link_activate', '=', $code)->find();
        if (!empty($code)) {
            if ($user->loaded())
            {
                $role = ORM::factory('Role', 1);
                $activate_user = ORM::factory('User',$user->id);
                $activate_user->link_activate = '';
                $activate_user->save();
                $activate_user->add('roles', $role);
                Db::insert('user_profiles', array('user_id', 'email'))->values(array($user->pk(), $user->email))->execute();
                Auth::instance()->force_login($user->username);
                $this->redirect('/');
            }
            else
                throw new HTTP_Exception_404('Page not found');
        } else
            throw new HTTP_Exception_404('Page not found');
    }
}