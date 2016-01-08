<?php
class Model_Thank extends ORM
{
    protected $_i18n_fields = array('name', 'text');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

}