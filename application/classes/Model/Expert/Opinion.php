<?php defined('SYSPATH') or die('No direct script access.');

class Model_Expert_Opinion extends ORM
{
    protected $_i18n_fields = array('text', 'description', 'title');

    public function filters()
    {
        return array(
            TRUE => array(
                array('Security::xss_clean'),
                array('trim'),
            ),
            /*'text_' . I18n::$lang => array(
                array('strip_tags')
            ),*/
            'description_' . I18n::$lang => array(
                array('strip_tags')
            ),
        );
    }

    public function rules()
    {
        return array(
            'text_' . I18n::$lang => array(
                array('not_empty'),
            ),
            'title_' . I18n::$lang => array(
                array('not_empty'),
                array('max_length', array(':value', 500))
            ),
            'description_' . I18n::$lang => array(
                array('not_empty'),
                array('max_length', array(':value', 500))
            ),
        );
    }

    public function translation($lang)
    {
        switch($lang){
            case 'ru': if ($this->text_ru == '') {return FALSE;} break;
            case 'kz': if ($this->text_kz == '') {return FALSE;} break;
            case 'en': if ($this->text_en == '') {return FALSE;} break;
        }
        return TRUE;
    }

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'user_id'
        ),
        'expert' => array(
            'model' => 'Expert',
            'far_key' => 'id',
            'foreign_key' => 'expert_id'
        )
    );

}