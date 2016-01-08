<?php defined('SYSPATH') or die('No direct script access.');

class Model_Question extends ORM {

    public function labels()
    {
        return array(
            'fio'           => 'ФИО',
            'email'         => 'Электронная почта',
            'text_question' => 'Текст вопроса',
            'text_answer' => 'Ответ на вопрос'
        );
    }

    public function rules()
    {
        return array(
            'fio' => array(
                array('not_empty')
            ),
            'email' => array(
                array('not_empty'),
                array('email'),
            ),
            'text_question' => array(
                array('not_empty')
            ),
            'date_question' => array(
                array('not_empty')
            )
        );
    }

    public function filters()
    {
        return array(
            'fio' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'email' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_question' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'text_answer' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'author' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }
}