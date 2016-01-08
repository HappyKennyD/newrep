<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_User extends Model_Auth_User {

    protected $_has_many = array(
        'user_tokens' => array('model' => 'User_Token'),
        'roles'       => array('model' => 'Role', 'through' => 'roles_users'),
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
        'code' => array(
            'model' => 'User_Code',
            'foreign_key' => 'user_id',
        ),
    );

    public function rules()
    {
        return array(
            'username' => array(
                array('not_empty'),
                array('max_length', array(':value', 32)),
                array('min_length', array(':value', 3)),
                array(array($this, 'unique'), array('username', ':value')),
            ),
            'password' => array(
                array('not_empty'),
            ),
            'email' => array(
                array('not_empty'),
                array(array($this, 'unique'), array('email', ':value')),
            ),
        );
    }
} // End User Model