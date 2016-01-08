<?php defined('SYSPATH') or die('No direct script access.');

class Model_Organization extends ORM {

    protected $_i18n_fields = array('title', 'description', 'address', 'phone', 'fax');

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
