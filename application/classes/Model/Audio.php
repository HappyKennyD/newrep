<?php
class Model_Audio extends ORM
{
    protected $_table_name = 'audio';

    protected $_belongs_to = array(
        'mp3' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'storage_id'
        ),
        'category' => array(
            'model'=> 'Audio_Category',
            'far_key'=> 'id',
            'foreign_key'=>'category_id'
        )
    );

    public function filters()
    {
        return array(
            'rubric' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'title' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }

}