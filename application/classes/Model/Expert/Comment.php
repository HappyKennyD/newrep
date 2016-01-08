<?php defined('SYSPATH') or die('No direct script access.');

class Model_Expert_Comment extends ORM
{

    public function filters()
    {
        return array(
            TRUE => array(
                array('Security::xss_clean'),
                array('trim'),
                array('strip_tags')
            ),
        );
    }

    public function rules()
    {
        return array(
            'text' => array(
                array('max_length', array(':value', 5000)),
                array('not_empty'),
            )
        );
    }

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'user_id'
        ),
        'opinion' => array(
            'model' => 'Expert_Opinion',
            'far_key' => 'id',
            'foreign_key' => 'opinion_id'
        )
    );

}