<?php defined('SYSPATH') or die('No direct script access.');

class Model_Debate_Opinion extends ORM
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

    public function rules()
    {
        return array(
            'opinion' => array(
                array('max_length', array(':value', 5000)),
                array('min_length', array(':value', 30)),
                array('not_empty'),
            ),
            'moderator_text' => array(
                array('max_length', array(':value', 5000)),
                array('not_empty'),
            )
        );
    }

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'author_id'
        ),
        'debate' => array(
            'model' => 'Debate',
            'far_key' => 'id',
            'foreign_key' => 'debate_id'
        )
    );

}