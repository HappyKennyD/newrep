<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Auth extends Controller_Core
{

    public function action_enter()
    {
        $username = Security::xss_clean(Arr::get($_POST, 'username', ''));
        $password = Security::xss_clean(Arr::get($_POST, 'password', ''));
        $res = Session::instance()->get('return');
        if (!empty($res))
            $return = $res;
        else
            $return="/";
        $captcha = false;
        $errors = NULL;

        if (!Auth::instance()->logged_in())
        {
            if ($this->request->post())
            {
                if (!empty($_POST['token']))
                {
                    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
                    $ulogin = json_decode($s, true);
                    $ulogin['token'] = md5($ulogin['identity'] . 'hdhHgth');
                    $identity = $ulogin['network'] . '_' . $ulogin['uid'];
                    $user = ORM::factory('user')->where('username', '=', $identity)->or_where('email', 'LIKE', '%'.Arr::get($ulogin, 'email', '1NrJH43ksWlrn'))->find();
                    if (!$user->loaded())
                    {
                        $pass = strtotime(date("Y-m-d H:i:s")) . $ulogin['token'];
                        $role = ORM::factory('Role', 1);
                        $user = ORM::factory('User')->values(array('username' => $identity,
                                                                  'password' => $pass,
                                                                  'password_confirm' => $pass,
                                                                  'email' => $ulogin['network'] . '_' . $ulogin['uid'] . '_' . Arr::get($ulogin, 'email', $identity . '@test.kz'),
                                                                  'network_reg' => 1,
                                                             ))->save();
                        $user->add('roles', $role);
                        if ($ulogin['network']!="yandex")
                        {
                            $photo = Storage::instance()->save_social_photo(Arr::get($ulogin, 'photo', ''),$user->pk());
                        }
                        else
                        {
                            $photo=0;
                        }
                        ORM::factory('User_Profile')
                            ->values(array('user_id' => $user->pk(),
                                          'first_name' => Arr::get($ulogin, 'first_name', ''),
                                          'last_name' => Arr::get($ulogin, 'last_name', ''),
                                          'photo' => $photo,
                                          'phone' => Arr::get($ulogin, 'phone', ''),
                                          'email' => Arr::get($ulogin, 'email', ''),
                                          'location_id' => 0
                                     ))
                            ->save();
                        Auth::instance()->force_login($identity);
                    }
                    else
                    {
                        Auth::instance()->force_login($user->username);
                    }

                    $this->redirect('/kabinet', 301);
                }
                else
                {
                    if (isset($_POST['captcha']))
                    {
                        if (!Captcha::valid($_POST['captcha']))
                        {
                            $captcha = true;
                            $errors['captcha'] = I18n::get("Неправильно ввели код подтверждения.");
                        }
                    }
                    if (!isset($errors)) {
                        $token = Arr::get($_POST, 'token_auth', false);
                        if (Security::token() === $token && Auth::instance()->login($username, $password))
                        {
                            $brute = ORM::factory('User_Bruteforce')->where('user_id', '=', Auth::instance()->get_user()->id)->find();
                            if ($brute->loaded())
                                ORM::factory('User_Bruteforce', $brute->id)->delete();

                            $this->redirect($return);
                        }
                        else
                        {
                            $user = ORM::factory('user')->where('username', '=', $username)->or_where('email','=',$username)->find();
                            if ($user->loaded())
                            {
                                $bruteforce = ORM::factory('User_Bruteforce')->where('user_id', '=', $user->id)->find();
                                if ($bruteforce->loaded())
                                {
                                    if ($bruteforce->attempt >= 1)
                                    {
                                        $captcha = true;
                                    }
                                    else
                                    {
                                        $brute = ORM::factory('User_Bruteforce', $bruteforce->id);
                                        $brute->attempt = $bruteforce->attempt + 1;
                                        $brute->save();
                                    }
                                }
                                else
                                {
                                    ORM::factory('User_Bruteforce')->values(array('user_id' => $user->id))->save();
                                }
                            }

                            $errors['username'] = I18n::get("Имя пользователя или пароль не верны.");
                        }
                    }
                }
            }
        }
        else
        {
                $this->redirect('/',301);
        }
        $this->set('url', 'http://' . $_SERVER['SERVER_NAME'] . '/' . Request::$initial->uri())
            ->set('errors', $errors)->set('captcha', $captcha)->set('return',$return);
    }

    public function action_register()
    {
        $username = Security::xss_clean(Arr::get($_POST, 'username', ''));
        $email = Security::xss_clean(Arr::get($_POST, 'email', ''));
        $class = Security::xss_clean(Arr::get($_POST, 'class', ''));
        $vuz = Security::xss_clean(Arr::get($_POST, 'vuz', ''));
        $radio = Security::xss_clean(Arr::get($_POST, 'radio', ''));
        $password = Security::xss_clean(Arr::get($_POST, 'password', ''));
        $password_confirm = Security::xss_clean(Arr::get($_POST, 'password_confirm', ''));
        $errors = NULL;
        $res = Session::instance()->get('return');
        if (!empty($res))
            $return = $res;
        else
            $return="/";
        if (!Auth::instance()->logged_in())
        {
            if ($this->request->post())
            {
                if (!empty($_POST['token']))
                {
                    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
                    $ulogin = json_decode($s, true);
                    $ulogin['token'] = md5($ulogin['identity'] . 'hdhHgth');
                    $identity = $ulogin['network'] . '_' . $ulogin['uid'];
                    $user = ORM::factory('user')->where('username', '=', $identity)->or_where('email', 'LIKE', '%'.Arr::get($ulogin, 'email', '1NrJH43ksWlrn'))->find();
                    if (!$user->loaded())
                    {
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
                                          'phone' => Arr::get($ulogin, 'phone', ''),
                                          'email' => Arr::get($ulogin, 'email', ''),
                                          'location_id' => 0
                                     ))
                            ->save();
                        Auth::instance()->force_login($identity);
                    }
                    else
                    {
                        Auth::instance()->force_login($user->username);
                    }
                    //запомнить токен для соц сети

                    $this->redirect($return,301);
                }
                else
                {
                    if (Captcha::valid($_POST['captcha']))
                    {
                        try
                        {
                            //$role = ORM::factory('Role', 1);
                            $date = date("Y-m-d H:i:s");
                            $code = md5($date . $password);
                            $user = ORM::factory('User')->values(array('username' => $username,
                                                                      'email' => $email,
                                                                      'class' => $class,
                                                                      'vuz' => $vuz,
                                                                      'role' => $radio,
                                                                      'password' => $password,
                                                                      'password_confirm' => $password_confirm,
                                                                      'network_reg' => 0,
                                                                      'link_activate' => $code,
                                                                 ));
                            $extra_rules = Validation::factory($_POST)
                                ->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));

                            if ($extra_rules->check())
                            {
//                                die($password);
                                $user->save();
                                Email::connect();
                                Email::View('activate');
                                Email::set(array('username' => $username, 'id' => $code, 'url' => str_replace('/auth/register','',URL::current(true))));
                                Email::send($email, array('no-reply@e-history.kz', 'e-history.kz'), "Подтверждение регистрации на сайте shkolkovo.kz", '', true);
                                Message::success('На указанный email отправлено письмо со ссылкой на подтверждение регистрации.');
                                $this->redirect('/',301);
                            }
                            else
                            {
                                $errors = $extra_rules->errors('validation');
                            }
                        }
                        catch (ORM_Validation_Exception $e)
                        {
                            $errors = $e->errors($e->alias());
                        }
                    }
                    else
                    {
                        $errors['captcha'] = I18n::get("Неправильно ввели код подтверждения.");
                    }
                }
            }
        }
        $this->set('username', $username)
            ->set('email', $email)
            ->set('url', 'http://' . $_SERVER['SERVER_NAME'] . '/' . Request::$initial->uri())
            ->set('errors', $errors)
            ->set('return', $return);
    }

    public function action_logout()
    {
        $token = Arr::get($_POST, 'token', false);
        if (Auth::instance()->logged_in() && Security::token()===$token) {
            Auth::instance()->logout();
        }
        $this->redirect('/',301);
    }

    public function action_reminder()
    {
        $errors = NULL;
        if (!Auth::instance()->logged_in())
        {
            if ($this->request->post())
            {
                if (Captcha::valid($_POST['captcha']))
                {
                    $user = ORM::factory('User')->where('username', '=', Arr::get($_POST, 'username', ''))->find();
                    if ($user->loaded() && $user->network_reg==0 && empty($user->link_activate))
                    {
                        $date = date("Y-m-d H:i:s");
                        $code = md5($date . $user->password);
                        Email::connect();
                        Email::View('reminder');
                        Email::set(array('username' => $user->username, 'id' => $code, 'url' => str_replace('/auth/reminder','',URL::current(true))));
                        Email::send($user->email, array('no-reply@e-history.kz', 'e-history.kz'), "E-history.kz, ссылка для смены пароля.", '', true);
                        $save_code = ORM::factory('User', $user->id);
                        $save_code->link_recovery = $code;
                        $save_code->save();
                        Message::success('Ссылка для восстановления пароля отправлена на указанный при регистрации адрес электронной почты.');
                        $this->redirect('/',301);
                    }
                    else
                    {
                        $errors['login'] = I18n::get("Пользователь с таким логином не зарегистрирован.");
                    }
                }
                else
                {
                    $errors['captcha'] = I18n::get("Неправильно ввели код подтверждения.");
                }
            }
        }
        $this->set('errors',$errors);
    }

    public function action_recovery()
    {
        $code = $this->request->param('id', '0');
        $user = ORM::factory('User')->where('link_recovery', '=', $code)->find();
        $token = Arr::get($_POST, 'token_auth', false);
        $errors = NULL;
        if ($this->request->post() && Security::token() === $token)
        {
            $code = Arr::get($_POST, 'code', '');
            $user = ORM::factory('User')->where('link_recovery', '=', $code)->find();
            if (!$user->loaded())
            {
                throw new HTTP_Exception_404('Page not found');
            }
            try
            {
                $password = Security::xss_clean(Arr::get($_POST, 'password', ''));
                $password_confirm = Security::xss_clean(Arr::get($_POST, 'password_confirm', ''));
                if ($password==$password_confirm && !empty($password))
                {
                    $new_user_pass = ORM::factory('User',$user->id)
                        ->values(array(
                                      'password' => $password,
                                      'password_confirm' => $password_confirm,
                                      'link_recovery' =>'',
                                 ));
                    $extra_rules = Validation::factory($_POST)
                        ->rule('password', 'not_empty')
                        ->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
                    if ($extra_rules->check())
                    {
                        $new_user_pass->save();
                        Message::success('Пароль успешно изменен. Теперь вы можете войти на сайт с новым паролем.');
                        $this->redirect('/',301);
                    }
                    else
                    {
                        $errors = $extra_rules->errors('validation');
                    }
                }
                else
                {
                    if ($password!=$password_confirm)
                        $errors['password_confirm'] = I18n::get("Подтверждение пароля должно совпадать с паролем.");
                    else
                        $errors['password'] = I18n::get("Пароль не должно быть пустым");
                }
            }catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
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
        }
        $this->set('errors',$errors);
        $this->set('code', $code);
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
                Message::success('Регистрация на портале e-history.kz успешно подтверждена.');
                $this->redirect('/kabinet',301);
            }
            else
                throw new HTTP_Exception_404('Page not found');
        } else
            throw new HTTP_Exception_404('Page not found');
    }
}