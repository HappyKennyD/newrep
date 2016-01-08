<?php defined('SYSPATH') or die('No direct script access.');

class Model_Chronology_Line extends ORM {

    protected $_i18n_fields = array('title', 'description', 'text', 'date');

    protected $_belongs_to = array(
        'period' => array(
            'model'         => 'Chronology',
            'far_key'       => 'id',
            'foreign_key'   => 'period_id'
        ),
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

}
