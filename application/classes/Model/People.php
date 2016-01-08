<?php defined('SYSPATH') or die('No direct script access.');

class Model_People extends ORM {

    protected $_i18n_fields = array('fio', 'description', 'rank');

    protected $_belongs_to = array(
        'page' => array(
            'model'         => 'Page',
            'far_key'       => 'id',
            'foreign_key'   => 'page_id'
        ),
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

}
