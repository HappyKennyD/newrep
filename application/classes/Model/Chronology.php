<?php defined('SYSPATH') or die('No direct script access.');

class Model_Chronology extends ORM_MPTT {

    protected $_i18n_fields = array('name', 'start', 'finish');

    protected $_has_many = array(
        'lines' => array(
            'model' => 'Chronology_Line',
            'foreign_key' => 'period_id',
        ),
    );

    public function filters()
    {
        return array(
            'name_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'start_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'finish_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'start_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'finish_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'start_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'finish_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }
}