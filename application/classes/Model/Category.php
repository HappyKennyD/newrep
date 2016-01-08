<?php
class Model_Category extends ORM_MPTT
{

    protected $_i18n_fields = array('name');

    protected $_has_many = array(
        'videos' => array(
            'model' => 'Video',
            'foreign_key' => 'category_id',
        ),
    );

}