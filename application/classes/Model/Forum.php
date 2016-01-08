<?php defined('SYSPATH') or die('No direct script access.');

class Model_Forum extends ORM {

    protected $_i18n_fields = array('name');

    protected $_has_many = array(
        'forum_theme' => array(
            'model' => 'Forum_Theme',
            'foreign_key' => 'forum_id',
        ),
        'forum_message' => array(
            'model' => 'Forum_Message',
            'foreign_key' => 'section_id',
        ),
    );

    public function filters()
    {
        return array(
            'name_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }

    public function last_message()
    {
        return $this->forum_message->order_by('date','desc')->find();
    }

    public function count_themes()
    {
        return $this->forum_theme->count_all();
    }

    public function count_messages()
    {
        return $this->forum_message->count_all();
    }
}