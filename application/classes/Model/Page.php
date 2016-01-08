<?php defined('SYSPATH') or die('No direct script access.');

class Model_Page extends ORM_MPTT {

    protected $_i18n_fields = array('name', 'description');

    protected $_has_many = array(
        'content' => array(
            'model' => 'Pages_Content',
            'foreign_key' => 'page_id',
        ),
        //сотрудники организаций образования и науки
        'people' => array(
            'model' => 'People',
            'foreign_key' => 'page_id',
        ),
    );

    protected $_has_one = array(
        //организации образования и науки
        'organization' => array(
            'model' => 'Organization',
            'foreign_key' => 'page_id',
        ),
    );
}