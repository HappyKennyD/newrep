<?php defined('SYSPATH') or die('No direct script access.');

class Model_Debate_Log extends ORM
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

    protected $_belongs_to = array(
        'moderator' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'moderator_id'
        ),
        'old_member' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'old_member'
        ),
        'new_member' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'new_member'
        ),
        'debate' => array(
            'model' => 'Debate',
            'far_key' => 'id',
            'foreign_key' => 'debate_id'
        ),
        'comment' => array(
            'model' => 'Debate_Comment',
            'far_key' => 'id',
            'foreign_key' => 'changed_id'
        ),
        'opinion' => array(
            'model' => 'Debate_Opinion',
            'far_key' => 'id',
            'foreign_key' => 'changed_id'
        )
    );

}