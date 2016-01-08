<?php defined('SYSPATH') or die('No direct script access.');

class Model_Debate extends ORM
{
    protected $_log_fields = array('title'=>'title','description'=>'description');

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
            'title' => array(
                array('max_length', array(':value', 500)),
                array('not_empty')
            ), 'opponent_email' => array(
                array('not_empty'),
                array('email')
            ), 'lifetime' => array(
                array('range', array(':value', 35, 169))
            ), 'description' => array(
                array('max_length', array(':value', 10000))
            )
        );
    }

    protected $_belongs_to = array(
        'author' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'author_id'
        ),
        'opponent' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'opponent_id'
        )
    );

}