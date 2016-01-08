<?php defined('SYSPATH') or die('No direct script access.');

class Model_Slider extends ORM {

    protected $_i18n_fields = array('title', 'link');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

}