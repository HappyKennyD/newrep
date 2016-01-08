<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_User extends Model_Auth_User {

    protected $_has_many = array(
        'user_tokens' => array('model' => 'User_Token'),
        'roles'       => array('model' => 'Role', 'through' => 'roles_users'),
        'messages' => array(
            'model' => 'Forum_Message',
            'foreign_key' => 'user_id',
        ),
        'forums' => array(
            'model' => 'Forum',
            'foreign_key' => 'user_id',
        ),
        /*'rating' => array(
            'model'       => 'Forum_Message',
            'through'     => 'users_voice',
            'foreign_key' => 'user_id',
            'far_key'     => 'message_id'
        ),*/
    );

    protected $_has_one = array(
        'profile' => array(
            'model' => 'User_Profile',
            'foreign_key' => 'user_id',
        ),
        'bruteforce' => array(
            'model' => 'User_Bruteforce',
            'foreign_key' => 'user_id',
        ),
        'socials' => array(
            'model' => 'User_Social',
            'foreign_key' => 'user_id',
        ),
        'api_query' => array(
            'model' => 'Api_Querie',
            'foreign_key' => 'user_id',
        ),
    );

    public function labels()
    {
        return array(
            'username'   => 'Имя пользователя',
            'password'    => 'Пароль',
            'email'    => 'Электронная почта',
        );
    }

    public function rules()
    {
        return array(
            'username' => array(
                array('not_empty'),
                array('max_length', array(':value', 64)),
                array('min_length', array(':value', 3)),
                array(array($this, 'unique'), array('username', ':value')),
            ),
            'password' => array(
                array('not_empty'),
                array('min_length', array(':value', 6)),
            ),
            'email' => array(
                array('email'),
                array('not_empty'),
                array(array($this, 'unique'), array('email', ':value')),
            ),
        );
    }

    public function has_access($level = '')
    {
        $has = false;
        $levels = array();
        if (strpos($level, 'r') !== false) { $levels[] = 5; }
        if (strpos($level, 'm') !== false) { $levels[] = 3; }
        if (strpos($level, 'a') !== false) { $levels[] = 2; }
        if (strpos($level, 'l') !== false) { $levels[] = 1; }
        foreach ($levels as $lvl)
        {
            $has = $has || $this->has('roles', $lvl);
        }
        return $has;
    }

    public function hasRole($role, $user_id = 0)
    {
        if (!Auth::instance()->logged_in() and $user_id==0)
            return false;
        $user_id = (int) $user_id;
        if ($user_id==0)
        {
            $user_id = Auth::instance()->get_user()->id;
        }
        $key_field = (is_numeric($role)) ? 'id': 'name';

        $roles = ORM::factory('Roles_User')
            ->join('roles','LEFT')
            ->on('roles_user.role_id','=','roles.id')
            ->select('roles.*')
            ->where('user_id', '=', $user_id)
            ->and_where('roles.'.$key_field, '=', $role)
            ->count_all();
        if ($roles>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function addRole($role_id, $user_id)
    {
        return Db::insert('roles_users', array('user_id', 'role_id'))->values(array($user_id, $role_id))->execute();
    }

    public function removeRole($role_id, $user_id)
    {
        return Db::delete('roles_users')->where('role_id', '=', $role_id)->and_where('user_id', '=', $user_id)->execute();
    }

    public function show_name()
    {
        if ($this->profile->first_name OR $this->profile->last_name)
        {
            return $this->profile->first_name.' '.$this->profile->last_name;
        }
        else
        {
            return $this->username;
        }
    }

    public function get_specialization()
    {
        return $this->profile->specialization;
    }

    public function complete_login()
    {
       if ($this->_loaded)
       {

          // Update the number of logins

          $this->logins = new Database_Expression('logins + 1');

          //Set previos before last login date

          $this->api_query->date = $this->last_login;

          // Set the last login date

          $this->last_login = time();

           // Save the user

           $this->update();

       }
    }


} // End User Model