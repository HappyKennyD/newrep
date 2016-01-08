<?php defined('SYSPATH') or die('No direct script access.');

class Model_Material extends ORM
{

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'user_id'
        ),
        'moderator' => array(
            'model' => 'User',
            'far_key' => 'id',
            'foreign_key' => 'moderator_id'
        ),
    );

    protected $_has_many = array(
        'files' => array(
            'model' => 'Material_File',
            'foreign_key' => 'material_id',
        ),
    );

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
            'theme' => array(
                array('max_length', array(':value', 255)),
                array('min_length', array(':value', 3)),
                array('not_empty')
            ), 'message' => array(
                array('max_length', array(':value', 511)),
                array('min_length', array(':value', 30)),
                array('not_empty')
            )
        );
    }
}