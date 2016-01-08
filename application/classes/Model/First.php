<?php
class Model_First extends ORM
{
    protected $_i18n_fields = array('title');


    public function rules()
    {
        return array(
            'link' => array(
                array('not_empty'),
                array('max_length', array(':value', 255)),
            ),
        );
    }

}