<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Feedback_Answer extends ORM {

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
            'answer' => array(
                array('not_empty'),
                array('min_length', array(':value', 30)),
                array('max_length', array(':value', 5000))
            ),
        );
    }

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'moderator_id'
        ),
    );
}
