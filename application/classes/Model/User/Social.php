<?php defined('SYSPATH') or die('No direct script access.');

class Model_User_Social extends ORM {

    public function filters()
    {
        return array(
            TRUE => array(
                array('Security::xss_clean'),
                array('strip_tags'),
                array('trim')
            ),
        );
    }

    public function rules()
    {
        return array(
            'link' => array(
                array('max_length', array(':value', 250)),
                array('not_empty'),
                array('url'),
            ),
        );
    }

}

