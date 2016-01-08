<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Roles_User extends Model_Auth_User {
    public function rules()
    {
        return array(
            'user_id' => array(
                array('not_empty'),
                array('numeric'),
            ),
            'role_id' => array(
                array('not_empty'),
                array('numeric'),
            ),
        );
    }
}