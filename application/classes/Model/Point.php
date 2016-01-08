<?php
class Model_Point extends ORM
{
    protected $_i18n_fields = array('name', 'desc', 'text');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

    public function filters()
    {
        return array(
            'desc_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'desc_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'desc_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }

}