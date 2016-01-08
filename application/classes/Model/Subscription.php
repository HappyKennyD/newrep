<?php defined('SYSPATH') or die('No direct script access.');

class Model_Subscription extends ORM {

    protected $_belongs_to = array(
        'category' => array(
            'model' => 'Page',
            'far_key' => 'id',
            'foreign_key' => 'category_id'
        ),
    );

}