<?php
class Controller_Manage_Users extends Controller_Manage_Core
{
    protected $page;

    protected $title = 'Пользователи';

    public function before()
    {
        parent::before();
        $input = $this->user->has('roles', ORM::factory('role', array('name' => 'admin')));
        if (!$input) {
            $this->redirect('manage');
        }

        $this->page = Security::xss_clean( (int) $this->request->param('page', 0) );
        if( empty($this->page) )
        {
            $this->page=1;
        }
    }

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        $this->set('search', $search);

        $users = ORM::factory('User')->with('Profile');

        //поиск
        if (!empty($search)) {
            $ids = array(0);
            $profiles = ORM::factory('User_Profile');
            $query_m = DB::expr(' MATCH(first_name, last_name, email) ');
            $query_a = DB::expr(' AGAINST("' . addslashes($search) . '") ');
            $profiles->where($query_m, '', $query_a);
            $profiles = $profiles->find_all()->as_array();

            foreach ($profiles as $profile) {
                $ids[] = $profile->user_id;
            }

            $users->where('id', 'IN', $ids);
        }

        $paginate = Paginate::factory($users)->paginate(NULL, NULL, 10)->render();
        $users = $users->find_all()->as_array();
        $this->set('users', $users)->set('paginate', $paginate)->set('page', $this->page);

        $logins = array();
        $moders = array();
        foreach ($users as $user) {
            if ($user->has_access('l')) {
                $logins[$user->id] = 1;
            } else {
                $logins[$user->id] = 0;
            }
            if ($user->has_access('m')) {
                $moders[$user->id] = 1;
            } else {
                $moders[$user->id] = 0;
            }
        }
        $this->set('logins', $logins)->set('moders', $moders);
    }

    public function action_edit()
    {
        $user_id = Security::xss_clean((int)$this->request->param('id', 0));
        $user = ORM::factory('User', $user_id);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            try {
                $user->values($_POST, array('username', 'email'))->save();
                $this->redirect('manage/users/page-'.$this->page);
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors('validation');
                $this->set('errors', $errors);
            }
        } else {
            $this->set('token', Security::token(true));
        }

        $this->set('user', $user)->set('r', Url::media('manage/users/page-'.$this->page));
    }

    public function action_new()
    {
        $token = Arr::get($_POST, 'token', false);

        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $password = Arr::get($_POST, 'password', NULL);
            $password_confirm = Arr::get($_POST, 'password_confirm', NULL);

            if (empty($password))
            {
                Message::success('Пароль не должен быть пустым');
                $this->redirect( $this->request->uri() );
            }

            if ($password !== $password_confirm) {
                Message::success('Пароли не совпадают');
                $this->redirect( $this->request->uri() );
            }

            try {
                $user = ORM::factory('User');
                $user = $user->values($_POST, array('username', 'email', 'password'))->save();
                $user->addRole(1, $user->id);

                $users = ORM::factory('User')->with('Profile');
                $paginate = Paginate::factory($users)->paginate(NULL, NULL, 10);
                $users = $users->find_all()->as_array();
                $last_page=$paginate->page_count();

                $this->redirect('manage/users/page-'.$last_page);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors('validation');
                $this->set('errors', $errors)->set('user', $_POST);
            }
        } else {
            $this->set('token', Security::token(true));
        }
        $this->set('r', Url::media('manage/users/page-'.$this->page));
    }

    public function action_roles()
    {
        $this->set('cancel_url', Url::media('manage/users/page-'.$this->page));
        $user_id = Security::xss_clean((int)$this->request->param('id', 0));
        $user = ORM::factory('User', $user_id);
        $roles = ORM::factory('Role')->find_all();

        $token = Arr::get($_POST, 'token', false);

        $posted_roles = array();

        if ($user->loaded()) {
            $data = array();
            foreach ($roles as $key => $role) {
                $record = $role->as_array();
                $record['has'] = $user->has_access(substr($role->name, 0, 1));
                $data[] = $record;
            }

            if (($this->request->method() == Request::POST) && Security::token() === $token)
            {

                if (isset($_POST['saveroles'])) {
                    if (isset($_POST['roles'])) {
                        $posted_roles = $_POST['roles'];
                    }
                    foreach ($posted_roles as $role_id) {
                        if (!$user->hasRole($role_id, $user->id)) {
                            $user->addRole($role_id, $user->id);

                        }
                    }
                    foreach ($roles as $role) {
                        if ($user->hasRole($role->id, $user->id) and !in_array($role->id, $posted_roles)) {
                            $user->removeRole($role->id, $user->id);
                        }
                    }
                    $this->redirect('manage/users/page-'.$this->page);
                }
            } else {
                $this->set('token', Security::token(true));
            }

            $this->set('roles', $data);
        } else {
            Notify::instance()->add('Пользователь не найден')->save();
            $this->redirect('manage');
        }
    }

    public function action_block()
    {
        $this->set('cancel_url', Url::media('manage/users/page-'.$this->page));

        $user_id = (int)$this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);

        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            $user = ORM::factory('User', $user_id);
            if ($user->loaded()) {
                $user->removeRole(1, $user_id);
                $this->redirect('manage/users/page-'.$this->page);
            }
        } else {
            $user = ORM::factory('User', $user_id);
            if ($user->loaded()) {
                $this->set('record', $user)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/users/page-'.$this->page));
            } else {
                Notify::instance()->add('Нет такого пользователя для блокировки')->save();
                $this->redirect('manage/users/page-'.$this->page);
            }
        }
    }

    public function action_unblock()
    {
        $this->set('cancel_url', Url::media('manage/users/page-'.$this->page));

        $user_id = (int)$this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);

        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            $user = ORM::factory('User', $user_id);
            if ($user->loaded()) {
                $user->addRole(1, $user_id);
                $this->redirect('manage/users/page-'.$this->page);
            }
        } else {
            $user = ORM::factory('User', $user_id);
            if ($user->loaded()) {
                $this->set('record', $user)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/users/page-'.$this->page));
            } else {
                Notify::instance()->add('Нет такого пользователя для разблокировки')->save();
                $this->redirect('manage/users/page-'.$this->page);
            }
        }
    }

    public function action_password()
    {
        $this->set('cancel_url', Url::media('manage/users/page-'.$this->page));

        $user_id = (int)$this->request->param('id', 0);

        $token = Arr::get($_POST, 'token', false);

        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            $password = Arr::get($_POST, 'password', NULL);
            $password_confirm = Arr::get($_POST, 'password_confirm', NULL);

            if (empty($password)) {
                //Notify::instance()->add('Пароль не должен быть пустым')->save();
                Message::success('Пароль не должен быть пустым');
                $this->redirect( $this->request->uri() );
            }
            if ($password !== $password_confirm) {
                Message::success('Пароли не совпадают');
                $this->redirect( $this->request->uri() );
            }

            $user = ORM::factory('User', $user_id);

            if ($user->loaded()) {
                $user->password = $password;
                $user->save();
                Message::success('Пароль пользователя успешно изменен');
                $this->redirect('manage/users/page-'.$this->page);
            } else {
                Message::success('Пользователь не найден');
                $this->redirect('manage/users/page-'.$this->page);
            }
        } else {
            $this->set('token', Security::token(true));
        }
    }

}