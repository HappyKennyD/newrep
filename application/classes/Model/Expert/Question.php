<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Expert_Question extends ORM
{
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

    protected $_has_one = array(
        'qalist' => array(
            'model' => 'Expert_Answer',
            'far_key' => 'id',
            'foreign_key' => 'question_id',
        ),
    );

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'user_id'
        ),
        'moderator' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'spam_mod_id'
        ),
    );


    public function rules()
    {
        return array(
            'question' => array(
                array('not_empty'),
                array('min_length', array(':value', 30)),
                array('max_length', array(':value', 5000))
            ),
        );
    }

}
