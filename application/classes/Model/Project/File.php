<?php defined('SYSPATH') or die('No direct script access.');

class Model_Project_File extends ORM
{
    protected $_belongs_to = array(
        'storage' => array(
            'model' => 'Storage',
            'far_key' => 'id',
            'foreign_key' => 'storage_id'
        ),
    );
}