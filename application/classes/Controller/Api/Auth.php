<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Auth extends Controller_Api_Core{

    /*
     * Авторизация пользователя
     */
    public function action_enter()
    {
        $avatar = '';
        $post=$this->post;
        $username = Security::xss_clean(Arr::get($post, 'username', ''));
        $password = Security::xss_clean(Arr::get($post, 'password', ''));
        $social_token=Security::xss_clean(Arr::get($post, 'token', ''));

        if (!Auth::instance()->logged_in())
        {
            if( (!empty($post)) || ($social_token!='') )
            {
                /* авторизация через соц. сети */
                if ($social_token!='')
                {
                    $s = file_get_contents('http://ulogin.ru/token.php?token=' .$social_token . '&host=' . $_SERVER['HTTP_HOST']);
                    $ulogin = json_decode($s, true);
                    if( empty($ulogin['error']) )
                    {
                        $identity = $ulogin['network'] . '_' . $ulogin['uid'];
                        $user = ORM::factory('user')->where('username', '=', $identity)->or_where('email', 'LIKE', '%'.Arr::get($ulogin, 'email', '1NrJH43ksWlrn'))->find();

                        if (!$user->loaded())
                        {
                            $pass = strtotime(date("Y-m-d H:i:s")) . $social_token;
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

                        $user_id = (int) Auth::instance()->get_user()->id;

                        /* проверяем есть ли у пользователя токен для доступа к закрытым данным через api */
                        $token=ORM::factory('Api_Token')->where('user_id', '=', $user_id)->find();

                        /* если есть токен для данного пользователя в таблице, обнавляем время жизни токена */
                        if($token->loaded())
                        {
                            /* если токен еще живой */
                            if($this->api->token_expires($token->token))
                            {
                                $api_token = $token->token;
                                $token->update_token();
                            }
                            /* если токен мертвый генерируем новый */
                            else
                            {
                                $api_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
                                $token->update_token($api_token);
                            }
                        }
                        /* если нет токена для данного пользователя, генрируем токен и сохраняем */
                        else
                        {
                            $api_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
                            $this->api->create_token($user_id, $api_token);
                        }
                        $user = Auth::instance()->get_user();
                        $user_id = $user->id;
                        if( !empty($user->profile->img->file_path) )
                        {
                            $avatar = 'http://'.$_SERVER['SERVER_NAME'] . '/' . $user->profile->img->file_path;
                        }

                        $notice = ORM::factory('Api_Notice')->where('user_id', '=', $user_id)->and_where('flag', '=', 0)->order_by('date', 'desc')->group_by('object_id')->find_all();

                        if( $notice->count() >0 )
                        {
                            $notifCount  = $notice->count();
                        }
                        else
                        {
                            $notifCount  = 0;
                        }

                        $this->data['tokenAuth'] = $api_token;
                        $this->data['userProfile'] = array(
                            'userId'     => $user_id,
                            'login'      => $user->show_name(),
                            'name'       => $user->profile->first_name,
                            'surname'    => $user->profile->last_name,
                            'position'   => $user->get_specialization(),
                            'photoUrl'   => $avatar,
                            'about'      => $user->profile->about,
                            'notifCount' => $notifCount
                        );
                    }
                    else
                    {
                        $this->data['error'] = $ulogin['error'];
                    }
                }
                else
                {
                    /* авторизация */
                    if ( Auth::instance()->login($username, $password) )
                    {
                        $user = Auth::instance()->get_user();
                        $user_id = (int) $user->id;

                        /* проверяем есть ли у пользователя токен для доступа к закрытым данным через api */
                        $token=ORM::factory('Api_Token')->where('user_id', '=', $user_id)->find();

                        /* если есть токен для данного пользователя в таблице, обнавляем время жизни токена */
                        if($token->loaded())
                        {
                            /* если токен еще живой */
                            if($this->api->token_expires($token->token))
                            {
                                $api_token = $token->token;
                                $token->update_token();
                            }
                            /* если токен мертвый генерируем новый */
                            else
                            {
                                $api_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
                                $token->update_token($api_token);
                            }
                        }
                        /* если нет токена для данного пользователя, генрируем токен и сохраняем */
                        else
                        {
                            $api_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
                            $this->api->create_token($user_id, $api_token);
                        }

                        if( !empty($user->profile->img->file_path) )
                        {
                            $avatar = 'http://'.$_SERVER['SERVER_NAME'] . '/' . $user->profile->img->file_path;
                        }

                        $notice = ORM::factory('Api_Notice')->where('user_id', '=', $user_id)->and_where('flag', '=', 0)->order_by('date', 'desc')->group_by('object_id')->find_all();

                        if( $notice->count() >0 )
                        {
                            $notifCount  = $notice->count();
                        }
                        else
                        {
                            $notifCount  = 0;
                        }

                        $this->data['tokenAuth'] = $api_token;
                        $this->data['userProfile'] = array(
                            'userId'     => $user_id,
                            'login'      => $user->show_name(),
                            'name'       => $user->profile->first_name,
                            'surname'    => $user->profile->last_name,
                            'position'   => $user->get_specialization(),
                            'photoUrl'   => $avatar,
                            'about'      => $user->profile->about,
                            'notifCount' => $notifCount
                        );
                    }
                    else
                    {
                        $this->data['error'] = 'Incorrect login or password';
                    }
                }
            }
        }
        /* Если пользователь залогинен вернем id */
        else
        {
            $user = Auth::instance()->get_user();
            $user_id = (int) $user->id;
            if( !empty($user->profile->img->file_path) )
            {
                $avatar = 'http://'.$_SERVER['SERVER_NAME'] . '/' . $user->profile->img->file_path;
            }
            $token=ORM::factory('Api_Token')->where('user_id', '=', $user_id)->find();
            $this->data['tokenAuth'] = $token->token;
            $notice = ORM::factory('Api_Notice')->where('user_id', '=', $user_id)->and_where('flag', '=', 0)->order_by('date', 'desc')->group_by('object_id')->find_all();

            if( $notice->count() >0 )
            {
                $notifCount  = $notice->count();
            }
            else
            {
                $notifCount  = 0;
            }
            $this->data['userProfile'] = array(
                'userId'      => $user_id,
                'login'       => $user->show_name(),
                'name'        => $user->profile->first_name,
                'surname'     => $user->profile->last_name,
                'position'    => $user->get_specialization(),
                'photoUrl'    => $avatar,
                'about'       =>  $user->profile->about,
                'notifCount'  => $notifCount
            );
        }

        $this->response->body(json_encode($this->data));
    }

    public function action_register()
    {
        $post=$this->post;
        $username = Security::xss_clean(Arr::get($post, 'login', ''));
        $email = Security::xss_clean(Arr::get($post, 'email', ''));
        $password = Security::xss_clean(Arr::get($post, 'password', ''));
        $password_confirm = Security::xss_clean(Arr::get($post, 'password', ''));

        $social_token=Security::xss_clean(Arr::get($post, 'token', ''));

        if (!Auth::instance()->logged_in())
        {
            if ( (!empty($post)) || ($social_token!='') )
            {
                if ($social_token!='')
                {
                    $s = file_get_contents('http://ulogin.ru/token.php?token=' .$social_token . '&host=' . $_SERVER['HTTP_HOST']);
                    /* узнать как будет выглядеть массив от клиента с данными из соц сети */
                    $ulogin = json_decode($s, true);
                    //var_dump($ulogin);
                    //$ulogin = Security::xss_clean(Arr::get($post, 'ulogin', ''));
                    $identity = $ulogin['network'] . '_' . $ulogin['uid'];
                    $user = ORM::factory('user')->where('username', '=', $identity)->or_where('email', 'LIKE', '%'.Arr::get($ulogin, 'email', '1NrJH43ksWlrn'))->find();

                    if (!$user->loaded())
                    {
                        $pass = strtotime(date("Y-m-d H:i:s")) . $social_token;
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
                        //Auth::instance()->force_login($identity);
                        //успешная регистрация
                        $this->data['socialReg']=true;
                    }
                    else
                    {
                        $this->data['error'] = 'Social user is already registered';
                        //Auth::instance()->force_login($user->username);
                        //Пользоатель уже существует
                    }
                }
                else
                {
                    $findusername=ORM::factory('User')->where('username','=',$username)->find_all()->count();
                    if ($findusername==0){
                        $findemail=ORM::factory('User')->where('email','=',$email)->find_all()->count();
                        if ($findemail==0){
                            $date = date("Y-m-d H:i:s");
                            $code = md5($date . $password);
                            $user = ORM::factory('User')->values(array('username' => $username,
                                'email' => $email,
                                'password' => $password,
                                'password_confirm' => $password_confirm,
                                'network_reg' => 0,
                                'link_activate' => $code,
                            ));
                                $user->save();
                                Email::connect();
                                Email::View('activate');
                                Email::set(array('username' => $username, 'id' => $code, 'url' =>URL::site('/', true) ));
                                Email::send($email, array('no-reply@e-history.kz', 'e-history.kz'), "Подтверждение регистрации на сайте E-history.kz", '', true);
                                $this->data[]=true;
                            //Успешная простая регистрация
                        } else{
                                $this->data['error'] = 'Email is already registered';
                            //такой ящик уже есть
                        }
                    } else{
                        $this->data['error'] = 'Login is already registered';
                    }


                        //Message::success('На указанный email отправлено письмо со ссылкой на подтверждение регистрации.');
                }
            }
        }
        $this->response->body(json_encode($this->data));
    }

    public function action_logout()
    {
        if(!empty($this->auth_token) AND $this->api->token_expires($this->auth_token))
        {
            $token = ORM::factory('Api_Token')->where('token', '=', $this->auth_token)->find();
            $token->delete();
            if (Auth::instance()->logged_in())
            {
                Auth::instance()->logout();
            }

            $this->data = true;
        }
        else
        {
            $this->data['error'] = 'Invalid token';
        }

        $this->response->body(json_encode($this->data));
    }

    public function action_wit(){
        $this->response->body(file_get_contents('php://input'));
    }
}