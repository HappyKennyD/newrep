<?php
class Model_Calendar extends ORM
{
    protected $_table_name = 'calendar';

    protected $_i18n_fields = array('title', 'text', 'desc');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );
}