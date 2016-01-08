<?php
class Model_Specproject extends ORM
{
    protected $_table_name = 'spec_projects';

    protected $_belongs_to = array(
        'publication' => array(
            'model'=> 'Publication',
            'far_key'=> 'id',
            'foreign_key'=>'id_publication'
        )
    );
}