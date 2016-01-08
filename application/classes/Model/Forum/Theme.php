<?php defined('SYSPATH') or die('No direct script access.');
class Model_Forum_Theme extends ORM {

    public function filters()
    {
        return array(
            TRUE => array(
                array('Security::xss_clean'),
                array('trim')
            ),
        );
    }

    protected $_has_many = array(
        'forum_message' => array(
            'model' => 'Forum_Message',
            'foreign_key' => 'theme_id',
        ),
    );

    public function count_messages()
    {
        return $this->forum_message->count_all();
    }

    public function last_message()
    {
        return $this->forum_message->where('moderator','=',1)->order_by('date', 'desc')->limit(1)->find();
    }

    public function first_message()
    {
        return $this->forum_message->where('first_message','=',1)->find();
    }
}