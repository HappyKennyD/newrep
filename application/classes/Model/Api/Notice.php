<?php
class Model_Api_Notice extends ORM
{
    protected $_belongs_to = array(
        'debate'  => array(
            'model'       => 'Debate',
            'foreign_key' => 'object_id',
        )
    );
}