<?php defined('SYSPATH') or die('No direct script access.');

class Model_Expert extends ORM
{
    protected $_i18n_fields = array('name', 'description', 'position');

    public function filters()
    {
        return array(
            TRUE => array(
                array('Security::xss_clean'),
                array('trim'),
                array('strip_tags')
            ),
        );
    }

    public function rules()
    {
        return array(
            'name_' . I18n::$lang => array(
                array('not_empty'),
                array('max_length', array(':value', 500))
            ),
            'position_' . I18n::$lang => array(
                array('not_empty'),
                array('max_length', array(':value', 500))
            ),
            'description_' . I18n::$lang => array(
                array('not_empty'),
                array('max_length', array(':value', 500))
            ),
        );
    }

    protected $_belongs_to = array(
        'picture' => array(
            'model' => 'Storage',
            'far_key' => 'id',
            'foreign_key' => 'image'
        ),
    );
}