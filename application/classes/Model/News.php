<?php
class Model_News extends ORM
{
    protected $_i18n_fields = array('title', 'text', 'desc');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

    protected $_has_many = array(
        'tags'    => array(
            'model'       => 'Tag',
            'foreign_key' => 'public_id',
        )
    );

    public function labels()
    {
        return array(
            'title'   => 'Заголовок',
            'desc'    => 'Краткое описание',
            'text'    => 'Текст',
        );
    }

    public function filters()
    {
        return array(
            'desc_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'title_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'desc_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'title_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'desc_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'title_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }

}