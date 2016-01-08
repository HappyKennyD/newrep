<?php

class Model_Exhibit extends ORM{
    protected $_i18n_fields = array('description', 'title');

    protected $_belongs_to = array('image'=> array(
        'model' => 'Storage',
        'far_key' => 'id',
        'foreign_key' => 'image_storage_id'
    ));

    protected $_has_many = array(
        'albums' => array(
            'model' => 'Exhibit_Album',
            'far_key' => 'id',
            'foreign_key' => 'exhibit_id'
        )
    );
}