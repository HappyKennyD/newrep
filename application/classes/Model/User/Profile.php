<?php defined('SYSPATH') or die('No direct script access.');

class Model_User_Profile extends ORM {

    public function rules()
    {
        return array(
            'first_name' => array(
                array('max_length', array(':value', 32)),
            ),
            'last_name' => array(
                array('max_length', array(':value', 32)),
            ),
            'specialization' => array(
                array('max_length', array(':value', 250)),
            ),
            'about' => array(
                array('max_length', array(':value', 500)),
            ),
            'email' => array(
                array('email')
            )
        );
    }

    protected $_belongs_to = array(

        'img'=> array(
            'model'         =>  'Storage',
            'far_key'       =>  'id',
            'foreign_key'   =>  'photo'
        )
    );
}

