<?php
class Model_Video extends ORM
{
    protected $_table_name = 'video';

    protected $_belongs_to = array(
        'category' => array(
            'model'         => 'Category',
            'far_key'       => 'id',
            'foreign_key'   => 'category_id'
        )
    );

    public function filters()
    {
        return array(
            'description' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'title' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
       );
    }

}